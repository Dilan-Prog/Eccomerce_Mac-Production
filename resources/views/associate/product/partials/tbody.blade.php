
@foreach ($productAssociate as $product)
  @php
    $precio = $product->price_personalizated == 1
      ? $product->price
      : ($aspelPrecio[$product->sku] ?? $product->price);

    $mon = (isset($aspelMoneda[$product->sku]) && (int)$aspelMoneda[$product->sku] === 2) ? 'USD' : 'MXN';
  @endphp

  <tr class="dataTable-row">
    <td class="dataTable-content-image">
      <div class="dataTable-image">
        <img src="{{ asset($product->thumb_image) }}" alt="">
      </div>
    </td>
    <td class="dataTable-title">{{ $product->name }}</td>
    <td class="dataTable-sku">{{ $product->sku }}</td>
    <td class="dataTable-model">{{ $product->productModel }}</td>
    <td class="dataTable-brand">{{ $product->brand->name }}</td>
    <td class="dataTable-price">{{ number_format($precio, 2, ',', '.') }} {{ $mon }}</td>
    <td class="dataTable-qty">{{ $product->qty_personalizated == 1 ? $product->qty : $product->qty_aspel }}</td>
    <td class="dataTable-add-cart" aria-disabled="true">Disabled</td>
    <td>
      <button type="button" class="dataTable-btn" data-product-id="{{ $product->id }}">Ver Producto</button>
    </td>
  </tr>
@endforeach
