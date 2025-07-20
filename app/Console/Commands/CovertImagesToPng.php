<?php

namespace App\Console\Commands;

use App\Models\Slider;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use File;
use Illuminate\Console\Command;

class CovertImagesToPng extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:convert-png';
    protected $description = 'Mueve y convierte imágenes de sliders existentes a nuevas rutas y tamaños';
    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
     public function handle()
    {
        $sliders = Slider::all();
        $manager = new ImageManager(new Driver());

        foreach ($sliders as $slider) {
            // Ruta antigua completa
            $oldPath = ($slider->banner);

            if (File::exists($oldPath)) {
                $this->error("Imagen no encontrada: {$oldPath}");
                continue;
            }

            $fileName = pathinfo($oldPath, PATHINFO_BASENAME); // media_66e36dffd37b2.banner-dewit.webp

            // Nuevas rutas
            $newPathComputers = ("uploads/slider/webp/computers/{$fileName}");
            $newPathLaptop = ("uploads/slider/webp/laptop/{$fileName}");
            $newPathTablet = ("uploads/slider/webp/tablet/{$fileName}");
            $newPathPhone = ("uploads/slider/webp/phone/{$fileName}");

            // Copiar la original a /computers
            if (!File::exists(dirname($newPathComputers))) {
                File::makeDirectory(dirname($newPathComputers), 0755, true);
            }

            File::copy($oldPath, $newPathComputers);

            // Redimensionar desde computers a los demás
            $this->resizeAndSave($manager, $newPathComputers, $newPathLaptop, 1140, 380);
            $this->resizeAndSave($manager, $newPathComputers, $newPathTablet, 720, 240);
            $this->resizeAndSave($manager, $newPathComputers, $newPathPhone, 370, 125);

            // Actualizar BD
            $slider->banner = 'uploads/slider/webp/computers/' . $fileName;
            $slider->banner_laptop = 'uploads/slider/webp/laptop/' . $fileName;
            $slider->banner_tablet = 'uploads/slider/webp/tablet/' . $fileName;
            $slider->banner_phone = 'uploads/slider/webp/phone/' . $fileName;
            $slider->save();

            $this->info("✅ Slider ID {$slider->id} procesado.");
        }

        $this->info("🎉 Todos los sliders han sido actualizados correctamente.");
    }

    private function resizeAndSave($manager, $source, $target, $width, $height)
    {
        if (!File::exists(dirname($target))) {
            File::makeDirectory(dirname($target), 0755, true);
        }

        $image = $manager->read($source)
                         ->resize($width, $height)
                         ->toWebp(90);

        $image->save($target);
    }
}
