<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Coupon;
use App\Models\Subcategory;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\CouponTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:ecommerce');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin-ui.coupons.index');
    }

    /** JSON data source for the custom admin table (replaces CouponDataTable). */
    public function tableData(Request $request, CouponTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every coupon matching the current filter/search (replaces the Yajra-Buttons export). */
    public function export(Request $request, CouponTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Cupones',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('cupones.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "cupones.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar (mirrors destroy(), which has no extra side effects). */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $count = Coupon::whereIn('id', $ids)->count();
            Coupon::whereIn('id', $ids)->delete();

            return response()->json(['status' => 'success', 'message' => $count . ' cupón(es) eliminado(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.coupon.create');
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        $categories = Category::active()->orderBy('name')->get();
        // Todas precargadas (no filtradas por categoria) -- el cascadeo
        // categoria->subcategoria->categoria hija se hace en JS del lado del
        // cliente, mismo patron ya usado en admin-ui/products/_form.blade.php.
        $subCategories = Subcategory::orderBy('name')->get();
        $childCategories = ChildCategory::orderBy('name')->get();

        return view('admin-ui.coupons._form', compact('categories', 'subCategories', 'childCategories'));
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $categories = Category::active()->orderBy('name')->get();
        $subCategories = Subcategory::orderBy('name')->get();
        $childCategories = ChildCategory::orderBy('name')->get();

        return view('admin-ui.coupons._form', compact('coupon', 'categories', 'subCategories', 'childCategories'));
    }

    /**
     * Un cupon ACTIVO no puede compartir exactamente la misma combinacion de
     * categoria/subcategoria/categoria hija (los 3 en NULL cuenta como "cupon
     * global", tambien unico) que otro cupon ya activo -- evita ambiguedad de
     * cual gana al momento de que n8n arme la oferta de correo. Cupones
     * inactivos no cuentan para esta regla (se pueden tener varios apagados
     * apuntando al mismo lugar, ej. borradores).
     */
    private function assertNoActiveConflict(?int $categoryId, ?int $subCategoryId, ?int $childCategoryId, ?string $exceptId = null): void
    {
        $conflict = Coupon::where('status', 1)
            ->where('category_id', $categoryId)
            ->where('sub_category_id', $subCategoryId)
            ->where('child_category_id', $childCategoryId)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'category_id' => "Ya existe un cupón activo (\"{$conflict->name}\") para exactamente esta misma combinación de categoría/subcategoría/categoría hija. Desactívalo primero, o cambia el alcance de este.",
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:200'],
            'cod' => ['required', 'max:200'],
            'quantity' => ['required', 'integer'],
            'max_use' => ['required', 'integer'],
            'start_date' => ['required'],
            'end_date' => ['required'],
            'discount_type' => ['required', 'max:200'],
            'discount' => ['required', 'max:200'],
            'status' => ['required', 'integer'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'integer', 'exists:subcategories,id'],
            'child_category_id' => ['nullable', 'integer', 'exists:child_categories,id'],
        ]);

        if ($request->boolean('status')) {
            $this->assertNoActiveConflict($request->category_id ?: null, $request->sub_category_id ?: null, $request->child_category_id ?: null);
        }

        $coupon = new Coupon();
        $coupon->name = $request->name;
        $coupon->cod = $request->cod;
        $coupon->category_id = $request->category_id ?: null;
        $coupon->sub_category_id = $request->sub_category_id ?: null;
        $coupon->child_category_id = $request->child_category_id ?: null;
        $coupon->quantity = $request->quantity;
        $coupon->max_use = $request->max_use;
        $coupon->start_date = $request->start_date;
        $coupon->end_date = $request->end_date;
        $coupon->discount_type = $request->discount_type;
        $coupon->discount = $request->discount;
        $coupon->total_used = 0;
        $coupon->status = $request->status;
        $coupon->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Cupón creado con éxito.']);
        }

        toastr('Cupon Creado', 'success', 'Success');

        return redirect()->route('admin.coupons.index');

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
        $coupon = Coupon::findOrFail($id);
        return view('admin.coupon.edit', compact('coupon'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => ['required', 'max:200'],
            'cod' => ['required', 'max:200'],
            'quantity' => ['required', 'integer'],
            'max_use' => ['required', 'integer'],
            'start_date' => ['required'],
            'end_date' => ['required'],
            'discount_type' => ['required', 'max:200'],
            'discount' => ['required', 'max:200'],
            'status' => ['required', 'integer'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'integer', 'exists:subcategories,id'],
            'child_category_id' => ['nullable', 'integer', 'exists:child_categories,id'],
        ]);

        if ($request->boolean('status')) {
            $this->assertNoActiveConflict($request->category_id ?: null, $request->sub_category_id ?: null, $request->child_category_id ?: null, $id);
        }

        $coupon = Coupon::findOrFail($id);
        $coupon->name = $request->name;
        $coupon->cod = $request->cod;
        $coupon->category_id = $request->category_id ?: null;
        $coupon->sub_category_id = $request->sub_category_id ?: null;
        $coupon->child_category_id = $request->child_category_id ?: null;
        $coupon->quantity = $request->quantity;
        $coupon->max_use = $request->max_use;
        $coupon->start_date = $request->start_date;
        $coupon->end_date = $request->end_date;
        $coupon->discount_type = $request->discount_type;
        $coupon->discount = $request->discount;

        $coupon->status = $request->status;
        $coupon->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Cupón actualizado con éxito.']);
        }

        toastr('Cupon Actualizado', 'success', 'Success');

        return redirect()->route('admin.coupons.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();
        return response(['status' => 'success','message' => 'Borrado con exito']);

    }

    public function changeStatus(Request $request){

        $coupon = Coupon::findOrFail($request->id);
        $activating = $request->status == 'true';

        if ($activating) {
            try {
                $this->assertNoActiveConflict($coupon->category_id, $coupon->sub_category_id, $coupon->child_category_id, $coupon->id);
            } catch (ValidationException $e) {
                return response(['status' => 'error', 'message' => $e->validator->errors()->first()], 422);
            }
        }

        $coupon->status = $activating ? 1 : 0;
        $coupon->save();

        return response(['message' =>'Status Changed Successfully!']);
    }
}
