<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\AdminListDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\AdminListTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class AdminListController extends Controller
{
    public function index()
    {
        return view('admin-ui.admin-list.index');
    }

    /** JSON data source for the custom admin table (replaces AdminListDataTable). */
    public function tableData(Request $request, AdminListTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every admin matching the current filter/search (replaces the Yajra-Buttons export). */
    public function export(Request $request, AdminListTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Administradores',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('administradores.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "administradores.{$extension}", $writerType);
    }

    /**
     * Bulk actions from the table's multi-select bar. Mirrors the single
     * destory() side effect exactly (a plain delete, no related cleanup) and
     * preserves the same id-1 (protected super-admin) guard that the old
     * DataTable template enforced by hiding the delete button.
     */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), fn ($id) => $id != 1);
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $admins = User::whereIn('id', $ids)->get();
            foreach ($admins as $admin) {
                $admin->delete();
            }
            return response()->json(['status' => 'success', 'message' => count($admins) . ' administrador(es) eliminado(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    public function changeStatus(Request $request)
    {
        $admin = User::findOrFail($request->id);
        $admin->status = $request->status == 'true' ? 'active' : 'inactive';
        $admin->save();

        return response(['message' => 'Status has been updated!']);
    }

    public function destory(string $id)
    {
        $admin = User::findOrFail($id);

        // Eliminar el usuario admin
        $admin->delete();

        return response(['status' => 'success', 'message' => 'Borrado con Exito']);

    }
}
