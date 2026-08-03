<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\CustomerTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class CustomerListController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:clientes');
    }

    public function index()
    {
        return view('admin-ui.customer.index');
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.customer._form');
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $customer = User::where('role', 'user')->findOrFail($id);

        return view('admin-ui.customer._form', compact('customer'));
    }

    /**
     * Admin-side registration of a brand-new client. Low-ceremony: the account
     * is created with a random password (no activation-email flow — out of
     * scope here); the admin can reset access separately if the client ever
     * needs to log in themselves.
     *
     * `sin_datos_contacto` (checkbox, create-only — see _form.blade.php) lets
     * the admin proceed without correo/RFC/teléfono/empresa on hand, e.g.
     * while building a quote fast. `name` stays required — everything else
     * becomes optional. `email` still can't be truly empty (NOT NULL + UNIQUE
     * at the DB level), so a unique placeholder is generated instead; the
     * admin fills in the real one later via the edit modal.
     */
    public function store(Request $request)
    {
        $skipContactInfo = $request->boolean('sin_datos_contacto');

        $request->validate([
            'name' => ['required', 'max:200'],
            'email' => [$skipContactInfo ? 'nullable' : 'required', 'nullable', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'max:15'],
            'company' => ['nullable', 'max:200'],
            'rfc' => ['nullable', 'max:14'],
            'account_type' => ['nullable', 'in:personal,b2b'],
            'tipo_cliente' => ['nullable', 'in:revendedor,tecnico,empresa,contratista'],
        ]);

        $customer = new User();
        $customer->name = $request->name;
        $customer->last_name = ''; // not part of this simplified admin form; matches self-registration's default (RegisteredUserController)
        $customer->email = $request->email ?: $this->generatePlaceholderEmail();
        $customer->phone = $request->phone;
        $customer->company = $request->company;
        $customer->rfc = $request->rfc;
        $customer->account_type = $request->account_type ?: 'personal';
        $customer->tipo_cliente = $request->tipo_cliente;
        $customer->role = 'user';
        $customer->status = 'active';
        $customer->password = bcrypt(Str::random(16));
        $customer->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cliente creado con éxito.',
            'client' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
        ]);
    }

    /** Unique, obviously-fake placeholder — used only when the admin explicitly skips correo at creation. */
    private function generatePlaceholderEmail(): string
    {
        do {
            $candidate = 'pendiente-' . Str::random(10) . '@sin-correo.macdelnorte.com';
        } while (User::where('email', $candidate)->exists());

        return $candidate;
    }

    /** Edits an existing customer's info. Does not touch role/password. */
    public function update(Request $request, string $id)
    {
        $customer = User::where('role', 'user')->findOrFail($id);

        $request->validate([
            'name' => ['required', 'max:200'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($customer->id)],
            'phone' => ['nullable', 'max:15'],
            'company' => ['nullable', 'max:200'],
            'rfc' => ['nullable', 'max:14'],
            'account_type' => ['nullable', 'in:personal,b2b'],
            'tipo_cliente' => ['nullable', 'in:revendedor,tecnico,empresa,contratista'],
        ]);

        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->company = $request->company;
        $customer->rfc = $request->rfc;
        $customer->account_type = $request->account_type ?: 'personal';
        $customer->tipo_cliente = $request->tipo_cliente;
        $customer->save();

        return response()->json(['status' => 'success', 'message' => 'Cliente actualizado con éxito.']);
    }

    /** JSON data source for the custom admin table (replaces CustomerListDataTable). */
    public function tableData(Request $request, CustomerTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every customer matching the current filter/search (replaces the Yajra-Buttons export). */
    public function export(Request $request, CustomerTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Usuarios/Clientes',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('clientes.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "clientes.{$extension}", $writerType);
    }

    public function changeStatus(Request $request)
    {
        $customer = User::findOrFail($request->id);
        $customer->status = $request->status == 'true' ? 'active' : 'inactive';
        $customer->save();

        return response(['message' => 'Status has been updated!']);
    }

    public function uploadCsf(Request $request, User $user)
    {
        $request->validate([
            'csf_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        if ($user->csf_path) {
            Storage::delete($user->csf_path);
        }

        $user->csf_path   = $request->file('csf_file')->store('csf');
        $user->b2b_status = 'pending';
        $user->save();

        return response()->json(['message' => 'CSF subido correctamente.']);
    }

    public function viewCsf(User $user)
    {
        abort_unless($user->csf_path && Storage::exists($user->csf_path), 404);

        return Storage::response($user->csf_path, null, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function b2bStatus(Request $request, User $user)
    {
        $request->validate([
            'b2b_status' => ['required', 'in:pending,verified,rejected'],
        ]);

        $user->b2b_status = $request->b2b_status;
        $user->save();

        return response()->json(['message' => 'Estado B2B actualizado.']);
    }
}
