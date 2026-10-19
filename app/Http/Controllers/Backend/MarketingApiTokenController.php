<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AttributionApiTokenMiddleware;
use App\Models\MarketingApiToken;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\MarketingApiTokenTableQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * CRUD de los tokens que autentican las rutas GET /api/marketing/* (ver
 * App\Http\Middleware\MarketingApiTokenMiddleware) y, con uso "attribution",
 * GET /api/attribution/lookup (ver AttributionApiTokenMiddleware). El uso
 * (`scope`) aísla ambos: un token de un uso no autentica las rutas del otro. Calco de
 * AspelApiTokenController pero para un universo de tokens aislado
 * (marketing_api_tokens, prefijo "mk_") — n8n consume estas rutas para leer
 * clientes/plantillas de correo, nunca las de Aspel. Sin export/bulk
 * actions a propósito — no tiene sentido exportar secretos a un Excel/CSV.
 */
class MarketingApiTokenController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:marketing-integracion,view')->only(['index', 'tableData']);
        $this->middleware('can-access-module:marketing-integracion,create')->only(['createFragment', 'store']);
        $this->middleware('can-access-module:marketing-integracion,edit')->only(['editFragment', 'update', 'regenerate']);
        $this->middleware('can-access-module:marketing-integracion,delete')->only(['destroy']);
    }

    public function index()
    {
        // Datos informativos del endpoint de atribucion (Google Ads) para pegarlo en n8n. NUNCA incluye el token.
        $attributionToken = (string) config('attribution.api_token');
        $attribution = [
            'lookup_url' => url('/api/attribution/lookup'),
            'token_configured' => $attributionToken !== '',
            'token_length_ok' => strlen($attributionToken) >= AttributionApiTokenMiddleware::MIN_TOKEN_LENGTH,
            'min_length' => AttributionApiTokenMiddleware::MIN_TOKEN_LENGTH,
            // Solo un conteo de tokens activos con uso "Atribución Google Ads" creados en esta tabla.
            'db_tokens_active' => MarketingApiToken::activeAttributionCount(),
        ];

        return view('admin-ui.marketing-tokens.index', compact('attribution'));
    }

    /** JSON data source for the custom admin table. */
    public function tableData(Request $request, MarketingApiTokenTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.marketing-tokens._form');
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $marketingApiToken = MarketingApiToken::findOrFail($id);

        return view('admin-ui.marketing-tokens._form', compact('marketingApiToken'));
    }

    /**
     * Crea un token nuevo — key_id (identidad pública, siempre visible) +
     * secret (nunca se guarda en claro, solo su hash y sus últimos 4
     * caracteres). El valor completo "{key_id}.{secret}" viaja en
     * reveal_secret — es la ÚNICA vez que el backend lo entrega; el
     * frontend lo debe mostrar una sola vez (ver admin.js) y luego no hay
     * forma de recuperarlo, solo regenerarlo.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:200'],
            // Sin `scope` (clientes viejos) = marketing, como antes.
            'scope' => ['nullable', Rule::in(array_keys(MarketingApiToken::useLabels()))],
        ]);

        $scope = $request->input('scope') ?: MarketingApiToken::SCOPE_MARKETING;

        // Antes de migrar no existe la columna: un token de atribución no se podría distinguir del de
        // marketing y autenticaría las rutas equivocadas, así que se rechaza (el de marketing sigue igual).
        $hasScopeColumn = Schema::hasColumn('marketing_api_tokens', 'scope');
        if ($scope !== MarketingApiToken::SCOPE_MARKETING && !$hasScopeColumn) {
            return response()->json([
                'status' => 'error',
                'message' => 'Falta correr la migración que agrega el uso del token (php artisan migrate).',
            ], 422);
        }

        $keyId = 'mk_' . Str::random(16);
        $secret = Str::random(40);

        $token = new MarketingApiToken();
        $token->name = $request->name;
        if ($hasScopeColumn) {
            $token->scope = $scope;
        }
        $token->key_id = $keyId;
        $token->secret_hash = Hash::make($secret);
        $token->secret_last_four = substr($secret, -4);
        $token->status = 1;
        $token->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Token creado con éxito.',
            'reveal_secret' => "{$keyId}.{$secret}",
        ]);
    }

    /**
     * Permite renombrar, activar/revocar y cambiar el uso — el valor del token nunca se edita aquí, ver
     * regenerate(). Cambiar el uso deja de autenticar de inmediato las rutas del uso anterior.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => ['required', 'max:200'],
            'status' => ['required'],
            // Opcional: si no viene, el uso no cambia.
            'scope' => ['nullable', Rule::in(array_keys(MarketingApiToken::useLabels()))],
        ]);

        $token = MarketingApiToken::findOrFail($id);
        $scope = $request->input('scope');

        if ($scope && $scope !== ($token->scope ?: MarketingApiToken::SCOPE_MARKETING)) {
            if (!Schema::hasColumn('marketing_api_tokens', 'scope')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Falta correr la migración que agrega el uso del token (php artisan migrate).',
                ], 422);
            }
            $token->scope = $scope;
        }

        $token->name = $request->name;
        $token->status = $request->status;
        $token->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Token actualizado con éxito.',
        ]);
    }

    /**
     * Sobreescribe el secreto del token — invalida de inmediato el valor
     * anterior. El key_id NO cambia (es la identidad estable del token a
     * través de rotaciones); solo el secreto rota.
     */
    public function regenerate(string $id)
    {
        $token = MarketingApiToken::findOrFail($id);
        $secret = Str::random(40);
        $token->secret_hash = Hash::make($secret);
        $token->secret_last_four = substr($secret, -4);
        $token->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Token regenerado con éxito.',
            'reveal_secret' => "{$token->key_id}.{$secret}",
        ]);
    }

    public function destroy(string $id)
    {
        $token = MarketingApiToken::findOrFail($id);
        $token->delete();

        return response()->json(['status' => 'success', 'message' => 'Token eliminado con éxito.']);
    }
}
