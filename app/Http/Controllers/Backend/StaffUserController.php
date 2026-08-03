<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\AccountCreatedMail;
use App\Models\Role;
use App\Models\User;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\StaffUserTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Unified "Personal / Staff" module — replaces UserManageController
 * (admin.manage-user, create-only, no technician option) and
 * AdminListController (admin.admin-list, admin-only listing). Covers every
 * non-customer, non-legacy-vendor role: admin, associate, technician.
 * ('vendor' was removed from STAFF_ROLES/ROLE_LABELS — it mapped to a dead,
 * separately-retired legacy vendor panel and only confused admins picking a
 * role for a new staff account; use role=admin + a custom "Rol personalizado"
 * instead.) Customer ('user' role) creation/listing stays in its own
 * existing module.
 *
 * Validation rules for name/last_name/company/rfc/phone/email/password and
 * the AccountCreatedMail sending behaviour are carried over as-is from
 * UserManageController::create() — just consolidated into one path instead
 * of three near-duplicate role branches.
 */
class StaffUserController extends Controller
{
    private const STAFF_ROLES = ['admin', 'associate', 'technician'];

    private const ROLE_LABELS = [
        'admin' => 'Administrador',
        'associate' => 'Asociado',
        'technician' => 'Técnico',
    ];

    public function __construct()
    {
        // This controller manages staff accounts themselves (creating/editing/
        // deleting admin/associate/technician users, and — indirectly —
        // who gets escalated access), so it must only ever be reachable by an
        // unrestricted admin (role === 'admin' and role_id === null). A
        // restricted custom-role admin must never be able to create more staff
        // accounts or grant itself/others more access, even if someone
        // mistakenly granted the "staff" module_key to a custom role. Same
        // hard-exclusion pattern as RoleController::__construct() — see that
        // file for the full rationale on why this is enforced independently
        // of the module-permission middleware, and why it's registered via
        // deferred $this->middleware() rather than an eager constructor-body
        // check (Laravel instantiates controllers to read their middleware
        // list before auth/session has resolved, e.g. during
        // `php artisan route:list`).
        $this->middleware(function (Request $request, \Closure $next) {
            $user = $request->user();
            abort_if(!$user || $user->role !== 'admin' || $user->role_id !== null, 403);
            return $next($request);
        });
    }

    public function index()
    {
        return view('admin-ui.staff-user.index');
    }

    /** JSON data source for the custom admin table. */
    public function tableData(Request $request, StaffUserTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every staff user matching the current filter/search. */
    public function export(Request $request, StaffUserTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Personal',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('personal.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "personal.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. Protected accounts are always skipped. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $users = User::whereIn('id', $ids)->whereIn('role', self::STAFF_ROLES)->get();
            $deleted = 0;
            $skipped = 0;
            foreach ($users as $user) {
                if ($user->is_protected) {
                    $skipped++;
                    continue;
                }
                $user->delete();
                $deleted++;
            }

            $message = "{$deleted} usuario(s) eliminado(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} cuenta(s) protegida(s) no se eliminaron.";
            }

            return response()->json(['status' => $deleted > 0 ? 'success' : 'error', 'message' => $message]);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    public function create()
    {
        return redirect()->route('admin.staff-users.index');
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        $customRoles = Role::where('is_system', false)->orderBy('name')->get();
        return view('admin-ui.staff-user._form', compact('customRoles'));
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $staffUser = User::whereIn('role', self::STAFF_ROLES)->findOrFail($id);
        $customRoles = Role::where('is_system', false)->orderBy('name')->get();
        return view('admin-ui.staff-user._form', compact('staffUser', 'customRoles'));
    }

    public function show(string $id)
    {
        return redirect()->route('admin.staff-users.index');
    }

    public function edit(string $id)
    {
        return redirect()->route('admin.staff-users.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:200'],
            'last_name' => ['max:100'],
            'company' => ['max:200'],
            // `rfc` is a nullable, UNIQUE column — stored as null (not '')
            // below when omitted, so a plain nullable+unique rule is correct:
            // MySQL never treats two NULLs as colliding under a unique index.
            'rfc' => ['nullable', 'max:14', 'unique:users,rfc'],
            'phone' => ['max:15'],
            'role' => ['required', Rule::in(self::STAFF_ROLES)],
            'role_id' => ['nullable', 'exists:roles,id'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'status' => ['nullable'],
        ]);

        $user = new User();
        $user->fill([
            // last_name/company/phone are nullable but harmless to default to
            // '' (no uniqueness constraint on them). rfc is ALSO nullable but
            // DOES have a unique index, so it must default to null, never ''
            // — two blank-string rows collide under MySQL's unique index,
            // while two NULLs never do.
            'name' => $validated['name'],
            'last_name' => $validated['last_name'] ?? '',
            'company' => $validated['company'] ?? '',
            'rfc' => $validated['rfc'] ?? null,
            'phone' => $validated['phone'] ?? '',
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);
        $user->role = $validated['role'];
        // role_id only ever means something for role=admin; silently ignore
        // whatever the client sent for any other role rather than trusting it.
        $user->role_id = $validated['role'] === 'admin' ? ($validated['role_id'] ?? null) : null;
        $user->status = $request->boolean('status', true) ? 'active' : 'inactive';
        $user->save();

        try {
            Mail::to($user->email)->send(new AccountCreatedMail($user->name, $user->email, $validated['password']));
            \Log::info("Correo enviado correctamente para el usuario de personal ({$user->role}): {$user->email}");
        } catch (\Throwable $e) {
            \Log::error('Mail send error (staff user create): ' . $e->getMessage());
        }

        $roleLabel = self::ROLE_LABELS[$user->role] ?? $user->role;

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => "Nuevo {$roleLabel} creado con éxito."]);
        }

        toastr("Nuevo {$roleLabel} Creado", 'success', 'success');
        return redirect()->route('admin.staff-users.index');
    }

    public function update(Request $request, string $id)
    {
        $staffUser = User::whereIn('role', self::STAFF_ROLES)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'max:200'],
            'last_name' => ['max:100'],
            'company' => ['max:200'],
            'rfc' => ['nullable', 'max:14', Rule::unique('users', 'rfc')->ignore($staffUser->id)],
            'phone' => ['max:15'],
            'role' => ['required', Rule::in(self::STAFF_ROLES)],
            'role_id' => ['nullable', 'exists:roles,id'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($staffUser->id)],
            'password' => ['nullable', 'min:8', 'confirmed'],
            'status' => ['nullable'],
        ]);

        $staffUser->fill([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'] ?? '',
            'company' => $validated['company'] ?? '',
            'rfc' => $validated['rfc'] ?? null,
            'phone' => $validated['phone'] ?? '',
            'email' => $validated['email'],
        ]);
        if (!empty($validated['password'])) {
            $staffUser->password = bcrypt($validated['password']);
        }
        $staffUser->role = $validated['role'];
        $staffUser->role_id = $validated['role'] === 'admin' ? ($validated['role_id'] ?? null) : null;
        $staffUser->status = $request->boolean('status', true) ? 'active' : 'inactive';
        $staffUser->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Usuario de personal actualizado con éxito.']);
        }

        toastr('Actualizacion con exito', 'success');
        return redirect()->route('admin.staff-users.index');
    }

    public function destroy(string $id)
    {
        $staffUser = User::whereIn('role', self::STAFF_ROLES)->findOrFail($id);

        abort_if($staffUser->is_protected, 403, 'No se puede eliminar esta cuenta protegida.');

        $staffUser->delete();

        return response(['status' => 'success', 'message' => 'Borrado con Exito']);
    }

    public function changeStatus(Request $request)
    {
        $staffUser = User::whereIn('role', self::STAFF_ROLES)->findOrFail($request->id);
        $staffUser->status = $request->status == 'true' ? 'active' : 'inactive';
        $staffUser->save();

        return response(['message' => 'Status has been updated!']);
    }
}
