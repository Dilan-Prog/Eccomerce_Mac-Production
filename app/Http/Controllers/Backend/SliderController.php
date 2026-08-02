<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SliderDataTable;
use App\Http\Controllers\Controller;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\SliderTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Image;
use Illuminate\Support\Facades\Cache;
use PhpParser\Node\Stmt\Return_;

class SliderController extends Controller
{
    use ImageUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin-ui.slider.index');
    }

    /** JSON data source for the custom admin table (replaces SliderDataTable). */
    public function tableData(Request $request, SliderTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every slider matching the current filter/search (replaces the Yajra-Buttons export). */
    public function export(Request $request, SliderTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Slider',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('slider.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "slider.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar (mirrors destroy()'s side effects: image cleanup). */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $sliders = Slider::whereIn('id', $ids)->get();
            foreach ($sliders as $slider) {
                $this->deleteImage($slider->banner);
                $this->deleteImage($slider->banner_laptop);
                $this->deleteImage($slider->banner_tablet);
                $this->deleteImage($slider->banner_phone);
                $slider->delete();
            }

            return response()->json(['status' => 'success', 'message' => count($sliders) . ' slider(s) eliminado(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.slider.create');
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.slider._form');
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $slider = Slider::findOrFail($id);
        return view('admin-ui.slider._form', compact('slider'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'banner'=>['required_without:banner_from_library','nullable','image','mimes:jpeg,png,jpg,gif,svg,webp','max:2000'],
            'banner_from_library'=>['required_without:banner','nullable','string'],
            'type' =>['nullable','string','max:200'],
            'title' => ['max:200'],
            'starting_price'=>['max:200'],
            'btn_url' => ['url'],

            'serial' => ['required', 'integer'],
            'status'=>['required']
        ]);

        $slider = new Slider();

        /**header file Upload */
        $imagePathComputers = $this->resolveOrCopyImage($request,'banner','banner_from_library','uploads/slider/webp/computers',null,1250,417);
        $imagePathLaptop = $this->resolveOrCopyImage($request,'banner','banner_from_library','uploads/slider/webp/laptop',null,1140,380);
        $imagePathTablet = $this->resolveOrCopyImage($request,'banner','banner_from_library','uploads/slider/webp/tablet',null,720,240);
        $imagePathPhone = $this->resolveOrCopyImage($request,'banner','banner_from_library','uploads/slider/webp/phone',null,370,125);

        $slider->banner=$imagePathComputers;
        $slider->banner_laptop=$imagePathLaptop;
        $slider->banner_tablet=$imagePathTablet;
        $slider->banner_phone=$imagePathPhone;

        $slider->type = $request->type;
        $slider->title = $request->title;
        $slider->starting_price = $request->starting_price;
        $slider->btn_url = $request->btn_url;

        $slider->serial = $request->serial;
        $slider->status = $request->status;
        $slider->save();

        Cache::forget('sliders');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Slider creado con éxito.']);
        }

        toastr('Creado con exito','success');
        return redirect()->back();



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
        $slider = Slider::findOrFail($id);
        return view('admin.slider.edit', compact('slider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'banner'=>['nullable','image','mimes:jpeg,png,jpg,gif,svg,webp','max:2000'],
            'banner_from_library'=>['nullable','string'],
            'type' =>['nullable','string','max:200'],
            'title' => ['max:200'],
            'starting_price'=>['max:200'],
            'btn_url' => ['url'],
            'serial' => ['required', 'integer'],
            'status'=>['required']
        ]);

        $slider = Slider::findOrFail($id);

        if ($request->hasFile('banner') || $request->filled('banner_from_library')) {
            // Verifica y elimina solo si existe. deleteImage() strips the app
            // URL from the stored value first, which is what this codebase
            // persists (asset() URLs, not relative paths) — resolveOrCopyImage()'s
            // own $oldPath cleanup expects a relative path, so it is passed
            // null here and the old files are removed via deleteImage() instead,
            // same as this controller did before the gallery picker existed.
            if (!empty($slider->banner)) {
                $this->deleteImage($slider->banner);
            }
            if (!empty($slider->banner_laptop)) {
                $this->deleteImage($slider->banner_laptop);
            }
            if (!empty($slider->banner_tablet)) {
                $this->deleteImage($slider->banner_tablet);
            }
            if (!empty($slider->banner_phone)) {
                $this->deleteImage($slider->banner_phone);
            }

            // Subir nuevas imágenes (o copiar la seleccionada desde la galería)
            $slider->banner = $this->resolveOrCopyImage($request, 'banner', 'banner_from_library', 'uploads/slider/webp/computers', null, 1320, 700);
            $slider->banner_laptop = $this->resolveOrCopyImage($request, 'banner', 'banner_from_library', 'uploads/slider/webp/laptop', null, 1140, 380);
            $slider->banner_tablet = $this->resolveOrCopyImage($request, 'banner', 'banner_from_library', 'uploads/slider/webp/tablet', null, 720, 240);
            $slider->banner_phone = $this->resolveOrCopyImage($request, 'banner', 'banner_from_library', 'uploads/slider/webp/phone', null, 370, 125);
        }

        $slider->type = $request->type;
        $slider->title = $request->title;
        $slider->starting_price = $request->starting_price;
        $slider->btn_url = $request->btn_url;

        $slider->serial = $request->serial;
        $slider->status = $request->status;
        $slider->save();

        Cache::forget('sliders');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Slider actualizado con éxito.']);
        }

        toastr('Actualizacion con exito','success');
        return redirect()->route('admin.slider.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $slider = Slider::findOrFail($id);
             $this->deleteImage($slider->banner);
             $this->deleteImage($slider->banner_laptop);
             $this->deleteImage($slider->banner_tablet);
             $this->deleteImage($slider->banner_phone);
             $slider->delete();

             return response(['status' => 'success', 'message' => 'Borrado Correctamente']);


    }
}
