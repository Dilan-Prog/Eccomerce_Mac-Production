<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\BankAccountTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:settings');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin-ui.bank-account.index');
    }

    /** JSON data source for the custom admin table. */
    public function tableData(Request $request, BankAccountTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every bank account matching the current filter/search. */
    public function export(Request $request, BankAccountTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Cuentas Bancarias',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('cuentas-bancarias.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "cuentas-bancarias.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $accounts = BankAccount::whereIn('id', $ids)->get();
            foreach ($accounts as $account) {
                $account->delete();
            }
            return response()->json(['status' => 'success', 'message' => count($accounts) . ' cuenta(s) eliminada(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return null;
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.bank-account._form');
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        return view('admin-ui.bank-account._form', compact('bankAccount'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'banco' => ['required', 'string', 'max:200'],
            'titular' => ['required', 'string', 'max:200'],
            'numero_cuenta' => ['nullable', 'string', 'max:200'],
            'numero_tarjeta' => ['nullable', 'string', 'max:200'],
            'clabe' => ['nullable', 'string', 'max:200'],
            'status' => ['required'],
        ]);

        $bankAccount = new BankAccount();
        $bankAccount->banco = $request->banco;
        $bankAccount->titular = $request->titular;
        $bankAccount->numero_cuenta = $request->numero_cuenta;
        $bankAccount->numero_tarjeta = $request->numero_tarjeta;
        $bankAccount->clabe = $request->clabe;
        $bankAccount->status = $request->status;
        $bankAccount->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Cuenta bancaria creada con éxito.']);
        }

        toastr('Creado Con Extio', 'success', 'Success');
        return redirect()->route('admin.bank-account.index');
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
        $request->validate([
            'banco' => ['required', 'string', 'max:200'],
            'titular' => ['required', 'string', 'max:200'],
            'numero_cuenta' => ['nullable', 'string', 'max:200'],
            'numero_tarjeta' => ['nullable', 'string', 'max:200'],
            'clabe' => ['nullable', 'string', 'max:200'],
            'status' => ['required'],
        ]);

        $bankAccount = BankAccount::findOrFail($id);
        $bankAccount->banco = $request->banco;
        $bankAccount->titular = $request->titular;
        $bankAccount->numero_cuenta = $request->numero_cuenta;
        $bankAccount->numero_tarjeta = $request->numero_tarjeta;
        $bankAccount->clabe = $request->clabe;
        $bankAccount->status = $request->status;
        $bankAccount->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Cuenta bancaria actualizada con éxito.']);
        }

        toastr('Actualizado Con Extio', 'success', 'Success');
        return redirect()->route('admin.bank-account.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $bankAccount->delete();
        return response(['status' => 'success', 'message' => 'Borrado con exito']);
    }
}
