<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EmailTemplate;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\EmailTemplateTableQuery;
use App\Support\BlockEmailRenderer;
use App\Support\EmailTemplateRenderer;
use Illuminate\Database\QueryException;
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
 * El editor (resources/views/admin-ui/email-templates/_editor.blade.php) se
 * sirve de dos formas, con el MISMO parcial y la MISMA validación:
 *  - create()/edit(): página completa, con envío normal y redirect. Es el
 *    camino de siempre y mantiene vivas las URLs ya conocidas.
 *  - createFragment()/editFragment(): el editor sin layout, para que la
 *    pantalla de pestañas de Email Marketing lo inyecte por AJAX; en ese
 *    caso store()/update() responden JSON en vez de redirigir.
 */
class EmailTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:marketing-integracion,view')->only(['index', 'tableData', 'previewBlocks']);
        $this->middleware('can-access-module:marketing-integracion,create')->only(['create', 'createFragment', 'store']);
        $this->middleware('can-access-module:marketing-integracion,edit')->only(['edit', 'editFragment', 'update']);
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

    /**
     * Fragmento del editor para el panel de la pantalla de pestañas de Email
     * Marketing — el mismo parcial que usa la página completa, pero sin
     * layout, para inyectarlo por AJAX.
     */
    public function createFragment()
    {
        return view('admin-ui.email-templates._editor', [
            'emailTemplate' => null,
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    /** Igual que createFragment(), ya precargado con la plantilla a editar. */
    public function editFragment(string $id)
    {
        return view('admin-ui.email-templates._editor', [
            'emailTemplate' => EmailTemplate::findOrFail($id),
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $emailTemplate = EmailTemplate::create($data);

        // El editor dentro de la pantalla de pestañas guarda por fetch y
        // espera JSON; la página completa sigue con su POST normal y su
        // redirect de siempre. Se decide por lo que pide el cliente, no por
        // una ruta aparte, para no duplicar la validación.
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Plantilla de correo creada con éxito.',
                'id' => $emailTemplate->id,
            ]);
        }

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

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Plantilla de correo actualizada con éxito.',
                'id' => $emailTemplate->id,
            ]);
        }

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

        // Las FK de email_campaigns.email_template_id y
        // email_sequence_steps.email_template_id son restrict: si alguna
        // campaña o algún paso de secuencia usa esta plantilla, la base de
        // datos rechaza el borrado. Se traduce a un mensaje entendible en vez
        // de dejarlo salir como error 500.
        try {
            $emailTemplate->delete();
        } catch (QueryException $e) {
            return response([
                'status' => 'error',
                'message' => 'No se puede borrar esta plantilla porque hay campañas o pasos de secuencia que la usan. Cámbiales la plantilla primero, o desactiva esta en vez de borrarla.',
            ]);
        }

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
        $rawBlocks = $request->input('blocks_json');

        if (is_string($rawHtml) && trim($rawHtml) !== '') {
            $html = $rawHtml;
        } elseif (is_string($rawBlocks) && trim($rawBlocks) !== '') {
            $blocksJson = $this->decodeBlocksJson($rawBlocks);
            $html = app(BlockEmailRenderer::class)->render($blocksJson, BlockEmailRenderer::dummyPlaceholderData());
        } else {
            // Ni HTML ni bloques: es una plantilla en blanco, no un error.
            // Antes esto caía en decodeBlocksJson(null) y respondía 422, lo
            // que con la vista previa en vivo saltaba de inmediato al abrir
            // el editor vacío (el modal anterior solo se pedía a mano, con
            // algo ya escrito, así que el caso nunca se daba). El 422 se
            // conserva solo para un JSON de bloques realmente malformado.
            $html = '';
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
            // Etiqueta de organización del listado (Individual/Campaña/
            // Secuencia). Nullable a propósito: cualquier POST viejo que no
            // mande el campo sigue funcionando y cae en 'individual', que es
            // también el default de la columna.
            'type' => ['nullable', 'in:individual,campaign,sequence'],
            'subject' => ['required', 'max:255'],
            'body' => ['nullable'],
            'blocks_json' => ['nullable', 'string'],
            'builder_mode' => ['nullable', 'in:code,blocks'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['required'],
        ]);

        $validated['type'] = $validated['type'] ?? 'individual';
        // ?? además de ?: — un campo "nullable" que no venga en la petición
        // ni siquiera existe como clave en $validated, y leerlo directo
        // reventaba con "Undefined array key".
        $validated['category_id'] = ($validated['category_id'] ?? null) ?: null;
        $validated['status'] = $request->boolean('status');

        $blocksJsonRaw = $validated['blocks_json'] ?? null;
        unset($validated['blocks_json']);

        // Lo que decide si esta plantilla es "de bloques" es que traiga
        // bloques REALES, no que traiga el campo blocks_json.
        //
        // El editor manda siempre ese campo, incluso en vista avanzada,
        // donde vale {"theme":{...},"blocks":[]}. Antes bastaba con que el
        // campo no viniera vacío para entrar por la rama de bloques, así
        // que al guardar en vista avanzada el `body` se sobrescribía con el
        // render de CERO bloques — es decir, el HTML que el admin acababa
        // de escribir se perdía y quedaba un cascarón vacío. Comprobar
        // `blocks` en vez del campo completo es lo que evita esa pérdida.
        $blocksJson = null;
        if ($blocksJsonRaw !== null && trim($blocksJsonRaw) !== '') {
            $blocksJson = $this->decodeBlocksJson($blocksJsonRaw);
        }
        $hasBlocks = !empty($blocksJson['blocks']);

        if ($hasBlocks) {
            $validated['blocks_json'] = $blocksJson;
            // El body se regenera siempre a partir de los bloques cuando
            // llegan bloques — así el campo `body` guardado en base de
            // datos queda como una copia ya renderizada (sirve de respaldo
            // y de "vista previa" para cualquier código que todavía lea
            // `body` directo), en vez de quedar desactualizado o vacío.
            $validated['body'] = app(BlockEmailRenderer::class)->render($blocksJson, BlockEmailRenderer::dummyPlaceholderData());
        } else {
            // Vista avanzada (o plantilla sin bloques): el body es el HTML
            // crudo que mandó el formulario, tal cual, y no se guarda
            // blocks_json — así al reabrirla el editor arranca de nuevo en
            // vista avanzada con su HTML, en vez de en un lienzo vacío.
            $validated['blocks_json'] = null;
        }

        // builder_mode solo es informativo (qué editor se usó por última
        // vez) — el formulario lo manda vía el hidden #builder-mode-field,
        // actualizado por el mismo JS que decide el aviso "editor de texto
        // simple todavía" (ver form.blade.php). Si por lo que sea no llega
        // (ej. un POST directo a la API), se cae al mismo criterio: hay
        // bloques reales -> 'blocks', si no -> 'code'.
        $validated['builder_mode'] = ($validated['builder_mode'] ?? null) ?: ($hasBlocks ? 'blocks' : 'code');

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
