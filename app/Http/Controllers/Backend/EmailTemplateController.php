<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EmailTemplate;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\EmailTemplateTableQuery;
use App\Support\BlockEmailRenderer;
use App\Support\EmailTemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * CRUD de las plantillas de correo del motor de ofertas de marketing (ver
 * App\Support\MarketingOfferBuilder, App\Support\EmailTemplateRenderer y
 * App\Http\Controllers\Api\MarketingDataController::email()). Módulo hermano
 * de MarketingApiTokenController — mismo permiso granular
 * "marketing-integracion" (config/admin-modules.php), ambos viven bajo el
 * grupo "Marketing" del sidebar.
 *
 * A diferencia de Cupones/Tokens (formularios en modal), este usa páginas
 * completas para crear/editar — el campo de cuerpo HTML necesita más
 * espacio, mismo criterio que resources/views/admin-ui/cotizaciones/*.
 */
class EmailTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:marketing-integracion,view')->only(['index', 'tableData', 'previewBlocks']);
        $this->middleware('can-access-module:marketing-integracion,create')->only(['create', 'store']);
        $this->middleware('can-access-module:marketing-integracion,edit')->only(['edit', 'update']);
        $this->middleware('can-access-module:marketing-integracion,delete')->only(['destroy']);
    }

    public function index()
    {
        return view('admin-ui.email-templates.index');
    }

    /** JSON data source for the custom admin table. */
    public function tableData(Request $request, EmailTemplateTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();

        return view('admin-ui.email-templates.form', [
            'emailTemplate' => null,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        EmailTemplate::create($data);

        toastr('Plantilla de correo creada con éxito.', 'success', 'Success');

        return redirect()->route('admin.email-templates.index');
    }

    public function edit(string $id)
    {
        $emailTemplate = EmailTemplate::findOrFail($id);
        $categories = Category::active()->orderBy('name')->get();

        return view('admin-ui.email-templates.form', compact('emailTemplate', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $emailTemplate = EmailTemplate::findOrFail($id);
        $data = $this->validateData($request);

        $emailTemplate->update($data);

        toastr('Plantilla de correo actualizada con éxito.', 'success', 'Success');

        return redirect()->route('admin.email-templates.index');
    }

    public function destroy(string $id)
    {
        $emailTemplate = EmailTemplate::findOrFail($id);

        if ($emailTemplate->is_system) {
            return response([
                'status' => 'error',
                'message' => 'Esta plantilla es del sistema y no se puede eliminar, pero sí puedes editarla.',
            ]);
        }

        $emailTemplate->delete();

        return response(['status' => 'success', 'message' => 'Borrado con éxito']);
    }

    /**
     * POST /admin/email-templates/preview-blocks
     *
     * Previsualización en vivo. Dos modos, según qué mande el editor (JS):
     * - Editor visual por bloques: recibe el mismo JSON que se guarda en
     *   `blocks_json` y arma el HTML a partir de los bloques.
     * - Modo avanzado (HTML crudo): recibe `html` directo — el textarea no
     *   pasa por bloques, así que no hay nada que armar, solo se sustituyen
     *   los marcadores. Antes esto no se distinguía y siempre se intentaba
     *   armar a partir de `blocks_json` (vacío en modo avanzado), lo que
     *   dejaba la vista previa en blanco al escribir/pegar HTML crudo.
     * En ambos casos se usan datos ficticios para productos/cupón/marcadores
     * — nunca un cliente real.
     */
    public function previewBlocks(Request $request)
    {
        $rawHtml = $request->input('html');

        if (is_string($rawHtml) && trim($rawHtml) !== '') {
            $html = $rawHtml;
        } else {
            $blocksJson = $this->decodeBlocksJson($request->input('blocks_json'));
            $html = app(BlockEmailRenderer::class)->render($blocksJson, BlockEmailRenderer::dummyPlaceholderData());
        }

        // Sustituye también los marcadores de texto ({{nombre_cliente}},
        // etc.) con los mismos datos ficticios, reutilizando el mecanismo de
        // EmailTemplateRenderer para no duplicar esa lógica — usamos un
        // EmailTemplate efímero (no se guarda) solo para pasar por ese
        // renderer.
        $previewTemplate = new EmailTemplate(['subject' => '', 'body' => $html]);
        $rendered = app(EmailTemplateRenderer::class)->render($previewTemplate, BlockEmailRenderer::dummyPlaceholderData());

        return response()->json(['html' => $rendered['html']]);
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'max:200'],
            'subject' => ['required', 'max:255'],
            'body' => ['nullable'],
            'blocks_json' => ['nullable', 'string'],
            'builder_mode' => ['nullable', 'in:code,blocks'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['required'],
        ]);

        $validated['category_id'] = $validated['category_id'] ?: null;
        $validated['status'] = $request->boolean('status');

        $blocksJsonRaw = $validated['blocks_json'] ?? null;
        unset($validated['blocks_json']);

        $hasBlocks = false;
        if ($blocksJsonRaw !== null && trim($blocksJsonRaw) !== '') {
            $blocksJson = $this->decodeBlocksJson($blocksJsonRaw);
            $validated['blocks_json'] = $blocksJson;
            $hasBlocks = !empty($blocksJson['blocks']);
            // El body se regenera siempre a partir de los bloques cuando
            // llegan bloques — así el campo `body` guardado en base de
            // datos queda como una copia ya renderizada (sirve de respaldo
            // y de "vista previa" para cualquier código que todavía lea
            // `body` directo), en vez de quedar desactualizado o vacío.
            $validated['body'] = app(BlockEmailRenderer::class)->render($blocksJson, BlockEmailRenderer::dummyPlaceholderData());
        } else {
            $validated['blocks_json'] = null;
        }

        // builder_mode solo es informativo (qué editor se usó por última
        // vez) — el formulario lo manda vía el hidden #builder-mode-field,
        // actualizado por el mismo JS que decide el aviso "editor de texto
        // simple todavía" (ver form.blade.php). Si por lo que sea no llega
        // (ej. un POST directo a la API), se cae al mismo criterio: hay
        // bloques reales -> 'blocks', si no -> 'code'.
        $validated['builder_mode'] = $validated['builder_mode'] ?: ($hasBlocks ? 'blocks' : 'code');

        if (trim((string) ($validated['body'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'body' => ['El cuerpo del correo es obligatorio (escríbelo directo o arma bloques en el editor visual).'],
            ]);
        }

        return $validated;
    }

    /**
     * Decodifica y valida mínimamente la estructura de `blocks_json` que
     * manda el editor visual (input oculto, ver
     * resources/views/admin-ui/email-templates/form.blade.php): debe ser
     * JSON bien formado y traer un arreglo `blocks` (puede venir vacío).
     *
     * @return array{theme?: array<string, mixed>, blocks: array<int, array<string, mixed>>}
     */
    private function decodeBlocksJson(?string $raw): array
    {
        $decoded = json_decode((string) $raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw ValidationException::withMessages([
                'blocks_json' => ['El JSON de bloques del correo no es válido.'],
            ]);
        }

        if (!array_key_exists('blocks', $decoded) || !is_array($decoded['blocks'])) {
            throw ValidationException::withMessages([
                'blocks_json' => ['El JSON de bloques del correo debe traer un arreglo "blocks".'],
            ]);
        }

        return $decoded;
    }
}
