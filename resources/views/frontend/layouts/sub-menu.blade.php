
    @php
        $categories = \App\Models\Category::where('status', 1)
        ->with(['subCategories' => function($query){
            $query->where('status', 1)
            ->with(['childCategories' => function($query){
                $query->where('status', 1);
            }]);
        }])
        ->get();

    @endphp
    <nav class="wsus__main_menu d-none d-lg-block">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    
                </div>
            </div>
        </div>
    </nav>
    <!--============================
        MAIN MENU END
    ==============================-->






       

