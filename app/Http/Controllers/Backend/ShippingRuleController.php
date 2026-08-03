<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ShippingRule;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\ShippingRuleTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ShippingRuleController extends Controller
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
        return view('admin-ui.shipping-rule.index');
    }

    /** JSON data source for the custom admin table (replaces ShippingRuleDataTable). */
    public function tableData(Request $request, ShippingRuleTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every shipping rule matching the current filter/search. */
    public function export(Request $request, ShippingRuleTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Reglas de Envio',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('reglas-de-envio.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "reglas-de-envio.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $rules = ShippingRule::whereIn('id', $ids)->get();
            foreach ($rules as $rule) {
                $rule->delete();
            }
            return response()->json(['status' => 'success', 'message' => count($rules) . ' regla(s) eliminada(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.shipping-rule.create');
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.shipping-rule._form');
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $shippingRule = ShippingRule::findOrFail($id);
        return view('admin-ui.shipping-rule._form', compact('shippingRule'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:200'],
            'type' => ['required'],
            'min_cost' => ['nullable', 'integer'],
            'cost' => ['required', 'integer'],
            'status' => ['required'],


        ]);

        $shipping = new ShippingRule();
        $shipping->name = $request->name;
        $shipping->type = $request->type;
        $shipping->min_cost = $request->min_cost;
        $shipping->cost = $request->cost;
        $shipping->status = $request->status;
        $shipping->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Regla de envío creada con éxito.']);
        }

        toastr('Creado Con Extio', 'success', 'Success');
        return redirect()->route('admin.shipping-rule.index');
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
        $shipping = ShippingRule::findOrFail($id);
        return view('admin.shipping-rule.edit', compact('shipping'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => ['required', 'max:200'],
            'type' => ['required'],
            'min_cost' => ['nullable', 'integer'],
            'cost' => ['required', 'integer'],
            'status' => ['required'],
            

        ]);

        $shipping = ShippingRule::findOrFail($id);
        $shipping->name = $request->name;
        $shipping->type = $request->type;
        $shipping->min_cost = $request->min_cost;
        $shipping->cost = $request->cost;
        $shipping->status = $request->status;
        $shipping->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Regla de envío actualizada con éxito.']);
        }

        toastr('Actualizado Con Extio', 'success', 'Success');
        return redirect()->route('admin.shipping-rule.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $shipping = ShippingRule::findOrFail($id);
        $shipping->delete();
        return response(['status' => 'success','message' => 'Borrado con exito']);

    } 

    public function changeStatus(Request $request){
        
        $shipping = ShippingRule::findOrFail($request->id);
        $shipping->status = $request->status == 'true' ? 1 : 0;
        $shipping->save();

        return response(['message' =>'Status Changed Successfully!']);
    }
}
