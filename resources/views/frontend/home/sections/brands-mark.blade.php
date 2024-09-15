<section id="brands-mark" class="wsus__flash_sell_2">
    <div class="container">
        <div class="row" id="brand_mark_slider">
            @foreach ($brands as $item)
               
                <div class="col-xl-3 col-sm-6 col-lg-4">
                    <a href="#">
                        <div class="brands-logo">
                            <img src="{{ $item->logo }}" alt="Brand" loading="lazy">
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
</section>
