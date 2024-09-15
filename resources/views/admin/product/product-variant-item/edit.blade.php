@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Nuevo Item</h1>
        <div class="section-header-breadcrumb">
          
        </div>
      </div>


      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Update Item Variant</h4>
                
              </div>
              <div class="card-body">
                
                <form action="{{route('admin.products-variant-item.update', $variantItem->id)}}" method="POST">
                  @csrf
                  @method('PUT')
                    <div class="form-group">
                        <label>Nombre de la variante</label>
                        <input type="text" class="form-control" name="variant_name" value="{{$variantItem->productVariant->name}}" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Item Name</label>
                        <input type="text" class="form-control" name="name" value="{{$variantItem->name}}">
                    </div>
                    <div class="form-group">
                        <label>Price <code>(Set 0 for make it free)</code></label>
                        <input type="text" class="form-control" name="price" value="{{$variantItem->price}}">
                    </div>
                    <div class="form-group">
                        <label for="inputState">Is Default</label>
                        <select id="inputState" class="form-control" name="is_default">
                            <option selected="">Select</option>
                            <option {{$variantItem->is_default == 1 ? 'selected' : ''}} value="1">Si</option>
                            <option {{$variantItem->is_default == 0 ? 'selected' : ''}} value="0">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="inputState">Status</label>
                        <select id="inputState" class="form-control" name="status">
                            <option selected="">Choose...</option>
                            <option {{$variantItem->status == 1 ? 'selected' : ''}} value="1">Active</option>
                            <option {{$variantItem->status == 0 ? 'selected' : ''}} value="0">Inactive</option>
                        </select>
                    </div>
                      <button type="submit" class="btn btn-primary">Update</button>
                </form>
              </div>
              
            </div>
          </div>
          
        </div>
        
      </div>
    </section>




@endsection