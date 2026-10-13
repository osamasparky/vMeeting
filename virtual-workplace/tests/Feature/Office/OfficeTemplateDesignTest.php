<?php

namespace Tests\Feature\Office;

use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Plan;
use App\Domains\Workspace\Models\FurnitureItem;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\MapObject;
use App\Domains\Workspace\Models\OfficeTemplate;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Templates\OfficeTemplateDesigner;
use App\Models\User;
use Database\Seeders\BlueprintOfficeSeeder;
use Database\Seeders\FullFurnitureCatalogSeeder;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeTemplateDesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(FullFurnitureCatalogSeeder::class);
    }

    /** Saves the designed template the way office-templates:rebuild --apply does, without rendering the floor image. */
    private function installTemplate(string $slug): array
    {
        $design = (new OfficeTemplateDesigner)->design($slug);
        $plan = Plan::where('slug', $slug)->firstOrFail();
        $tile = OfficeTemplateDesigner::TILE;
        OfficeTemplate::getForPlan($plan)->forceFill([
            'plan_id' => $plan->id,
            'plan_slug' => $slug,
            'width' => $design['width'],
            'height' => $design['height'],
            'tile_size' => $tile,
            'layout_data' => ['background_width' => $design['width'] * $tile, 'background_height' => $design['height'] * $tile, 'background_image_url' => '/x.jpg'],
            'rooms_data' => $design['rooms'],
            'objects_data' => $design['objects'],
        ])->save();

        return $design;
    }

    private function company(string $plan)
    {
        return app(CreateOrganizationAction::class)->execute(['name' => "{$plan} Co", 'plan_slug' => $plan], User::factory()->create());
    }

    public function test_the_designs_have_the_requested_rooms_and_pass_the_editor_rules(): void
    {
        $this->artisan('office-templates:rebuild')->assertSuccessful();

        $free = (new OfficeTemplateDesigner)->design('free');
        $this->assertSame(['meeting', 'private', 'focus'], array_column($free['rooms'], 'type'));
        $this->assertNotEmpty($free['objects']);

        $business = (new OfficeTemplateDesigner)->design('business');
        $this->assertGreaterThan(200, count($business['objects']));
        $this->assertCount(19, $business['rooms']);
        $capacities = array_count_values(array_map(fn ($r) => $r['type'].':'.$r['capacity'], $business['rooms']));
        $this->assertSame(7, $capacities['private:4']); // 6 small offices + the CEO office
        $this->assertSame(3, $capacities['private:6']);
        $this->assertSame(4, $capacities['focus:1']);
    }

    public function test_the_rebuild_refuses_a_catalogue_that_lacks_pieces(): void
    {
        FurnitureItem::where('slug', 'desk_10')->delete();

        $this->artisan('office-templates:rebuild', ['--apply' => true])
            ->expectsOutputToContain('desk_10')
            ->assertFailed();
    }

    public function test_a_new_business_company_gets_every_room_and_its_furniture(): void
    {
        $design = $this->installTemplate('business');
        $org = $this->company('business');

        (new BlueprintOfficeSeeder)->seedOrganizationOffice($org->fresh('plan'));

        $map = Map::where('organization_id', $org->id)->firstOrFail();
        $this->assertSame(19, Room::withoutGlobalScopes()->where('organization_id', $org->id)->count());
        $this->assertSame(count($design['objects']), MapObject::where('map_id', $map->id)->count());
        $this->assertSame(16, $map->tile_size);
        // Furniture is walk-through unless someone makes it solid.
        $this->assertSame(0, MapObject::where('map_id', $map->id)->where('collision', true)->count());
    }

    public function test_a_room_limit_drops_the_furniture_of_the_rooms_left_out(): void
    {
        $design = $this->installTemplate('business');
        Plan::where('slug', 'business')->update(['room_limit' => 2]);
        $org = $this->company('business');

        (new BlueprintOfficeSeeder)->seedOrganizationOffice($org->fresh('plan'));

        $map = Map::where('organization_id', $org->id)->firstOrFail();
        $this->assertSame(2, Room::withoutGlobalScopes()->where('organization_id', $org->id)->count());
        $placed = MapObject::where('map_id', $map->id)->count();
        $this->assertGreaterThan(0, $placed);
        $this->assertLessThan(count($design['objects']), $placed);

        // Nothing sits inside a room the company does not have.
        $third = $design['rooms'][2]['bounds'];
        $this->assertSame(0, MapObject::where('map_id', $map->id)->get()->filter(function ($o) use ($third) {
            $cx = $o->position['x'] + $o->size['width'] / 2;
            $cy = $o->position['y'] + $o->size['height'] / 2;

            return $cx >= $third['x'] && $cx <= $third['x'] + $third['width'] && $cy >= $third['y'] && $cy <= $third['y'] + $third['height'];
        })->count());
    }

    public function test_a_new_office_branch_is_laid_out_from_the_template(): void
    {
        $design = $this->installTemplate('business');
        $owner = User::factory()->create();
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Branch Co', 'plan_slug' => 'business'], $owner);
        (new BlueprintOfficeSeeder)->seedOrganizationOffice($org->fresh('plan'));

        $this->actingAs($owner)->post('/offices', ['name' => 'Riyadh', 'timezone' => 'Asia/Riyadh'])->assertRedirect();

        $floor = $org->floors()->where('name', 'Riyadh')->firstOrFail();
        $map = Map::where('floor_id', $floor->id)->firstOrFail();
        $this->assertSame($design['width'], $map->width);
        $this->assertSame(19, $map->rooms()->count());
        $this->assertSame(count($design['objects']), MapObject::where('map_id', $map->id)->count());
    }

    public function test_a_new_office_gets_only_the_rooms_left_under_the_plan_limit(): void
    {
        $design = $this->installTemplate('business');
        Plan::where('slug', 'business')->update(['room_limit' => 21]);
        $owner = User::factory()->create();
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Limit Co', 'plan_slug' => 'business'], $owner);
        (new BlueprintOfficeSeeder)->seedOrganizationOffice($org->fresh('plan')); // uses 19 of the 21

        $this->actingAs($owner)->post('/offices', ['name' => 'Jeddah', 'timezone' => 'Asia/Riyadh'])->assertRedirect();

        $map = Map::where('floor_id', $org->floors()->where('name', 'Jeddah')->value('id'))->firstOrFail();
        $this->assertSame(2, $map->rooms()->count());
        $this->assertLessThan(count($design['objects']), MapObject::where('map_id', $map->id)->count());
        $this->assertSame(21, Room::withoutGlobalScopes()->where('organization_id', $org->id)->count());
    }

    public function test_a_company_without_a_settings_row_can_open_its_dashboard(): void
    {
        $this->installTemplate('free');
        $owner = User::factory()->create();
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Bare Co'], $owner);
        $org->settings()->delete();

        $this->actingAs($owner)->get('/dashboard')->assertOk();
    }
}
