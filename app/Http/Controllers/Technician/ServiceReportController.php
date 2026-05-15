<?php
// app/Http/Controllers/Technician/ServiceReportController.php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\ServiceReport;
use Illuminate\Http\Request;

class ServiceReportController extends Controller
{
    public function index()
    {
        $reports = ServiceReport::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('technician.reports.index', compact('reports'));
    }

    public function create()
    {
        return view('technician.reports.create');
    }

    public function generateFolio()
    {
        $last = ServiceReport::whereRaw("folio REGEXP '^MAC-[0-9]{8}-[0-9]{4}$'")
            ->orderBy('id', 'desc')
            ->value('folio');

        $seq = $last ? (intval(substr($last, -4)) + 1) : 1;

        $date = now()->format('Ymd');

        return response()->json(['folio' => 'MAC-' . $date . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT)]);
    }

    public function uploadFotos(Request $request, $id)
    {
        $report = ServiceReport::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $fotos  = $report->fotos ?? ['antes' => [], 'despues' => []];
        $tipo   = in_array($request->input('tipo'), ['antes', 'despues']) ? $request->input('tipo') : 'antes';

        foreach ($request->file('fotos', []) as $file) {
            $path          = $file->store("service-reports/{$id}", 'public');
            $fotos[$tipo][] = ['path' => $path, 'url' => asset('storage/' . $path)];
        }

        $report->fotos = $fotos;
        $report->save();

        return response()->json(['status' => 'ok', 'fotos' => $fotos]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'folio'          => 'required|string|unique:service_reports,folio',
            'fecha_servicio' => 'required|date',
            'tipo_servicio'  => 'required|string',
            'tecnico_nombre' => 'required|string|max:255',
        ]);

        $report = ServiceReport::create([
            'user_id'        => auth()->id(),
            'folio'          => $request->folio,
            'fecha_servicio' => $request->fecha_servicio,
            'tipo_servicio'  => $request->tipo_servicio,
            'tecnico_nombre' => $request->tecnico_nombre,
        ]);

        return response()->json(['id' => $report->id, 'status' => 'ok']);
    }

    public function update(Request $request, $id)
    {
        $report = ServiceReport::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $allowed = [
            'fecha_servicio', 'tipo_servicio', 'tecnico_nombre',
            'cliente_nombre', 'cliente_empresa', 'cliente_rfc',
            'cliente_direccion', 'cliente_telefono', 'cliente_email',
            'equipo_descripcion', 'equipo_marca', 'equipo_modelo',
            'equipo_serie', 'equipo_ubicacion_tag',
            'mediciones', 'observaciones', 'recomendaciones',
        ];

        $report->fill($request->only($allowed))->save();

        return response()->json(['status' => 'ok']);
    }

    public function complete(Request $request, $id)
    {
        $report = ServiceReport::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $report->firma_tecnico = $request->input('firma_tecnico');
        $report->status        = 'completed';
        $report->save();

        return response()->json(['status' => 'ok', 'report' => $report]);
    }

    public function show($id)
    {
        $report = ServiceReport::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json($report);
    }

    public function destroy($id)
    {
        $report = ServiceReport::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $report->delete();

        return response()->json(['status' => 'success', 'message' => 'Reporte eliminado correctamente.']);
    }
}
