<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocalizeFurnitureImagesTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = public_path('images/furniture/catalog/test-qa');
        File::deleteDirectory($this->dir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    public function test_images_are_copied_and_only_copied_ones_are_repointed(): void
    {
        $ok = 'https://assets.kumospace.com/test-qa/desk-01/0.png';
        $broken = 'https://assets.kumospace.com/test-qa/lamp-01/0.png';
        $cat = DB::table('furniture_categories')->insertGetId(['name' => 'T', 'slug' => 't-qa', 'icon' => 'x', 'order' => 1]);
        DB::table('furniture_items')->insert([
            ['category_id' => $cat, 'name' => 'Desk', 'slug' => 'desk-qa', 'image_url' => $ok, 'thumbnail_url' => $ok, 'interaction_config' => json_encode(['frames' => [$ok]]), 'width' => 1, 'height' => 1, 'collision' => false, 'colors' => '[]', 'is_active' => true],
            ['category_id' => $cat, 'name' => 'Lamp', 'slug' => 'lamp-qa', 'image_url' => $broken, 'thumbnail_url' => null, 'interaction_config' => null, 'width' => 1, 'height' => 1, 'collision' => false, 'colors' => '[]', 'is_active' => true],
        ]);
        Http::fake([
            'assets.kumospace.com/test-qa/desk-01/*' => Http::response('PNGDATA', 200, ['Content-Type' => 'image/png']),
            'assets.kumospace.com/test-qa/lamp-01/*' => Http::response('nope', 404),
        ]);

        $this->artisan('furniture:localize-images')->assertSuccessful();
        $this->assertSame($ok, DB::table('furniture_items')->where('slug', 'desk-qa')->value('image_url'), 'dry run writes nothing');

        $this->artisan('furniture:localize-images', ['--apply' => true])->assertSuccessful();
        $desk = DB::table('furniture_items')->where('slug', 'desk-qa')->first();
        $this->assertSame('/images/furniture/catalog/test-qa/desk-01/0.png', $desk->image_url);
        $this->assertSame($desk->image_url, $desk->thumbnail_url);
        $this->assertSame([$desk->image_url], json_decode($desk->interaction_config, true)['frames']);
        $this->assertSame('PNGDATA', File::get($this->dir.'/desk-01/0.png'));
        $this->assertSame($broken, DB::table('furniture_items')->where('slug', 'lamp-qa')->value('image_url'), 'a failed copy keeps its address');
    }
}
