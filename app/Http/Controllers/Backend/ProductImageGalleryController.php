<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImageGallery;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\ProductImageGalleryTableQuery;
use App\Support\UploadPath;
use App\Traits\ImageUploadTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class ProductImageGalleryController extends Controller
{
    use ImageUploadTrait;

    public function __construct()
    {
        $this->middleware('can-access-module:products');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $product = Product::findOrFail($request->product);
        return view('admin-ui.products-image-gallery.index', compact('product'));
    }

    /** JSON data source for the custom admin table (replaces ProductImageGalleryDataTable). */
    public function tableData(Request $request, ProductImageGalleryTableQuery $table)
    {
        $table->forProduct((int) $request->input('product'));

        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every image matching the current filter/search (replaces the Yajra-Buttons export). */
    public function export(Request $request, ProductImageGalleryTableQuery $table)
    {
        $table->forProduct((int) $request->input('product'));

        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Galeria de Imagenes de Producto',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('galeria-de-imagenes.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "galeria-de-imagenes.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $images = ProductImageGallery::whereIn('id', $ids)->get();
            foreach ($images as $image) {
                $this->deleteImage($image->image);
                $image->delete();
            }
            return response()->json(['status' => 'success', 'message' => count($images) . ' imagen(es) eliminada(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — multi-image upload scoped to the current product. */
    public function createFragment(Request $request)
    {
        $product = Product::findOrFail($request->product);
        return view('admin-ui.products-image-gallery._form', compact('product'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image.*' => ['sometimes', 'image', 'max:2048'],
            'image_from_library' => ['sometimes', 'array'],
            'image_from_library.*' => ['string'],
        ]);

        $libraryPaths = array_values(array_filter((array) $request->input('image_from_library', [])));

        // Either source can supply images, but at least one is required —
        // mirrors the old plain 'image.*' => 'required' rule, now that a
        // fresh file isn't the only way to add a gallery image.
        if (!$request->hasFile('image') && empty($libraryPaths)) {
            throw ValidationException::withMessages([
                'image' => 'Selecciona al menos una imagen (subida o desde la galería).',
            ]);
        }

        /**Handle Image Upload */
        $imagePaths = $this->uploadMultiImage($request, 'image', 'uploads', true) ?? [];

        /** Handle images picked from the Media Library gallery: copy + reprocess
         *  each through the same watermark/webp pipeline uploadMultiImage() uses
         *  for fresh uploads, so every gallery row ends up in the same
         *  relative-path format under uploads/, regardless of its source. */
        foreach ($libraryPaths as $libraryPath) {
            $copiedPath = $this->copyGalleryImageFromLibrary($libraryPath, 'uploads', true);
            if ($copiedPath) {
                $imagePaths[] = $copiedPath;
            }
        }

        foreach($imagePaths as $path){
            $productImageGallery = new ProductImageGallery();
            $productImageGallery->image = $path;
            $productImageGallery->product_id = $request->product;
            $productImageGallery->save();


        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Galería de imágenes creada con éxito.']);
        }

        toastr('Galeria de Imagenes Subida Con exito');
        return redirect()->back();


    }

    /**
     * Copies one Media Library path (e.g. "uploads/product/foo.webp") into this
     * module's own uploads folder, reprocessed through the same Intervention
     * watermark/webp pipeline uploadMultiImage() already applies to fresh
     * uploads — so a library-picked gallery row is indistinguishable in
     * format from a freshly uploaded one (a relative path, matching this
     * module's existing `image` column convention; see the frontend's
     * asset($galleryImage->image) usage).
     *
     * Deliberately NOT ImageUploadTrait::resolveOrCopyImage(): that helper
     * returns a full asset() URL, built for single "main image" fields
     * (Brand logo, Slider image). Reusing it here would store gallery rows
     * in a different format than fresh multi-uploads, breaking asset() on
     * the frontend for the mixed rows. Uses the same path-traversal guard
     * style as resolveOrCopyImage() / MediaLibraryController::destroy().
     */
    private function copyGalleryImageFromLibrary(string $libraryPath, string $path, bool $watermark = false): ?string
    {
        $sourceFull = realpath(UploadPath::full($libraryPath));
        $uploadsRoot = realpath(UploadPath::full('uploads'));
        if (!$sourceFull || !$uploadsRoot || !str_starts_with($sourceFull, $uploadsRoot)) {
            return null;
        }

        $manager = new ImageManager(new Driver());
        $imageName = 'media_' . uniqid() . '.webp';

        $img = $manager->read($sourceFull);
        if ($watermark) {
            $img = $this->applyWatermark($img);
        }
        $img->encode(new WebpEncoder(quality: 75));

        $uploadPath = UploadPath::full($path . '/');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        $img->save($uploadPath . $imageName);

        return $path . '/' . $imageName;
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productImage = ProductImageGallery::findOrFail($id);
        $this->deleteImage($productImage->image);
        $productImage->delete();
        return response(['status' => 'success', 'message' => 'Borrado con exito']);
    }
}
