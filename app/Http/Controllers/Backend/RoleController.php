<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Models\User;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\RoleTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Str;

class RoleController extends Controller
{
    public function __construct()
    {
        // This controller manages roles/permissions themselves, so it must
        // only ever be reachable by an unrestricted admin (role === 'admin'
        // and role_id === null). A restricted custom-role admin must never
        // be able to manage roles — even one that has been granted the
        // "staff" module — so this is enforced here independently of
        // whatever module-permission middleware is wired up elsewhere.
        //
        // Registered via $this->middleware() (deferred), NOT evaluated
        // directly in the constructor body. Laravel's router instantiates
        // every controller merely to read getMiddleware() — e.g. while
        // building the route list, or while gathering a real request's
        // middleware stack, which happens *before* that request's own
        // 'web'/'auth'/'role:admin' pipeline (and therefore session/auth
        // resolution) has run. An immediate abort_if(auth()->user()...) in
        // the constructor body would evaluate at that too-early point —
        // breaking `php artisan route:list` and, worse, incorrectly
        // rejecting legitimate admins on real cookie-based requests before
        // their session was even loaded. Deferring via $this->middleware()
        // ensures this closure only runs once the pipeline reaches it, i.e.
        // strictly after 'web' (session) and 'auth'/'role:admin' — so
        // $request->user() is fully resolved by then.
        $this->middleware(function (Request $request, \Closure $next) {
            $user = $request->user();
            abort_if(!$user || $user->role !== 'admin' || $user->role_id !== null, 403);
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin-ui.roles.index');
    }

    /** JSON data source for the custom admin table. */
    public function tableData(Request $request, RoleTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every role matching the current filter/search. */
    public function export(Request $request, RoleTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Roles y Permisos',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('roles.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "roles.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar (mirrors destroy()'s guards). */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $roles = Role::whereIn('id', $ids)->get();
            $deleted = 0;
            $skipped = 0;
            foreach ($roles as $role) {
                if ($role->is_system || User::where('role_id', $role->id)->exists()) {
                    $skipped++;
                    continue;
                }
                $role->delete();
                $deleted++;
            }

            $message = "{$deleted} rol(es) eliminado(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} rol(es) no se eliminaron porque son de sistema o tienen usuarios asignados.";
            }

            return response()->json(['status' => $deleted > 0 ? 'success' : 'error', 'message' => $message]);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.roles._form');
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $role = Role::findOrFail($id);
        return view('admin-ui.roles._form', compact('role'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', $this->notSystemNameRule()],
        ]);

        $role = DB::transaction(function () use ($request) {
            $role = new Role();
            $role->name = $request->name;
            $role->slug = $this->uniqueSlug($request->name);
            $role->is_system = false;
            $role->save();

            $this->syncModulePermissions($role, $request->input('modules', []), $request->input('module_actions', []));

            return $role;
        });

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Rol creado con éxito.']);
        }

        toastr('Rol creado con éxito');
        return redirect()->route('admin.roles.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:100', $this->notSystemNameRule($role)],
        ]);

        DB::transaction(function () use ($request, $role) {
            if (!$role->is_system) {
                // Only rename/re-slug custom roles — system roles keep their
                // seeded name/slug regardless of what a bypassed request sends.
                if ($role->name !== $request->name) {
                    $role->slug = $this->uniqueSlug($request->name, $role->id);
                }
                $role->name = $request->name;
                $role->save();

                $this->syncModulePermissions($role, $request->input('modules', []), $request->input('module_actions', []));
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Rol actualizado con éxito.']);
        }

        toastr('Rol actualizado con éxito', 'success');
        return redirect()->route('admin.roles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        abort_if($role->is_system, 403);

        if (User::where('role_id', $role->id)->exists()) {
            return response(['status' => 'error', 'message' => 'Este rol tiene usuarios asignados, no se puede eliminar.']);
        }

        $role->delete();

        return response(['status' => 'success', 'message' => 'Eliminado con éxito!']);
    }

    /**
     * Builds a closure-based validation rule rejecting any name that collides
     * (case-insensitive, trimmed) with an existing system role's name, or
     * whose derived slug collides with an existing system role's slug.
     *
     * Rule::notIn() only does exact/case-sensitive matching, so a plain
     * "vendedor" would slip past it even though "Vendedor" is reserved —
     * hence the manual strcasecmp() comparison here. This runs as part of
     * $request->validate(), i.e. before uniqueSlug() is ever called, so a
     * collision surfaces as this specific, clear message instead of being
     * silently absorbed into a "-2" suffix by uniqueSlug()'s generic
     * uniqueness loop.
     *
     * When editing ($editingRole given), resubmitting that role's own
     * already-saved name is not a new attempt to claim a reserved name —
     * some custom roles were created before this rule existed and happen to
     * share a name with a system role added later, so every edit to them
     * (even just toggling a module checkbox) would otherwise fail this check
     * forever. Only an actual rename to a new colliding value is blocked.
     */
    private function notSystemNameRule(?Role $editingRole = null): \Closure
    {
        $systemNames = Role::where('is_system', true)->pluck('name');
        $systemSlugs = Role::where('is_system', true)->pluck('slug');

        return function (string $attribute, mixed $value, \Closure $fail) use ($systemNames, $systemSlugs, $editingRole) {
            $trimmed = trim((string) $value);

            if ($editingRole && strcasecmp(trim($editingRole->name), $trimmed) === 0) {
                return;
            }

            $collidesByName = $systemNames->contains(
                fn ($systemName) => strcasecmp(trim($systemName), $trimmed) === 0
            );
            $collidesBySlug = $systemSlugs->contains(Str::slug($trimmed));

            if ($collidesByName || $collidesBySlug) {
                $fail('Ese nombre ya está reservado para un rol del sistema y no se puede usar.');
            }
        };
    }

    /** Generates a unique slug for the given name, appending -2, -3, ... on collision. */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (
            Role::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Replaces every role_module_permissions row for $role: $moduleKeys son
     * los checkboxes planos de los módulos "todo o nada" (validados contra
     * config('admin-modules')); $moduleActions trae los checkboxes por
     * acción (Ver/Crear/Editar/Borrar/Exportar) de los 3 módulos granulares
     * (RoleModulePermission::GRANULAR_MODULE_KEYS), en la forma
     * module_actions[moduleKey][action]=1.
     */
    private function syncModulePermissions(Role $role, array $moduleKeys, array $moduleActions = []): void
    {
        $validKeys = array_keys(config('admin-modules'));
        $granularKeys = RoleModulePermission::GRANULAR_MODULE_KEYS;

        $moduleKeys = array_values(array_diff(array_intersect($moduleKeys, $validKeys), $granularKeys));

        RoleModulePermission::where('role_id', $role->id)->delete();

        foreach ($moduleKeys as $moduleKey) {
            RoleModulePermission::create([
                'role_id' => $role->id,
                'module_key' => $moduleKey,
                'can_access' => true,
            ]);
        }

        foreach ($granularKeys as $moduleKey) {
            $checked = $moduleActions[$moduleKey] ?? [];
            $flags = [];
            foreach (RoleModulePermission::GRANULAR_ACTIONS as $action) {
                $flags['can_' . $action] = !empty($checked[$action]);
            }

            // Nada marcado = sin fila, mismo criterio que los demás módulos.
            if (!array_filter($flags)) {
                continue;
            }

            RoleModulePermission::create(array_merge([
                'role_id' => $role->id,
                'module_key' => $moduleKey,
                // can_access refleja "Ver": sin Ver el módulo no es
                // alcanzable aunque otras acciones estén marcadas.
                'can_access' => $flags['can_view'],
            ], $flags));
        }
    }
}
