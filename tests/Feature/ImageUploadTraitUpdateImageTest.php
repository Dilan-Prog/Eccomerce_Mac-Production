<?php

namespace Tests\Feature;

use App\Support\UploadPath;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageUploadTraitUpdateImageTest extends TestCase
{
    public function test_update_image_deletes_old_file_stored_as_full_asset_url(): void
    {
        $relativeOldPath = 'uploads/test-fixtures/old-' . uniqid() . '.webp';
        $oldFullPath = UploadPath::full($relativeOldPath);

        @mkdir(dirname($oldFullPath), 0755, true);
        file_put_contents($oldFullPath, 'fake-old-image-bytes');
        $this->assertFileExists($oldFullPath);

        $storedOldUrl = asset($relativeOldPath);
        $this->assertStringStartsWith('http', $storedOldUrl);

        $uploaded = UploadedFile::fake()->image('new.jpg', 10, 10);
        $request = Request::create('/test', 'POST', [], [], ['image' => $uploaded]);

        $subject = new class {
            use ImageUploadTrait;
        };

        $newUrl = $subject->updateImage($request, 'image', 'uploads/test-fixtures', $storedOldUrl);

        $this->assertFileDoesNotExist($oldFullPath, 'Old file stored as a full asset() URL should be deleted by updateImage().');
        $this->assertNotNull($newUrl);

        $newRelativePath = 'uploads/test-fixtures/' . basename(parse_url($newUrl, PHP_URL_PATH));
        $newFullPath = UploadPath::full($newRelativePath);
        $this->assertFileExists($newFullPath);
        @unlink($newFullPath);
    }
}
