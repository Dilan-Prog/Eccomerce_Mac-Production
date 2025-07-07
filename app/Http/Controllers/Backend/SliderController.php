<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SliderDataTable;
use App\Http\Controllers\Controller;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use App\Models\Slider;
use Image;
use Illuminate\Support\Facades\Cache;
use PhpParser\Node\Stmt\Return_;

class SliderController extends Controller
{
    use ImageUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(SliderDataTable $dataTable)
    {
        return $dataTable->render('admin.slider.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.slider.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'banner'=>['required','image','mimes:jpeg,png,jpg,gif,svg,webp','max:2000'],
            'type' =>['nullable','string','max:200'],
            'title' => ['max:200'],
            'starting_price'=>['max:200'],
            'btn_url' => ['url'],

            'serial' => ['required', 'integer'],
            'status'=>['required']
        ]);

        $slider = new Slider();

        /**header file Upload */
        $imagePathComputers = $this->uploadImage($request,'banner','uploads/slider/webp/computers',1320,440);
        $imagePathLaptop = $this->uploadImage($request,'banner','uploads/slider/webp/laptop',1140,380);
        $imagePathTablet = $this->uploadImage($request,'banner','uploads/slider/webp/tablet',720,240);
        $imagePathPhone = $this->uploadImage($request,'banner','uploads/slider/webp/phone',370,125);
        
        $slider->banner=$imagePathComputers;
        $slider->banner_laptop=$imagePathLaptop;
        $slider->banner_tablet=$imagePathTablet;
        $slider->banner_phone=$imagePathPhone;

        $slider->type = $request->type;
        $slider->title = $request->title;
        $slider->starting_price = $request->starting_price;
        $slider->btn_url = $request->btn_url;

        $slider->serial = $request->serial;
        $slider->status = $request->status;
        $slider->save();

        Cache::forget('sliders');

        toastr('Creado con exito','success');
        return redirect()->back();



    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $slider = Slider::findOrFail($id);
        return view('admin.slider.edit', compact('slider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'banner'=>['required','image','mimes:jpeg,png,jpg,gif,svg,webp','max:2000'],
            'type' =>['nullable','string','max:200'],
            'title' => ['max:200'],
            'starting_price'=>['max:200'],
            'btn_url' => ['url'],
            'serial' => ['required', 'integer'],
            'status'=>['required']
        ]);

        $slider = Slider::findOrFail($id);

        /**header file Upload */
        $imagePathComputers = $this->uploadImage($request,'banner','uploads/slider/webp/computers',1320,440);
        $imagePathLaptop = $this->uploadImage($request,'banner','uploads/slider/webp/laptop',1140,380);
        $imagePathTablet = $this->uploadImage($request,'banner','uploads/slider/webp/tablet',720,240);
        $imagePathPhone = $this->uploadImage($request,'banner','uploads/slider/webp/phone',370,125);

        $slider->banner= empty(!$imagePathComputers) ? $imagePathComputers : $slider->banner;
        $slider->banner_laptop= empty(!$imagePathLaptop) ? $imagePathLaptop : $slider->banner_laptop;
        $slider->banner_tablet= empty(!$imagePathTablet) ? $imagePathTablet : $slider->banner_tablet;   
        $slider->banner_phone= empty(!$imagePathPhone) ? $imagePathPhone : $slider->banner_phone;

        $slider->type = $request->type;
        $slider->title = $request->title;
        $slider->starting_price = $request->starting_price;
        $slider->btn_url = $request->btn_url;

        $slider->serial = $request->serial;
        $slider->status = $request->status;
        $slider->save();

        Cache::forget('sliders');

        toastr('Actualizacion con exito','success');
        return redirect()->route('admin.slider.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $slider = Slider::findOrFail($id);
             $this->deleteImage($slider->banner);
             $this->deleteImage($slider->banner_laptop);
             $this->deleteImage($slider->banner_tablet);
             $this->deleteImage($slider->banner_phone);
             $slider->delete();

             return response(['status' => 'success', 'message' => 'Borrado Correctamente']);


    }
}
