<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DuplicateImages\FilesystemUploadsScanner;
use App\Services\DuplicateImages\ImageReferenceRegistry;
use App\Support\MediaLibrary\ProductImageSearch;
use Illuminate\Support\Collection;
use App\Support\UploadPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\Support\InteractsWithAttributionTestMode;
use Tests\TestCase;

/**
 * Buscador del selector de imagenes del formulario de producto: ademas del nombre del archivo, la biblioteca de
 * medios encuentra las imagenes de un producto por su nombre, SKU, modelo (productModel) o marca.
 * Sin RefreshDatabase: las tablas minimas se crean a mano en la SQLite en memoria.
 */
class MediaLibraryProductSearchTest extends TestCase
{
    use InteractsWithAttributionTestMode;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAdminUsersTables();

        Schema::dropIfExists('product_image_galleries');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::create('brands', function ($t) {
            $t->id();
            $t->string('name');
        });
        Schema::create('products', function ($t) {
            $t->id();
            $t->string('name');
            $t->string('sku')->nullable();
            $t->string('productModel')->nullable();
            $t->unsignedBigInteger('brand_id')->nullable();
            $t->text('thumb_image')->nullable();
        });
        Schema::create('product_image_galleries', function ($t) {
            $t->id();
            $t->unsignedBigInteger('product_id');
            $t->string('image');
        });

        $this->dir = 'uploads/mltest-' . uniqid();

        // data() tambien avisa "tambien usada en..." consultando sliders, marcas, etc. (tablas que no existen en la
        // SQLite de pruebas); aqui solo interesa la busqueda, asi que ese aviso informativo se sustituye por uno vacio.
        $this->app->instance(ImageReferenceRegistry::class, new class(new FilesystemUploadsScanner()) extends ImageReferenceRegistry {
            public function findReferencesToMany(Collection $physicalPaths): Collection
            {
                return collect();
            }
        });
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(UploadPath::full($this->dir));
        parent::tearDown();
    }

    /** Crea un archivo de imagen falso bajo uploads/ y devuelve su ruta relativa. */
    private function file(string $name): string
    {
        $relative = $this->dir . '/' . $name;
        $full = UploadPath::full($relative);
        @mkdir(dirname($full), 0755, true);
        file_put_contents($full, 'fake-image-bytes');

        return $relative;
    }

    private function brand(string $name): int
    {
        return DB::table('brands')->insertGetId(['name' => $name]);
    }

    private function product(array $a): int
    {
        return DB::table('products')->insertGetId($a);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function names($response): array
    {
        return collect($response->json('rows'))->pluck('cells.name')->sort()->values()->all();
    }

    // ------------------------------------------------------------------ ProductImageSearch

    public function test_matches_by_name_sku_model_and_brand(): void
    {
        $honeywell = $this->brand('Honeywell');
        $mcd = $this->brand('McDonnell & Miller');
        $dc = $this->product(['name' => 'Controlador digital DC1000', 'sku' => 'DC1010CR-101', 'productModel' => 'DC1010', 'brand_id' => $honeywell, 'thumb_image' => asset('uploads/p/dc.webp')]);
        $this->product(['name' => '157S Control de Nivel', 'sku' => 'AL1-1584', 'productModel' => '157S', 'brand_id' => $mcd, 'thumb_image' => asset('uploads/p/157s.webp')]);

        $s = new ProductImageSearch();
        $this->assertSame(['uploads/p/dc.webp'], array_keys($s->match('controlador')));        // nombre
        $this->assertSame(['uploads/p/dc.webp'], array_keys($s->match('dc1010cr')));           // sku
        $this->assertSame(['uploads/p/dc.webp'], array_keys($s->match('DC1010')));             // modelo (sku tambien)
        $this->assertSame(['uploads/p/157s.webp'], array_keys($s->match('al1-1584')));         // sku
        $this->assertSame(['uploads/p/157s.webp'], array_keys($s->match('157s')));             // modelo
        $this->assertSame(['uploads/p/dc.webp'], array_keys($s->match('honeywell')));          // marca
        $this->assertSame(['uploads/p/157s.webp'], array_keys($s->match('mcdonnell')));        // marca parcial
        $this->assertSame([], $s->match('no-existe-xyz'));
        $this->assertSame([], $s->match('   '));
        $this->assertSame($dc, DB::table('products')->where('sku', 'DC1010CR-101')->value('id'));
    }

    public function test_every_word_must_match_some_field_and_returns_product_info(): void
    {
        $honeywell = $this->brand('Honeywell');
        $this->product(['name' => 'Controlador DC1000', 'sku' => 'DC1010CR', 'productModel' => 'DC1010', 'brand_id' => $honeywell, 'thumb_image' => 'uploads/p/dc.webp']);
        $this->product(['name' => 'Transmisor', 'sku' => 'TX-9', 'productModel' => 'TX9', 'brand_id' => $honeywell, 'thumb_image' => 'uploads/p/tx.webp']);

        $s = new ProductImageSearch();
        $hits = $s->match('dc1010 honeywell');                     // modelo + marca en campos distintos
        $this->assertSame(['uploads/p/dc.webp'], array_keys($hits));
        $this->assertSame('Controlador DC1000', $hits['uploads/p/dc.webp']['name']);
        $this->assertSame('DC1010CR', $hits['uploads/p/dc.webp']['sku']);
        $this->assertSame('Honeywell', $hits['uploads/p/dc.webp']['brand']);
        $this->assertSame('DC1010', $hits['uploads/p/dc.webp']['model']);

        $this->assertSame([], $s->match('dc1010 otra-marca'));    // una palabra sin coincidencia => nada
        $this->assertCount(2, $s->match('honeywell'));
    }

    public function test_gallery_images_are_included_and_stored_urls_are_normalized(): void
    {
        $b = $this->brand('Honeywell');
        $id = $this->product(['name' => 'Controlador', 'sku' => 'DC1', 'brand_id' => $b,
            'thumb_image' => 'http://otro-host.test/uploads/product/honeywell/webp/computers/a.webp?x=1']);
        DB::table('product_image_galleries')->insert(['product_id' => $id, 'image' => 'uploads/product/honeywell/gal-1.webp']);
        DB::table('product_image_galleries')->insert(['product_id' => $id, 'image' => '/uploads/product/honeywell/gal-2.webp']);

        $paths = array_keys((new ProductImageSearch())->match('dc1'));
        sort($paths);
        $this->assertSame([
            'uploads/product/honeywell/gal-1.webp',
            'uploads/product/honeywell/gal-2.webp',
            'uploads/product/honeywell/webp/computers/a.webp',
        ], $paths);
    }

    public function test_like_wildcards_in_the_search_are_escaped(): void
    {
        $this->product(['name' => 'Valvula 100%', 'sku' => 'V_1', 'thumb_image' => 'uploads/p/v.webp']);
        $this->product(['name' => 'Otra cosa', 'sku' => 'XYZ', 'thumb_image' => 'uploads/p/o.webp']);

        $s = new ProductImageSearch();
        // Un % o _ del texto vale como la letra que es: solo coincide con el producto que lo trae escrito.
        $this->assertSame(['uploads/p/v.webp'], array_keys($s->match('%')), 'un % suelto no es comodin (no coincide con todo)');
        $this->assertSame(['uploads/p/v.webp'], array_keys($s->match('100%')));
        $this->assertSame(['uploads/p/v.webp'], array_keys($s->match('_')), 'un _ suelto no es comodin');
        $this->assertSame(['uploads/p/v.webp'], array_keys($s->match('v_1')));
        $this->assertSame([], $s->match('x%z'), 'x%z no debe actuar como "x...z"');
        $this->assertSame([], $s->match('x_z'), 'x_z no debe actuar como "x?z"');
        $this->assertSame([], $s->match('o_ra'), '_ no es comodin de un caracter ("Otra" tiene "tra", no "o_ra")');
    }

    // ------------------------------------------------------------------ GET /admin/media-library/data

    public function test_data_endpoint_finds_images_by_product_fields_and_still_by_file_name(): void
    {
        $honeywell = $this->brand('Honeywell');
        $thumb = $this->file('9f3a-random-name.webp');
        $gallery = $this->file('b7c1-other-name.webp');
        $other = $this->file('dc-folleto.webp');                 // sin producto, pero su nombre contiene "dc"
        $unrelated = $this->file('zzz.webp');

        $id = $this->product(['name' => 'Controlador DC1000', 'sku' => 'DC1010CR-101', 'productModel' => 'DC1010', 'brand_id' => $honeywell, 'thumb_image' => asset($thumb)]);
        DB::table('product_image_galleries')->insert(['product_id' => $id, 'image' => $gallery]);
        $admin = $this->admin();

        $url = '/admin/media-library/data?filter=todas&per_page=60&search=';

        // Por SKU / modelo / marca / nombre: devuelve la principal y la de la galeria; no la ajena.
        foreach (['dc1010cr-101', 'DC1010', 'honeywell', 'controlador', 'dc1010 honeywell'] as $term) {
            $r = $this->actingAs($admin)->getJson($url . rawurlencode($term))->assertOk();
            $names = $this->names($r);
            $this->assertContains(basename($thumb), $names, $term);
            $this->assertContains(basename($gallery), $names, $term);
            $this->assertNotContains(basename($unrelated), $names, $term);
        }

        // Por nombre de archivo (comportamiento de siempre).
        $r = $this->actingAs($admin)->getJson($url . 'dc-folleto')->assertOk();
        $this->assertSame([basename($other)], $this->names($r));

        // Sin coincidencias.
        $this->assertSame([], $this->names($this->actingAs($admin)->getJson($url . 'no-existe-xyz')->assertOk()));
    }

    public function test_data_endpoint_exposes_the_product_of_each_image_for_the_picker(): void
    {
        $b = $this->brand('Honeywell');
        $thumb = $this->file('aaa.webp');
        $this->product(['name' => 'Controlador DC1000', 'sku' => 'DC1010CR', 'productModel' => 'DC1010', 'brand_id' => $b, 'thumb_image' => asset($thumb)]);

        $r = $this->actingAs($this->admin())->getJson('/admin/media-library/data?filter=todas&per_page=60&search=dc1010')->assertOk();
        $row = collect($r->json('rows'))->firstWhere('cells.name', 'aaa.webp');

        $this->assertNotNull($row);
        $this->assertSame('Controlador DC1000', $row['cells']['product']['name']);
        $this->assertSame('DC1010CR', $row['cells']['product']['sku']);
        $this->assertSame('Honeywell', $row['cells']['product']['brand']);
        $this->assertSame('DC1010', $row['cells']['product']['model']);
        // El texto de "Tamano" sigue llevando el SKU (la biblioteca lo muestra asi).
        $this->assertSame('SKU: DC1010CR', $row['cells']['size']['subtext']);
    }

    public function test_data_endpoint_without_search_is_unchanged_and_requires_the_site_module(): void
    {
        $b = $this->brand('X');
        $f = $this->file('plain.webp');
        $this->product(['name' => 'P', 'sku' => 'S', 'brand_id' => $b, 'thumb_image' => asset($f)]);

        $r = $this->actingAs($this->admin())->getJson('/admin/media-library/data?filter=todas&per_page=200')->assertOk();
        $this->assertContains('plain.webp', $this->names($r));

        auth()->logout();
        $this->getJson('/admin/media-library/data')->assertStatus(401);
    }
}
