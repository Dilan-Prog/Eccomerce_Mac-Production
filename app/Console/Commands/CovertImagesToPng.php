<?php

namespace App\Console\Commands;
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
    protected $signature = 'images:convert-to-png';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convierte todas las imagenes de los productos a png';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sourcePath = public_path('uploads');
        $destPath = public_path('uploads/image-png');

        if (!file_exists($destPath)) {
            mkdir($destPath, 0755, true);
        }

        $manager = new ImageManager(new Driver());

        $files = File::allFiles($sourcePath);

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'webp', 'bmp', 'gif', 'png'])) {
                $originalName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $newName = $originalName . '_converted.png';
                $newPath = $destPath . '/' . $newName;

                try {
                    $img = $manager->read($file->getRealPath());
                    $img->toPng()->save($newPath);
                    $this->info("Convertido: {$file->getFilename()} -> {$newName}");
                } catch (\Exception $e) {
                    $this->error("Error con {$file->getFilename()}: " . $e->getMessage());
                }
            }
        }

        $this->info('¡Conversión masiva completada!');
    }
}
