<?php
// app/Http/Controllers/Technician/ServiceReportController.php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\ServiceReport;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;

class ServiceReportController extends Controller
{
    use ImageUploadTrait;

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
        $last = ServiceReport::whereRaw("folio REGEXP '^MAC-[0-9]{6}-M[0-9]{4}$'")
            ->orderBy('id', 'desc')
            ->value('folio');

        $startSeq = 3537;

        if ($last) {
            preg_match('/M?([0-9]{1,})$/', $last, $m);
            $lastNum = isset($m[1]) ? intval($m[1]) : 0;
            $seq = $lastNum + 1;
            if ($seq < $startSeq) $seq = $startSeq;
        } else {
            $seq = $startSeq;
        }

        $date = now()->format('dmy');

        return response()->json(['folio' => 'MAC-' . $date . '-M' . str_pad($seq, 4, '0', STR_PAD_LEFT)]);
    }

    /**
     * Sube las fotos usando el mismo mecanismo de UPLOADS_BASE_PATH que el
     * resto del proyecto (App\Traits\ImageUploadTrait::uploadMultiImage() ->
     * App\Support\UploadPath), en vez de $file->store(..., 'public')
     * (storage/app/public, symlink de Laravel) que se usaba antes. Así las
     * fotos sobreviven un deploy/git reset igual que las de Marca/Slider/
     * Producto, y se sirven con el mismo patrón de URL en toda la app.
     */
    public function uploadFotos(Request $request, $id)
    {
        $report = ServiceReport::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $fotos  = $report->fotos ?? ['antes' => [], 'despues' => []];
        $tipo   = in_array($request->input('tipo'), ['antes', 'despues']) ? $request->input('tipo') : 'antes';

        $paths = $this->uploadMultiImage($request, 'fotos', "uploads/service-reports/{$id}") ?? [];

        foreach ($paths as $path) {
            $fotos[$tipo][] = ['path' => $path, 'url' => asset($path)];
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

        // Regenerar URLs de fotos desde path (evita URLs stale de otro entorno).
        // Compatibilidad: las fotos subidas ANTES de este fix tienen un path
        // tipo "service-reports/{id}/archivo.jpg" servido vía el disco
        // 'public' de Laravel (storage/app/public); las subidas después usan
        // "uploads/service-reports/{id}/archivo.webp" vía UploadPath — cada
        // una necesita su propio prefijo al reconstruir la URL.
        if (!empty($report->fotos)) {
            $fotos = $report->fotos;
            foreach (['antes', 'despues'] as $tipo) {
                foreach ($fotos[$tipo] ?? [] as &$foto) {
                    if (empty($foto['path'])) {
                        continue;
                    }
                    $foto['url'] = str_starts_with($foto['path'], 'uploads/')
                        ? asset($foto['path'])
                        : asset('storage/' . $foto['path']);
                }
                unset($foto);
            }
            $report->fotos = $fotos;
        }

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
