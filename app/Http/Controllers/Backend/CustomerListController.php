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
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class CustomerListController extends Controller
{
    public function index()
    {
        return view('admin-ui.customer.index');
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
