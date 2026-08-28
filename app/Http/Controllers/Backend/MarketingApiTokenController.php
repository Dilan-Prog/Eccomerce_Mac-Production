<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\MarketingApiToken;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\MarketingApiTokenTableQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * CRUD de los tokens que autentican las rutas GET /api/marketing/* (ver
 * App\Http\Middleware\MarketingApiTokenMiddleware). Calco de
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
        return view('admin-ui.marketing-tokens.index');
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
        ]);

        $keyId = 'mk_' . Str::random(16);
        $secret = Str::random(40);

        $token = new MarketingApiToken();
        $token->name = $request->name;
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

    /** Solo permite renombrar y activar/revocar — el valor del token nunca se edita aquí, ver regenerate(). */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => ['required', 'max:200'],
            'status' => ['required'],
        ]);

        $token = MarketingApiToken::findOrFail($id);
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
