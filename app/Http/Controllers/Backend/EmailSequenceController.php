<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EmailSequence;
use App\Models\EmailSequenceStep;
use App\Models\EmailTemplate;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\EmailSequenceEnrollmentTableQuery;
use App\Support\AdminTable\Queries\EmailSequenceTableQuery;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Secuencias de seguimiento de cotizaciones (pestaña "Secuencias" de
 * EmailMarketingController).
 *
 * Crear/editar usa página completa, no modal: el constructor de pasos es una
 * lista de filas repetibles (plantilla + días de espera) que no cabe cómodo
 * en un modal — mismo criterio que ya se usó para las plantillas de correo.
 *
 * Nada de este controlador manda correos ni decide tiempos: quien inscribe
 * cotizaciones, saca a los que compraron y vence pasos es
 * App\Support\SequenceProcessor, ejecutado cuando n8n pregunta.
 */
class EmailSequenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:marketing-integracion,view')->only(['tableData', 'show', 'enrollmentsTableData']);
        $this->middleware('can-access-module:marketing-integracion,create')->only(['create', 'store']);
        $this->middleware('can-access-module:marketing-integracion,edit')->only(['edit', 'update']);
        $this->middleware('can-access-module:marketing-integracion,delete')->only(['destroy']);
    }

    /** JSON data source for the custom admin table. */
    public function tableData(Request $request, EmailSequenceTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    public function create()
    {
        return view('admin-ui.email-sequences.form', [
            'emailSequence' => null,
            'templates' => $this->templateOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data, $request) {
            $sequence = EmailSequence::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $request->boolean('status'),
                'created_by_admin_id' => Auth::id(),
            ]);

            $this->syncSteps($sequence, $data['steps']);
        });

        toastr('Secuencia creada con éxito.', 'success', 'Success');

        return redirect()->route('admin.email-marketing.index', ['tab' => 'secuencias']);
    }

    public function edit(string $id)
    {
        $emailSequence = EmailSequence::with('steps')->findOrFail($id);

        return view('admin-ui.email-sequences.form', [
            'emailSequence' => $emailSequence,
            'templates' => $this->templateOptions(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $sequence = EmailSequence::findOrFail($id);
        $data = $this->validateData($request);

        DB::transaction(function () use ($sequence, $data, $request) {
            $sequence->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $request->boolean('status'),
            ]);

            $this->syncSteps($sequence, $data['steps']);
        });

        toastr('Secuencia actualizada con éxito.', 'success', 'Success');

        return redirect()->route('admin.email-marketing.index', ['tab' => 'secuencias']);
    }

    /**
     * Borrar una secuencia arrastra sus pasos, inscripciones y calendario
     * (todo en cascade) — es decir, borra el historial de seguimiento. Se
     * deja pasar solo si no hay inscripciones: si ya se le dio seguimiento a
     * cotizaciones reales, lo correcto es pausar la secuencia (status = 0),
     * no destruir el registro de lo que se envió.
     */
    public function destroy(string $id)
    {
        $sequence = EmailSequence::withCount('enrollments')->findOrFail($id);

        if ($sequence->enrollments_count > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Esta secuencia ya tiene cotizaciones en seguimiento, así que no se puede borrar. Pausala (Estado: Pausada) para que deje de inscribir cotizaciones nuevas.',
            ]);
        }

        try {
            $sequence->delete();
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede borrar esta secuencia por registros relacionados. Pausala en vez de borrarla.',
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Secuencia eliminada con éxito.']);
    }

    /** Monitor: cabecera con los pasos configurados + tabla de inscripciones. */
    public function show(string $id)
    {
        $emailSequence = EmailSequence::with(['steps.template:id,name'])->findOrFail($id);

        return view('admin-ui.email-sequences.show', compact('emailSequence'));
    }

    /** JSON data source de la tabla de inscripciones, acotada a esta secuencia. */
    public function enrollmentsTableData(Request $request, string $id, EmailSequenceEnrollmentTableQuery $table)
    {
        $sequence = EmailSequence::findOrFail($id);

        return response()->json($table->forSequence((int) $sequence->id)->paginate(AdminTableRequest::fromRequest($request)));
    }

    /**
     * Reescribe los pasos de la secuencia a partir de lo que mandó el
     * constructor del formulario.
     *
     * Los pasos que siguen existiendo se ACTUALIZAN en su sitio (mismo id),
     * no se borran y recrean: sus filas de email_sequence_step_sends
     * apuntan a ese id, y recrearlos borraría en cascada el calendario y el
     * historial de envíos de todas las inscripciones en curso. Solo se
     * borran los pasos que el admin realmente quitó del formulario.
     *
     * @param  array<int, array{email_template_id: int, wait_days: int, name: ?string}>  $steps
     */
    private function syncSteps(EmailSequence $sequence, array $steps): void
    {
        $existing = $sequence->steps()->orderBy('step_order')->get()->values();
        $keptIds = [];

        foreach (array_values($steps) as $index => $step) {
            $order = $index + 1;
            $attributes = [
                'email_template_id' => $step['email_template_id'],
                'step_order' => $order,
                'wait_days' => $step['wait_days'],
                'name' => $step['name'] ?: null,
            ];

            $current = $existing->get($index);
            if ($current) {
                $current->update($attributes);
                $keptIds[] = $current->id;
                continue;
            }

            $created = $sequence->steps()->create($attributes);
            $keptIds[] = $created->id;
        }

        EmailSequenceStep::query()
            ->where('email_sequence_id', $sequence->id)
            ->whereNotIn('id', $keptIds ?: [0])
            ->delete();
    }

    /** @return \Illuminate\Support\Collection<int, EmailTemplate> */
    private function templateOptions()
    {
        // Igual que en campañas: se ofrecen todas las plantillas activas, el
        // `type` es solo una etiqueta de organización.
        return EmailTemplate::where('status', 1)->orderBy('name')->get(['id', 'name', 'type']);
    }

    /** @return array{name: string, description: ?string, steps: array<int, array{email_template_id: int, wait_days: int, name: ?string}>} */
    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'max:200'],
            'description' => ['nullable', 'max:1000'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.email_template_id' => ['required', 'integer', 'exists:email_templates,id'],
            // wait_days se cuenta desde la inscripción, no desde el paso
            // anterior (ver la migración) — 0 significa "de inmediato".
            'steps.*.wait_days' => ['required', 'integer', 'min:0', 'max:365'],
            'steps.*.name' => ['nullable', 'max:200'],
        ], [
            'steps.required' => 'Una secuencia necesita al menos un paso.',
            'steps.min' => 'Una secuencia necesita al menos un paso.',
        ]);

        $validated['steps'] = array_map(fn ($step) => [
            'email_template_id' => (int) $step['email_template_id'],
            'wait_days' => (int) $step['wait_days'],
            'name' => $step['name'] ?? null,
        ], array_values($validated['steps']));

        return $validated;
    }
}
