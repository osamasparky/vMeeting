<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RemoveThirdPartyReferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_eraser_integration_links_are_removed(): void
    {
        $cat = DB::table('furniture_categories')->insertGetId(['name' => 'T', 'slug' => 't-wb', 'icon' => 'x', 'order' => 1]);
        $config = ['behavior' => ['type' => 'whiteboard', 'data' => [
            'whiteboardUrl' => 'https://app.eraser.io/',
            'embedUrl' => 'https://app.eraser.io/integration/other-product/undefined?layout=canvas',
            'previewUrl' => 'https://app.eraser.io/workspace/other-product:undefined/preview?layout=canvas',
        ]], 'tags' => ['office', 'kumogames', 'whiteboard'], 'embedUrl' => 'https://youtube.com/embed/x'];
        DB::table('furniture_items')->insert([
            'category_id' => $cat, 'name' => 'Whiteboard', 'slug' => 'wb-qa', 'width' => 1, 'height' => 1,
            'collision' => false, 'colors' => '[]', 'is_active' => true, 'interaction_config' => json_encode($config),
        ]);

        (require database_path('migrations/2026_10_16_000002_remove_third_party_references.php'))->up();

        $after = json_decode(DB::table('furniture_items')->where('slug', 'wb-qa')->value('interaction_config'), true);
        $this->assertSame(['whiteboardUrl' => 'https://app.eraser.io/'], $after['behavior']['data']);
        $this->assertSame(['office', 'ulagames', 'whiteboard'], $after['tags'], 'the old catalogue tag is renamed, lists keep their shape');
        $this->assertSame('https://youtube.com/embed/x', $after['embedUrl'], 'other embeds are left alone');
    }
}
