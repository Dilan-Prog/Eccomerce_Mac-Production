

    <section id="wsus__banner">
        <div class="container ">
            <div class="row ">
                <div class="col-xl-12">
                    <div class="wsus__banner_content">
                        <div class="row banner_slider ">
                            @foreach ($sliders as $slider)

                            <div class="col-xl-12">
                                <a href="{{$slider->btn_url}}">
                                    <img src="{{asset($slider->banner)}}" alt="slider image" loading="lazy" class="wsus__single_slider">
                                    {{-- <div class="wsus__single_slider "  style=" background: url({{$slider->banner}})" >
                                    </div> --}}
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

