@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Productos M&aacute;s Vendidos
@endsection

@section('content')




    <!--============================
        DAILY DEALS DETAILS START
    ==============================-->
    <section id="wsus__daily_deals">
        <div class="container">
            <div class="wsus__offer_details_area">


                <div class="row">
                    <div class="col-xl-12">
                        <div class="wsus__section_header rounded-0">
                            <h3>Lo mas vendido</h3>
                            <div class="wsus__offer_countdown">
                                <span class="end_text">ends time :</span>
                                <div class="simply-countdown simply-countdown-one"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @foreach ($flashSaleItems as $item )
                    @php
                        $product = \App\Models\Product::find($item->product_id);
                    @endphp
                    <div class="col-xl-3 col-sm-6 col-lg-4">
                        <div class="wsus__product_item">

                            @if ($product)

                            @endif
                            <span class="wsus__new" style="background: linear-gradient(to right, #FF5733, #FFA500, #FFDA1F); width: auto; font-weight: 700">Oferta </span>



                            @if (checkDiscount($product))

                            <span class="wsus__minus">-{{calculatedDiscountPercent($product->price, $product->offert_price)}}%</span>

                            @endif
                            <a class="wsus__pro_link" href="{{route('product-detail', $product->slug)}}">
                                <img src="{{asset($product->thumb_image)}}" alt="product" class="img-fluid w-100 img_1" />
                                <img src="
                                @if (isset($product->productImageGalleries[0]->image))
                                    {{asset($product->productImageGalleries[0]->image)}}
                                @else
                                    {{asset($product->thumb_image)}}
                                @endif
                                " class="img-fluid w-100 img_2" />
                            </a>

                            <div class="wsus__product_details">
                                <a class="wsus__category" href="{{route('product-detail', $product->slug)}}">{{$product->category->name}}</a>
                                <p class="wsus__pro_rating">


                                    @if ($product->qty > 0)
                                    <p class="wsus__stock_area"><span class="in_stock">Disponibles</span></p>
                                    @elseif ($product->qty === 0)
                                        <p class="wsus__stock_area"><span class="in_stock">Agotado</span></p>
                                    @endif

                                </p>
                                <a class="wsus__pro_name" href="{{route('product-detail', $product->slug)}}">{{$product->name}}</a>
                                @if (checkDiscount($product))
                                    <p itemscope itemtype="http://schema.org/Offer">
                                        <meta itemprop="priceCurrency" content="MXN">
                                        <span class="wsus__price" itemprop="price" content="{{$product->offert_price}}">
                                        {{$settings->currency_icon}}{{ number_format($product->offert_price, 2, '.', ',') }} <del>{{$settings->currency_icon}}{{ number_format($product->price, 2, '.', ',') }} </del></span></p>
                                @else
                                    <p itemscope itemtype="http://schema.org/Offer">
                                        <meta itemprop="priceCurrency" content="MXN">
                                        <span class="wsus__price" itemprop="price" content="{{$product->price}}">
                                        {{$settings->currency_icon}}{{ number_format($product->price, 2, '.', ',') }} </span></p>
                                @endif
                                <form class="shopping-cart-form">
                                    <input type="hidden" name="product_id" value="{{$product->id}}">
                                    <input type="hidden" name="brand_name" value="{{ $product->brand->name }}">
                                    <button type="submit" class="add_cart" href="#">Agregar al Carrito</button>

                                    <input name="qty" type="hidden" min="1" max="100" value="1" />
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="d-flex mt-5 justify-content-center align-items-center">
                    @if ($flashSaleItems->hasPages())
                    {{$flashSaleItems->links()}}
                    @endif
                </div>
            </div>
        </div>
    </section>
    <!--============================
        DAILY DEALS DETAILS END
    ==============================-->




@endsection

@push('scripts')

    <script>

        $(document).ready(function(){


                simplyCountdown('.simply-countdown-one', {
                    year: {{date('Y', strtotime($flashSaleDate->end_date))}},
                    month:{{date('m', strtotime($flashSaleDate->end_date))}},
                    day: {{date('d', strtotime($flashSaleDate->end_date))}},

            });
        })
    </script>

    @endpush
