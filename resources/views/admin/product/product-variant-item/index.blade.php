@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Variantes Items de Productos</h1>
      </div>
      <div class="mb-3">
        <a href="{{route('admin.products-variant.index',['product' => $product->id])}}" class="btn btn-primary">Regresar</a>
      </div>

      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Variantes: {{$variant->name}} </h4>
                <div class="card-header-action">
                    <a href="{{route('admin.products-variant-item.create', ['productId' => $product->id, 'variantId'=> $variant->id ])}}" class="btn btn-primary">+ Crear Nuevo</a>
                </div>
              </div>
              <div class="card-body">
                {{$dataTable->table()}}
              </div>
              
            </div>
          </div>
          
        </div>
        
      </div>
    </section>




@endsection

@push('scripts')
  {{$dataTable->scripts(attributes:['type' => 'module'])}}
  
  <script>
    $(document).ready(function(){
      $('body').on('click', '.change-status',function(){
        let isChecked = $(this).is(':checked');
        console.log(isChecked);
        let id = $(this).data('id');
        $.ajax({
          url:"{{route('admin.products-variant-item.change-status')}}",
          method:'PUT',
          data:{
            status:isChecked,
            id:id
          },
          success:function(data){
              toastr.success(data.message);
          },
          error:function(xhr,status,error){
            
               }

        })

            })
    })
  </script>
  
@endpush


