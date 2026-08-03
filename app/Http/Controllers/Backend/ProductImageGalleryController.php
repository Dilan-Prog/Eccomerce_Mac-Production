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
use Illuminate\Support\Str;
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
                if (!empty($image->image_laptop)) {
                    $this->deleteImage($image->image_laptop);
                }
                if (!empty($image->image_tablet)) {
                    $this->deleteImage($image->image_tablet);
                }
                if (!empty($image->image_phone)) {
                    $this->deleteImage($image->image_phone);
                }
                if (!empty($image->image_carrusel)) {
                    $this->deleteImage($image->image_carrusel);
                }
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

        $product = Product::with('brand')->findOrFail($request->product);
        $brandSlug = Str::slug($product->brand->name);

        /** Handle fresh uploads: each file gets the same 5 responsive sizes
         *  (computers/laptop/tablet/phone/carrusel) the product's own main
         *  image gets, stored in the same per-brand folder tree. */
        if ($request->hasFile('image')) {
            foreach ((array) $request->file('image') as $file) {
                $variants = $this->generateResponsiveVariants($file->getRealPath(), $brandSlug, true);
                $this->saveGalleryRow($variants, $request->product);
            }
        }

        /** Handle images picked from the Media Library gallery: resolve +
         *  guard against path traversal the same way this controller always
         *  has, then run the file through the same responsive pipeline as
         *  fresh uploads, so every gallery row ends up with the same 5
         *  columns regardless of its source. */
        $uploadsRoot = realpath(UploadPath::full('uploads'));
        foreach ($libraryPaths as $libraryPath) {
            $sourceFull = realpath(UploadPath::full($libraryPath));
            if (!$sourceFull || !$uploadsRoot || !str_starts_with($sourceFull, $uploadsRoot)) {
                continue;
            }

            $variants = $this->generateResponsiveVariants($sourceFull, $brandSlug, true);
            $this->saveGalleryRow($variants, $request->product);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Galería de imágenes creada con éxito.']);
        }

        toastr('Galeria de Imagenes Subida Con exito');
        return redirect()->back();


    }

    /**
     * Generates the same 5 responsive size variants (computers/laptop/tablet/
     * phone/carrusel) that a product's own main image gets, from a single
     * already-resolved absolute source path on disk. Source-agnostic on
     * purpose: the caller may pass a freshly-uploaded temp file's real path
     * or a realpath()-resolved Media Library file — this method just needs
     * something it can read, so both a fresh upload and a library pick end
     * up with identical output shapes.
     *
     * Each size gets its own fresh ImageManager::read() of the source
     * because Intervention images are mutated in place by resize()/place()
     * — reusing one instance across sizes would compound the resizes
     * instead of producing 5 independent variants.
     *
     * Deliberately NOT ImageUploadTrait::resolveOrCopyImage()/uploadImage():
     * those helpers return a full asset() URL, built for single "main
     * image" fields (Brand logo, Slider image, Product thumb_image). This
     * module's `image`/`image_*` columns use bare relative paths instead
     * (see the frontend's asset($galleryImage->image) usage), so wrapping
     * them here would break that convention for gallery rows.
     *
     * Mirrors the try/catch shape of ImageUploadTrait::resolveOrCopyImage():
     * any Intervention failure (corrupt/unreadable file, etc.) becomes a
     * ValidationException instead of a raw 500.
     *
     * @return array{computers:string, laptop:string, tablet:string, phone:string, carrusel:string}
     */
    private function generateResponsiveVariants(string $sourceFullPath, string $brandSlug, bool $watermark): array
    {
        $sizes = [
            'computers' => [1200, 1200],
            'laptop' => [1000, 1000],
            'tablet' => [800, 800],
            'phone' => [600, 600],
            'carrusel' => [600, 600],
        ];

        $variants = [];

        try {
            foreach ($sizes as $size => [$width, $height]) {
                $manager = new ImageManager(new Driver());
                $img = $manager->read($sourceFullPath);
                $img->resize($width, $height);

                if ($watermark) {
                    $img = $this->applyWatermark($img);
                }
                $img->encode(new WebpEncoder(quality: 75));

                $relativeDir = 'uploads/product/' . $brandSlug . '/webp/' . $size;
                $uploadPath = UploadPath::full($relativeDir . '/');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $imageName = 'media_' . uniqid() . '.webp';
                $img->save($uploadPath . $imageName);

                $variants[$size] = $relativeDir . '/' . $imageName;
            }
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'image' => 'No se pudo procesar la imagen. Verifica que el archivo no esté dañado e inténtalo de nuevo.',
            ]);
        }

        return $variants;
    }

    /** Creates one ProductImageGallery row from a generateResponsiveVariants() result. */
    private function saveGalleryRow(array $variants, $productId): void
    {
        $row = new ProductImageGallery();
        $row->image = $variants['computers'];
        $row->image_laptop = $variants['laptop'];
        $row->image_tablet = $variants['tablet'];
        $row->image_phone = $variants['phone'];
        $row->image_carrusel = $variants['carrusel'];
        $row->product_id = $productId;
        $row->save();
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
        if (!empty($productImage->image_laptop)) {
            $this->deleteImage($productImage->image_laptop);
        }
        if (!empty($productImage->image_tablet)) {
            $this->deleteImage($productImage->image_tablet);
        }
        if (!empty($productImage->image_phone)) {
            $this->deleteImage($productImage->image_phone);
        }
        if (!empty($productImage->image_carrusel)) {
            $this->deleteImage($productImage->image_carrusel);
        }
        $productImage->delete();
        return response(['status' => 'success', 'message' => 'Borrado con exito']);
    }
}
