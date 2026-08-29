<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AspelClient;
use App\Models\EmailContactList;
use App\Models\EmailContactListMember;
use App\Models\User;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\EmailContactListMemberTableQuery;
use App\Support\AdminTable\Queries\EmailContactListTableQuery;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Listas de contactos del módulo de Email Marketing (pestaña "Listas" de
 * EmailMarketingController). Mismo permiso granular
 * "marketing-integracion" que el resto del módulo.
 *
 * El CRUD de la lista en sí va por modal (AU.FormModal, calco de
 * MarketingApiTokenController); los contactos de una lista viven en una
 * página completa aparte — son una tabla con sus propios filtros e
 * importadores, no caben en un modal.
 */
class EmailContactListController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:marketing-integracion,view')->only(['tableData', 'show', 'membersTableData']);
        $this->middleware('can-access-module:marketing-integracion,create')->only(['createFragment', 'store']);
        $this->middleware('can-access-module:marketing-integracion,edit')->only(['editFragment', 'update', 'addManual', 'importCustomers', 'importAspel']);
        $this->middleware('can-access-module:marketing-integracion,delete')->only(['destroy', 'removeMember']);
    }

    /** JSON data source for the custom admin table. */
    public function tableData(Request $request, EmailContactListTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.email-lists._form');
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $emailContactList = EmailContactList::findOrFail($id);

        return view('admin-ui.email-lists._form', compact('emailContactList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'max:200'],
            'description' => ['nullable', 'max:1000'],
        ]);

        EmailContactList::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => 1,
            'created_by_admin_id' => Auth::id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lista creada con éxito. Entra a "Ver contactos" para llenarla.',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $list = EmailContactList::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'max:200'],
            'description' => ['nullable', 'max:1000'],
            'status' => ['required'],
        ]);

        $list->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $request->boolean('status'),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Lista actualizada con éxito.']);
    }

    /**
     * Borrar una lista arrastra a sus contactos (cascade), pero NUNCA a las
     * campañas: la FK de email_campaigns.email_contact_list_id es restrict,
     * así que si alguna campaña la está usando la base de datos rechaza el
     * borrado. Ese rechazo se traduce aquí a un mensaje amigable en vez de
     * dejarlo reventar como error 500.
     */
    public function destroy(string $id)
    {
        $list = EmailContactList::findOrFail($id);

        try {
            $list->delete();
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede borrar esta lista porque hay campañas que la usan. Borra o cancela primero esas campañas.',
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Lista eliminada con éxito.']);
    }

    /** Página completa con los contactos de la lista + los tres importadores. */
    public function show(string $id)
    {
        $emailContactList = EmailContactList::withCount([
            'members as members_count' => fn ($q) => $q->whereNull('unsubscribed_at'),
            'members as unsubscribed_count' => fn ($q) => $q->whereNotNull('unsubscribed_at'),
        ])->findOrFail($id);

        return view('admin-ui.email-lists.show', compact('emailContactList'));
    }

    /** JSON data source de la tabla de contactos, acotada a esta lista. */
    public function membersTableData(Request $request, string $id, EmailContactListMemberTableQuery $table)
    {
        $list = EmailContactList::findOrFail($id);

        return response()->json($table->forList((int) $list->id)->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Alta manual de un contacto suelto, sin cuenta en el sitio ni en Aspel. */
    public function addManual(Request $request, string $id)
    {
        $list = EmailContactList::findOrFail($id);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'max:200'],
            'company' => ['nullable', 'max:200'],
        ]);

        $email = mb_strtolower(trim($data['email']));

        // El unique(lista, email) de la tabla es la defensa real; esta
        // consulta previa solo existe para poder dar un mensaje claro en vez
        // de un error de integridad.
        if ($list->members()->where('email', $email)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ese correo ya está en la lista.',
            ]);
        }

        EmailContactListMember::create([
            'email_contact_list_id' => $list->id,
            'email' => $email,
            'name' => $data['name'] ?? null,
            'company' => $data['company'] ?? null,
            'source' => 'manual',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Contacto agregado con éxito.']);
    }

    /**
     * Importa clientes del sitio (users con role = 'user', mismo universo
     * que el módulo Clientes).
     *
     * scope:
     * - 'todos': todos los clientes registrados con correo.
     * - 'compradores': solo los que tienen al menos una compra válida —
     *   order_status != 'canceled' y payment_status = 1, exactamente el
     *   mismo criterio que ya usa GET /api/marketing/customers, para que
     *   "cliente que compró" signifique lo mismo en todo el módulo.
     */
    public function importCustomers(Request $request, string $id)
    {
        $list = EmailContactList::findOrFail($id);

        $request->validate([
            'scope' => ['required', 'in:todos,compradores'],
        ]);

        $query = User::query()
            ->where('role', 'user')
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($request->input('scope') === 'compradores') {
            $query->whereHas('orders', function ($q) {
                $q->where('order_status', '!=', 'canceled')->where('payment_status', 1);
            });
        }

        $imported = $this->importRows(
            $list,
            $query->orderBy('id'),
            fn (User $user) => [
                'email' => $user->email,
                'name' => trim(($user->name ?? '') . ' ' . ($user->last_name ?? '')),
                'company' => $user->company,
                'source' => 'user',
                'user_id' => $user->id,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => $this->importSummary($imported),
        ]);
    }

    /**
     * Importa clientes sincronizados desde Aspel (tabla aspel_clients).
     *
     * No se filtra por `status`: esa columna viene tal cual de Aspel como un
     * solo carácter cuyo significado no está documentado de este lado (ver
     * AspelClientSyncController), así que filtrar por ella sería adivinar.
     * El único requisito real es tener un correo — sin correo no hay nada
     * que importar.
     */
    public function importAspel(Request $request, string $id)
    {
        $list = EmailContactList::findOrFail($id);

        $query = AspelClient::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id');

        $imported = $this->importRows(
            $list,
            $query,
            fn (AspelClient $client) => [
                'email' => $client->email,
                'name' => $client->nombre_comercial ?: $client->nombre,
                'company' => $client->nombre,
                'source' => 'aspel_client',
                'aspel_client_id' => $client->id,
                // user_id se llena si el cliente de Aspel ya está ligado a
                // una cuenta del sitio — así el mismo contacto queda
                // identificado por los dos lados.
                'user_id' => $client->user_id,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => $this->importSummary($imported),
        ]);
    }

    public function removeMember(string $id, string $memberId)
    {
        $list = EmailContactList::findOrFail($id);

        $member = $list->members()->findOrFail($memberId);
        $member->delete();

        return response()->json(['status' => 'success', 'message' => 'Contacto quitado de la lista.']);
    }

    /**
     * Motor común de los dos importadores. Recorre la consulta por bloques y
     * agrega los contactos que faltan.
     *
     * Idempotente por diseño: los correos ya presentes en la lista (incluidos
     * los dados de baja) se saltan — reimportar la misma fuente diez veces
     * no duplica nada ni "revive" a alguien que pidió no recibir correos.
     * Correos repetidos dentro de la misma corrida también se colapsan (dos
     * clientes de Aspel pueden compartir correo).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  callable(\Illuminate\Database\Eloquent\Model): array<string, mixed>  $mapRow
     * @return array{added: int, skipped: int}
     */
    private function importRows(EmailContactList $list, $query, callable $mapRow): array
    {
        $existing = $list->members()->pluck('email')->map(fn ($e) => mb_strtolower($e))->flip();
        $added = 0;
        $skipped = 0;

        $query->chunkById(500, function ($rows) use ($list, $mapRow, &$existing, &$added, &$skipped) {
            $now = now();
            $batch = [];

            foreach ($rows as $row) {
                $attributes = $mapRow($row);
                $email = mb_strtolower(trim((string) $attributes['email']));

                if ($email === '' || $existing->has($email)) {
                    $skipped++;
                    continue;
                }

                $existing[$email] = true;

                $batch[] = [
                    'email_contact_list_id' => $list->id,
                    'email' => $email,
                    'name' => $attributes['name'] ?: null,
                    'company' => $attributes['company'] ?: null,
                    'source' => $attributes['source'],
                    'user_id' => $attributes['user_id'] ?? null,
                    'aspel_client_id' => $attributes['aspel_client_id'] ?? null,
                    'unsubscribed_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $added++;
            }

            if (!empty($batch)) {
                EmailContactListMember::insert($batch);
            }
        });

        return ['added' => $added, 'skipped' => $skipped];
    }

    /** @param  array{added: int, skipped: int}  $imported */
    private function importSummary(array $imported): string
    {
        return 'Importación terminada: ' . $imported['added'] . ' contacto(s) agregado(s), '
            . $imported['skipped'] . ' omitido(s) por estar ya en la lista o no tener correo.';
    }
}
