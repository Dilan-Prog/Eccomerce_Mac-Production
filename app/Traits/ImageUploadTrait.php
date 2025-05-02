<?php
namespace App\Traits;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\EncodedImage;
use File;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;

trait ImageUploadTrait{

    /**Multi - image */
    public function uploadMultiImage(Request $request,$inputName,$path){

        $imagePaths = [];

        if($request->hasFile($inputName)){


            $images = $request->{$inputName};
            foreach($images as $image){

                $ext = $image->getClientOriginalName();
                $imageName = 'media_'.uniqid().'.'.$ext;

                $image->move(public_path($path),$imageName);



                $imagePaths[] = $path.'/'.$imageName;
            }
            return $imagePaths;

        }


    }
    public function uploadMultiImageReview(Request $request, $inputName, $path)
    {
        $imagePaths = [];

        if($request->hasFile($inputName)){

            $images = $request->{$inputName};

            foreach($images as $image){

                $ext = $image->getClientOriginalExtension();
                $imageName = 'media_'.uniqid().'.'.$ext;

                $image->move(public_path($path), $imageName);

                $imagePaths[] =  $path.'/'.$imageName;
            }

            return $imagePaths;
       }
    }

    /*One image*/
    public function uploadImage(Request $request,$inputName,$path,$width,$height){

         if ($request->hasFile($inputName)) {
        $image = $request->{$inputName};
        $manager = new ImageManager(new Driver());

        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $imageName = $originalName . 'media_' . uniqid() . '.webp';

        // Procesar la imagen con ImageManager
        $img = $manager->read($image->getRealPath());
        $img->resize($width, $height);

        $img->encode(new WebpEncoder(quality:75));

        $uploadPath = public_path($path . '/');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $img->save($uploadPath . $imageName);


        $imagePath = asset($path . '/' . $imageName);
        return $imagePath;
    }


    }

    public function updateImage(Request $request,$inputName,$path, $oldaPath=null){

        if($request->hasFile($inputName)){
            if(File::exists(public_path($oldaPath))){
                File::delete(public_path($oldaPath));
            }

            $image = $request->{$inputName};
            $ext = $image->getClientOriginalName();
            $imageName = 'media_'.uniqid().'.'.$ext;

            $image->move(public_path($path),$imageName);


             $imagePath = asset($path . '/' . $imageName);


             return $imagePath;
        }


    }

    /**handle delete file */
    public function deleteImage(string $path){

        if(File::exists(public_path($path))){
            File::delete(public_path($path));
        }



    }



}
