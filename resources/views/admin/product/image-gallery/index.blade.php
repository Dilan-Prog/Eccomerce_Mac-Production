@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Galeria de Imagenes de Productos</h1>
        
      </div>
      <div class="mb-3">
        <a href="{{route('admin.products.index')}}" class="btn btn-primary">Regresar</a>

      </div>

      <div class="section-body">
        
        <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>Producto : {{$product->name}}</h4>
                  
                </div>
                <div class="card-body">
                    <form action="{{route('admin.products-image-gallery.store')}}" method="POST" enctype="multipart/form-data">
                      @csrf
                        <div class="form-group">
                            <label for="">Imagen<code> (Soporta Multiple Imagenes) </code> </label>
                            <input type="file" class="form-control" name="image[]" multiple>
                            <input type="hidden" name="product" value="{{$product->id}}">
                        </div>
                        <button type="submit" class="btn btn-primary">Subir</button>
                    </form>
                </div>
                
              </div>
            </div>
            
          </div>

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Todas las Imagenes</h4>
                
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
  
@endpush