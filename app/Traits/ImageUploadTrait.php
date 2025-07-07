<?php
namespace App\Traits;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\EncodedImage;
use Illuminate\Support\Facades\File;
// use File;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;

trait ImageUploadTrait{


    public function uploadImageAsPng(Request $request, $inputName, $path, $width = null, $height = null)
    {
        if ($request->hasFile($inputName)) {
            $image = $request->{$inputName};
            $manager = new ImageManager(new Driver());

            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageName = $originalName . '_media_' . uniqid() . '.png';

            $img = $manager->read($image->getRealPath());
            if ($width && $height) {
                $img->resize($width, $height);
            }

            $uploadPath = public_path($path . '/');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $img->toPng()->save($uploadPath . $imageName);

            // Devuelve solo el nombre del archivo o la ruta relativa
            return $path . '/' . $imageName;
        }
        return null;
    }
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

    /**handle delete file antiguo no sirve para rutas url hacer el cambio a rutas relativas*/


    // public function deleteImage(string $path){

    //     if(File::exists(storage_path($path))){
    //         File::delete(storage_path($path));
    //     }
    // }

    /**handle delete file Nuevo ahi que poner o cambiar a rutas relativas*/
    public function deleteImage(string $fullUrl)
    {
    // Obtiene el host base (dominio + esquema)
    $baseUrl = config('app.url'); // ej: http://eccomerce_mac-production.test

    // Elimina la baseUrl de la URL completa para obtener ruta relativa
    $relativePath = str_replace($baseUrl, '', $fullUrl);

    // Limpia posibles barras iniciales
    $relativePath = ltrim($relativePath, '/');

    $fullPath = public_path($relativePath);

    if (File::exists($fullPath)) {
        File::delete($fullPath);
        return true;
    }

    return false;

    }





}
