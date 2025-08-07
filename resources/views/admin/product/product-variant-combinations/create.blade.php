@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Nuevo Combinacion</h1>
        <div class="section-header-breadcrumb">

        </div>
      </div>


      <div class="section-body">


        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Crear Nueva Combinacion</h4>

              </div>
              <div class="card-body">

                <form action="{{route('admin.products-variant-combinations.store')}}" method="POST">
                  @csrf
                    <div class="form-group">
                        <label>Nombre de la Combinacion</label>
                        <input type="text" class="form-control" name="name" value="">
                    </div>
                    <div class="form-group">
                        <label>Seleccionar Items de Variante</label>
                        <div class="row">
                            @foreach ($variantItems as $item)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            name="variants_item_ids[]"
                                            value="{{ $item->id }}"
                                            id="item{{ $item->id }}">
                                        <label class="form-check-label" for="item{{ $item->id }}">
                                            {{ $item->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- <div class="form-group">
                        <input type="hidden" name="variant_item_ids" id="variant_item_ids" value="" readonly>
                    </div> --}}
                    <div class="form-group">
                        <input type="hidden" class="form-control" name="product_id" value="{{ $product->id }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Sku <code>(Clave del Producto)</code></label>
                        <input type="text" class="form-control" name="sku" value="">
                    </div>
                    <div class="form-group">
                        <label>Precio <code>(Establecer 0 para hacerlo gratis)</code></label>
                        <input type="text" class="form-control" name="price" value="">
                    </div>
                    <div class="form-group">
                        <label> Cantidad <code> (Stock de la Variante) </code></label>
                        <input type="number" class="form-control" name="qty">
                    </div>
                    <div class="form-group">
                        <label for="inputState">Seleccionar por Defecto</label>
                        <select id="inputState" class="form-control" name="is_default">
                            <option selected="">Seleccionar</option>
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="inputState">Estado</label>
                        <select id="inputState" class="form-control" name="status">
                            <option selected="">Seleccionar...</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                      <button type="submit" class="btn btn-primary">Crear</button>
                </form>
              </div>

            </div>
          </div>

        </div>

      </div>
    </section>




@endsection
@push('scripts')
{{-- <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const checkedItems = Array.from(document.querySelectorAll('.variant-item-checkbox:checked'))
            .map(cb => cb.value);

        // En vez de JSON, únelos con coma (name[] en el input sería mejor)
        document.getElementById('variant_item_ids').value = checkedItems;
    });
</script> --}}

@endpush

