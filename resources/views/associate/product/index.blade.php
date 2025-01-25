@extends('associate.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Productos</h1>
        <img src="{{asset('frontend/images/logo/logo-negro-azul.png')}}" style="width: 150px; margin-left: 1980px" alt="logo" id="logo-horizontal">
      </div>


      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Todos Los Producto</h4>
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
          url:"{{route('admin.product.change-status')}}",
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