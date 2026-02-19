@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>New Category</h1>
        <div class="section-header-breadcrumb">

        </div>
      </div>


      <div class="section-body">


        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Edit Category</h4>

              </div>
              <div class="card-body">

                <form action="{{route('admin.category.update', $category->id)}}" method="POST">
                  @csrf
                  @method('PUT')
                    <div class="form-group">
                        <label>Icon</label>
                            <div>
                                <button class="btn btn-primary" data-selected-class="btn-danger"
                                data-unselected-class="btn-info" role="iconpicker" name="icon" data-icon="{{$category->icon}}"></button>
                            </div>
                    </div>
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="name" value="{{$category->name}}">
                    </div>
                    <div class="form-group">
                        <label for="inputState">Status</label>
                        <select id="inputState" class="form-control" name="status">
                          <option value="1" {{$category->status == 1 ? 'selected':''}}>Active</option>
                          <option value="0" {{$category->status == 0 ? 'selected':''}}>Inactive</option>
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
