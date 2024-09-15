<?php
namespace App\Traits;
use Illuminate\Http\Request;
use File;

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
    /*One image*/
    public function uploadImage(Request $request,$inputName,$path){

        if($request->hasFile($inputName)){
            
            
            $image = $request->{$inputName};
            $ext = $image->getClientOriginalName();
            $imageName = 'media_'.uniqid().'.'.$ext;

            $image->move(public_path($path),$imageName);

             
             $imagePath = asset($path . '/' . $imageName);

             
             return $imagePath;
        }


    }

//     public function uploadImage(Request $request, $inputName, $path)
// {
//     if ($request->hasFile($inputName)) {
//         // Obtener la imagen
//         $image = $request->file($inputName);
//         // Obtener la extensión del archivo
//         $ext = $image->getClientOriginalExtension();
//         // Generar un nombre único para la imagen
//         $imageName = 'media_'.uniqid().'.'.$ext;

//         // Mover la imagen a la carpeta especificada
//         $image->move(public_path($path), $imageName);
        
//         $imagePath = asset($path . '/' . $imageName);
//         // Retornar la ruta de la imagen guardada
//         return $imagePath;
//     }
//     return null;
// }


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