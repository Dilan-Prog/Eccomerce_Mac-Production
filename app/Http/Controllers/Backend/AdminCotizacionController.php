<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\CotizacionTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class AdminCotizacionController extends Controller
{
    public function index(Request $request)
    {
        return view('admin-ui.cotizaciones.index');
    }

    /** JSON data source for the custom admin table (replaces the hand-rolled paginated query above). */
    public function tableData(Request $request, CotizacionTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every cotización matching the current filter/search. */
    public function export(Request $request, CotizacionTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Cotizaciones',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('cotizaciones.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "cotizaciones.{$extension}", $writerType);
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load(['user', 'perfil']);
        $pdfUrl = $cotizacion->pdf_path ? Storage::url($cotizacion->pdf_path) : null;

        return view('admin.cotizaciones.show', compact('cotizacion', 'pdfUrl'));
    }
}
