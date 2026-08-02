<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TrackConversion;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\TrackConversionTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackConversionController extends Controller
{
    //
    public function index()
    {
        return view('admin-ui.track-conversion.index');
    }

    /** JSON data source for the custom admin table (replaces TrackConversionDataTable). */
    public function tableData(Request $request, TrackConversionTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /**
     * Excel/CSV/PDF export of every conversion matching the current filter/search
     * (replaces the Yajra-Buttons export).
     */
    public function export(Request $request, TrackConversionTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Seguimiento de Conversiones',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('seguimiento-conversiones.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "seguimiento-conversiones.{$extension}", $writerType);
    }

    // Note: no bulkAction() here — the old view/controller never exposed a
    // delete/destroy action for this module (read-only tracking log fed by
    // store(), used by the public-facing tracking pixel), so there is nothing
    // for a bulk-delete bar to call.

    public function store(Request $request)
    {
        $data = $request->validate([
            'gclid' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'landing_page' => ['nullable', 'string', 'max:255'],
        ]);

        TrackConversion::create($data);
        Log::info('Conversion Rastreada', [
            'gclid' => $data['gclid'],
            'type' => $data['type'],
            'utm_source' => $data['utm_source'],
            'utm_medium' => $data['utm_medium'],
            'utm_campaign' => $data['utm_campaign'],
            'landing_page' => $data['landing_page'],
        ]);
        return response()->json(['success' => true]);
    }
}
