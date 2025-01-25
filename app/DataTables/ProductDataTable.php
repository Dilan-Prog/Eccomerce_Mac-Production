<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;


class ProductDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    // //   <a class="dropdown-item has-icon" href="'.route('admin.products-links.index', ['productId' => $query->id]).'"><i class="fas fa-link"></i> Manage Links</a>
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function($query){
                $editBtn = "<a href='".route('admin.products.edit',$query->id)."' class='btn btn-primary'>Edit</a>";
                $deleteBtn = "<a href='".route('admin.products.destroy',$query->id)."' class='btn btn-danger m-2 delete-item'>Delete</a>";

                $moreBtn = '<div class="dropdown dropleft d-inline">
                <button class="btn btn-primary dropdown-toggle ml-1" type="button" id="dropdownMenuButton2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-cog"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item has-icon" href="'.route('admin.products-image-gallery.index', ['product' => $query->id]).'"><i class="far fa-images"></i> Image Gallery </a>
                  <a class="dropdown-item has-icon" href="'.route('admin.products-variant.index', ['product' => $query->id]).'"><i class="fas fa-boxes"></i> Product Variant</a>
                

                </div>
              </div>';

                return $editBtn.$deleteBtn.$moreBtn;
            })

            ->addColumn('image', function($query){
                return $img = "<img width='70px' src='".asset($query->thumb_image)."' ></img>";
            })

            ->addColumn('type', function($query){
                switch($query->product_type){
                    case 'new_arrival':
                        return '<i class="badge badge-success">Nuevo</i>';
                    break;
                    case 'featured_product':
                        return '<i class="badge badge-warning">Producto Favorito</i>';
                    break;
                    case 'top_product':
                        return '<i class="badge badge-info">Producto Top</i>';
                    break;
                    case 'best_product':
                        return '<i class="badge badge-danger">Mas Vendido</i>';
                    break;
                    default:
                    return '<i class="badge badge-dangerdark">Ninguno</i>';
                        break;
                }
            })
            ->addColumn('status', function($query){
                if($query->status == 1){

                                    $button = '<label class="custom-switch mt-2">
                                    <input type="checkbox"checked name="custom-switch-checkbox" data-id="'.$query->id.'" class="custom-switch-input change-status">
                                    <span class="custom-switch-indicator"></span>
                                    </label>';

                }else if($query->status == 0){
                    $button = '<label class="custom-switch mt-2">
                                    <input type="checkbox" name="custom-switch-checkbox" data-id="'.$query->id.'" class="custom-switch-input change-status">
                                    <span class="custom-switch-indicator"></span>
                                    </label>';
                }
                return $button;
            })
            ->addColumn('category', function($query) {
                return $query->category ? $query->category->name : 'N/A';  // Retrieve category name
            })
            ->addColumn('brand', function($query) {
                return $query->brand ? $query->brand->name : 'N/A';  // Retrieve brand name
            })
            ->addColumn('qty', function($query) {
                return $query->qty;
            })
            ->addColumn('short_description', function($query) {
                return $query->short_description;
            })
            ->addColumn('long_description', function($query) {
                return $query->long_description;
            })
            ->addColumn('video_link', function($query) {
                return $query->video_link;
            })
            ->addColumn('url_PDF', function($query) {
                return $query->url_PDF;
            })
            ->addColumn('canonical_url', function($query) {
                return $query->canonical_url;
            })
            ->addColumn('is_canonical', function($query) {
                return $query->is_canonical ? 'Yes' : 'No';
            })
            ->rawColumns(['image','type', 'action' , 'status'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('product-table')
                    ->minifiedAjax()
                    ->lengthMenu([
                        [10, 25, 50, -1],
                        ['10', '25', '50', 'All']   // Configuración de las opciones de cantidad de registros
                    ])->language('spanish')
                    ->lengthChange(true) 
                    ->orderBy(0)
                    ->columns($this->getColumns())
                    ->parameters([
                        'dom'     => 'lBfrtip',
                        'buttons' => [
                            [
                                'extend'   => 'excel',
                                'text'     => 'Exportar a Excel',
                                // 'title'    => 'Productos Exportados',  // Título personalizado del archivo Excel
                                // 'filename' => 'productos_exportados_mac-del-norte',  // Nombre del archivo Excel
                                
                            ],
                            'csv',
                            'pdf',
                            'print',
                            'reset',
                            'reload',
                        ],
                        
                    'language' => [
                        'sLengthMenu' => 'Mostrar _MENU_ registros por página',
                        'sZeroRecords' => 'No se encontraron resultados',
                        'sInfo' => 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        'sInfoEmpty' => 'Mostrando 0 a 0 de 0 registros',
                        'sInfoFiltered' => '(filtrado de _MAX_ registros en total)',
                        'sSearch' => 'Buscar:',
                        'oPaginate' => [
                            'sFirst' => 'Primera',
                            'sPrevious' => 'Anterior',
                            'sNext' => 'Siguiente',
                            'sLast' => 'Última'
                        ]
                    ]
                ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            'id',
            'name',
            'sku',
            'productModel',
            'price',
            'image', // Aquí agregamos la columna de imagen
            'type',  // Aquí agregamos la columna 'type'
            'status', // Aquí agregamos la columna 'status'

            // Columnas adicionales que no se mostrarán, pero se exportarán
        Column::make('category')   // Agregamos la columna 'category'
        ->exportable(true)  // Hacemos que sea exportable
        ->visible(false),   // No la mostramos en el DataTable

        Column::make('brand')      // Agregamos la columna 'brand'
                ->exportable(true)  // Hacemos que sea exportable
                ->visible(false),   // No la mostramos en el DataTable

        Column::make('qty')        // Agregamos la columna 'qty'
                ->exportable(true)  // Hacemos que sea exportable
                ->visible(false),   // No la mostramos en el DataTable

        Column::make('short_description') // Agregamos la columna 'short_description'
                ->exportable(true)  // Hacemos que sea exportable
                ->visible(false),   // No la mostramos en el DataTable

        Column::make('long_description')  // Agregamos la columna 'long_description'
                ->exportable(true)  // Hacemos que sea exportable
                ->visible(false),   // No la mostramos en el DataTable

        Column::make('video_link') // Agregamos la columna 'video_link'
                ->exportable(true)  // Hacemos que sea exportable
                ->visible(false),   // No la mostramos en el DataTable

        Column::make('url_PDF')    // Agregamos la columna 'url_PDF'
                ->exportable(true)  // Hacemos que sea exportable
                ->visible(false),   // No la mostramos en el DataTable

        Column::make('canonical_url') // Agregamos la columna 'canonical_url'
                ->exportable(true)  // Hacemos que sea exportable
                ->visible(false),   // No la mostramos en el DataTable

        Column::make('is_canonical')  // Agregamos la columna 'is_canonical'
                ->exportable(true)  // Hacemos que sea exportable
                ->visible(false),   // No la mostramos en el DataTable

            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(300)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Product_' . date('YmdHis');
    }
}
