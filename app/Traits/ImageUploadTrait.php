<?php
namespace App\Traits;
use App\Support\UploadPath;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\EncodedImage;
use Intervention\Image\Interfaces\ImageInterface;
use Illuminate\Support\Facades\File;

// use File;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
trait ImageUploadTrait{

    /**
     * Superpone el logo configurado como marca de agua, repetido en cuadrícula y
     * rotado en diagonal, sobre la imagen dada (patrón tipo "banco de imágenes").
     * No hace nada si config('watermark.enabled') es false o el archivo no existe.
     */
    protected function applyWatermark(ImageInterface $img): ImageInterface
    {
        if (!config('watermark.enabled')) {
            return $img;
        }

        $watermarkPath = UploadPath::full(config('watermark.image'));
        if (!file_exists($watermarkPath)) {
            return $img;
        }

        $manager = new ImageManager(new Driver());
        $watermark = $manager->read($watermarkPath);

        // Tamaño de cada repetición en función del lado más corto, para que la
        // densidad del patrón se vea igual sin importar la proporción de la imagen.
        $shortSide = min($img->width(), $img->height());
        $tileWidth = (int) round($shortSide * (config('watermark.tile_scale_percent', 22) / 100));
        if ($tileWidth < 1) {
            return $img;
        }
        $watermark->scale(width: $tileWidth);
        $watermark->rotate((float) config('watermark.rotation', -30), 'transparent');

        $spacing = $watermark->width() * (float) config('watermark.spacing_factor', 1.4);
        $cols = max(2, (int) ceil($img->width() / $spacing) + 1);
        $rows = max(2, (int) ceil($img->height() / $spacing) + 1);

        // Los offsets se mantienen siempre dentro del lienzo (0..usable) para que GD
        // no tenga que copiar regiones fuera de los límites de la imagen base.
        $usableWidth = max(0, $img->width() - $watermark->width());
        $usableHeight = max(0, $img->height() - $watermark->height());
        $stepX = $cols > 1 ? $usableWidth / ($cols - 1) : 0;
        $stepY = $rows > 1 ? $usableHeight / ($rows - 1) : 0;
        $opacity = (int) config('watermark.opacity', 30);

        for ($row = 0; $row < $rows; $row++) {
            $stagger = ($row % 2 === 0) ? 0 : min($stepX / 2, $usableWidth);
            for ($col = 0; $col < $cols; $col++) {
                $offsetX = (int) round(min($col * $stepX + $stagger, $usableWidth));
                $offsetY = (int) round(min($row * $stepY, $usableHeight));

                $img->place($watermark, 'top-left', $offsetX, $offsetY, $opacity);
            }
        }

        return $img;
    }

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

            $uploadPath = UploadPath::full($path . '/');
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
    public function uploadMultiImage(Request $request,$inputName,$path,$watermark = false){

        $imagePaths = [];

        if($request->hasFile($inputName)){


            $images = $request->{$inputName};
            foreach($images as $image){

                if ($watermark) {
                    $manager = new ImageManager(new Driver());
                    $imageName = 'media_' . uniqid() . '.webp';

                    $img = $manager->read($image->getRealPath());
                    $img = $this->applyWatermark($img);
                    $img->encode(new WebpEncoder(quality: 75));

                    $uploadPath = UploadPath::full($path . '/');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }
                    $img->save($uploadPath . $imageName);
                } else {
                    $ext = $image->getClientOriginalName();
                    $imageName = 'media_'.uniqid().'.'.$ext;

                    $image->move(UploadPath::full($path),$imageName);
                }

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

                $image->move(UploadPath::full($path), $imageName);

                $imagePaths[] =  $path.'/'.$imageName;
            }

            return $imagePaths;
       }
    }

    /*One image*/
    public function uploadImage(Request $request,$inputName,$path,$width = null,$height = null,$watermark = false){

        if ($request->hasFile($inputName)) {
            $image = $request->{$inputName};

            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanedName = Str::slug($originalName, '-');
            $imageName = $cleanedName  . '-media_' . uniqid() . '.webp';

            $uploadPath = UploadPath::full($path . '/');

            try {
                $manager = new ImageManager(new Driver());

                // Procesar la imagen con ImageManager
                $img = $manager->read($image->getRealPath());
                if ($width && $height) {
                    $img->resize($width, $height);
                }

                if ($watermark) {
                    $img = $this->applyWatermark($img);
                }

                $img->encode(new WebpEncoder(quality:75));

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $img->save($uploadPath . $imageName);
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    $inputName => 'No se pudo procesar la imagen. Verifica que el archivo no esté dañado e inténtalo de nuevo.',
                ]);
            }

            $imagePath = asset($path . '/' . $imageName);
            return $imagePath;
    }


    }

    public function updateImage(Request $request, $inputName, $path, $oldaPath = null, $width = null, $height = null, $watermark = false)
{
    if ($request->hasFile($inputName)) {

        // Procesar imagen nueva
        $image = $request->{$inputName};

        // Limpiar nombre y crear nombre único
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanedName = Str::slug($originalName, '-');
        $imageName = $cleanedName . '-media_' . uniqid() . '.webp';

        $uploadPath = UploadPath::full($path . '/');

        try {
            $manager = new ImageManager(new Driver());

            // Leer, redimensionar y codificar la imagen
            $img = $manager->read($image->getRealPath());
            if ($width && $height) {
                $img->resize($width, $height);
            }

            if ($watermark) {
                $img = $this->applyWatermark($img);
            }

            $img->encode(new WebpEncoder(quality: 75));

            // Crear carpeta si no existe
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Guardar imagen procesada
            $img->save($uploadPath . $imageName);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                $inputName => 'No se pudo procesar la imagen. Verifica que el archivo no esté dañado e inténtalo de nuevo.',
            ]);
        }

        // La imagen anterior solo se elimina DESPUÉS de que la nueva ya se
        // guardó con éxito — si algo falla arriba, el registro se queda con
        // la imagen anterior en vez de terminar sin ninguna.
        if ($oldaPath) {
            $this->deleteImage($oldaPath);
        }

        // Devolver ruta pública
        return asset($path . '/' . $imageName);
    }

    return null; // Si no se subió archivo
}

    /**
     * Resolves an image field that can come from EITHER a fresh upload OR a
     * path picked from the Media Library gallery (AU.ImagePicker) — never
     * both. Fresh uploads delegate to uploadImage()/updateImage() completely
     * unchanged (byte-for-byte the same as before the gallery picker
     * existed); a library pick copies the existing file through the exact
     * same Intervention resize/watermark/webp pipeline into the module's own
     * destination folder, so "picked" and "freshly uploaded" produce
     * identical output shapes and neither module ends up with a live
     * reference to another module's file.
     *
     * Returns null when neither a file nor a library path was given, so
     * callers keep their existing "no new upload => keep old value" logic.
     */
    public function resolveOrCopyImage(Request $request, $inputName, $libraryFieldName, $path, $oldPath = null, $width = null, $height = null, $watermark = false)
    {
        if ($request->hasFile($inputName)) {
            return $oldPath !== null
                ? $this->updateImage($request, $inputName, $path, $oldPath, $width, $height, $watermark)
                : $this->uploadImage($request, $inputName, $path, $width, $height, $watermark);
        }

        $libraryPath = $request->input($libraryFieldName);
        if (!$libraryPath) {
            return null;
        }

        $sourceFull = realpath(UploadPath::full($libraryPath));
        $uploadsRoot = realpath(UploadPath::full('uploads'));
        if (!$sourceFull || !$uploadsRoot || !str_starts_with($sourceFull, $uploadsRoot)) {
            return null;
        }

        $originalName = pathinfo($sourceFull, PATHINFO_FILENAME);
        $cleanedName = Str::slug($originalName, '-');
        $imageName = $cleanedName . '-media_' . uniqid() . '.webp';
        $uploadPath = UploadPath::full($path . '/');

        try {
            $manager = new ImageManager(new Driver());

            $img = $manager->read($sourceFull);
            if ($width && $height) {
                $img->resize($width, $height);
            }
            if ($watermark) {
                $img = $this->applyWatermark($img);
            }
            $img->encode(new WebpEncoder(quality: 75));

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $img->save($uploadPath . $imageName);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                $inputName => 'No se pudo procesar la imagen seleccionada de la galería. Verifica el archivo e inténtalo de nuevo.',
            ]);
        }

        // Solo se elimina la anterior una vez que la nueva ya se guardó con éxito.
        if ($oldPath) {
            $this->deleteImage($oldPath);
        }

        return asset($path . '/' . $imageName);
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

    $fullPath = UploadPath::full($relativePath);

    if (File::exists($fullPath)) {
        File::delete($fullPath);
        return true;
    }

    return false;

    }





}
