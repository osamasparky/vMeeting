<?php

namespace Tests\Feature;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\People\Models\Department;
use App\Domains\People\Models\UserProfile;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\FurnitureCategory;
use App\Domains\Workspace\Models\FurnitureItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\FurnitureSeeder::class);
    }

    /**
     * Test that dashboard member rendering does NOT trigger N+1 queries.
     */
    public function test_dashboard_eager_loading_prevents_n_plus_one_queries(): void
    {
        $owner = User::factory()->create(['email' => 'org_admin@test.local']);
        $action = app(CreateOrganizationAction::class);
        $organization = $action->execute(['name' => 'Performance Corp'], $owner);

        $dept = Department::create([
            'organization_id' => $organization->id,
            'name' => 'Engineering',
        ]);

        $memberRole = Role::where('slug', 'employee')->first();

        // Create 10 members with profiles
        for ($i = 0; $i < 10; $i++) {
            $u = User::factory()->create(['email' => "dev{$i}@test.local"]);
            OrganizationMember::create([
                'organization_id' => $organization->id,
                'user_id' => $u->id,
                'role_id' => $memberRole->id,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            UserProfile::create([
                'user_id' => $u->id,
                'organization_id' => $organization->id,
                'department_id' => $dept->id,
                'job_title' => "Engineer {$i}",
            ]);
        }

        // Warm up initial workspace bootstrap
        $this->actingAs($owner)->get('/dashboard');

        // Count queries executed during steady-state dashboard view rendering
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($owner)->get('/dashboard');
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Without eager loading, 10 members would add 10 separate queries for user_profiles.
        // With eager loading, user_profiles is loaded exactly once in a batched query.
        $profileQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'user_profiles') || str_contains($q['query'], 'profiles');
        });

        // Ensure user_profiles is queried at most 1 time in batch (IN clause), never 10 times!
        $this->assertLessThanOrEqual(1, count($profileQueries), 'N+1 detected: user_profiles queried multiple times!');
        $this->assertLessThanOrEqual(35, $queryCount, "Total dashboard steady-state query count ({$queryCount}) exceeded baseline limit.");
    }

    /**
     * Test that furniture catalog is cached in memory/cache store.
     */
    public function test_furniture_catalog_is_cached_and_serves_from_cache(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $action = app(CreateOrganizationAction::class);
        $organization = $action->execute(['name' => 'Cache Test Org'], $user);

        $this->assertFalse(Cache::has('furniture_catalog_active'));

        // First hit to /office populates cache
        $response = $this->actingAs($user)->get('/office');
        $response->assertStatus(200);

        $this->assertTrue(Cache::has('furniture_catalog_active'), 'Active furniture items were not cached.');
        $this->assertIsIterable(Cache::get('furniture_catalog_active'));

        // Hit to /editor populates category cache as well
        $response = $this->actingAs($user)->get('/editor');
        $response->assertStatus(200);

        $this->assertTrue(Cache::has('furniture_categories_with_items'), 'Furniture categories with items were not cached.');
    }

    /**
     * Test that furniture cache is properly invalidated when SuperAdmin mutates catalog.
     */
    public function test_furniture_cache_is_invalidated_on_superadmin_mutation(): void
    {
        $superAdmin = User::factory()->create(['email' => 'info@meemdtt.com']);
        $category = FurnitureCategory::first();

        // Seed cache
        Cache::put('furniture_catalog_active', ['dummy'], 3600);
        Cache::put('furniture_categories_with_items', ['dummy'], 3600);

        $this->assertTrue(Cache::has('furniture_catalog_active'));

        // SuperAdmin creates a new furniture item
        $response = $this->actingAs($superAdmin)->post('/superadmin/furniture/item', [
            'name' => 'Ultra Ergonomic Chair',
            'category_id' => $category->id,
            'width' => 1,
            'height' => 1,
            'collision' => 1,
            'colors' => '#00b4b3',
        ]);

        $response->assertRedirect();

        // Cache must be invalidated
        $this->assertFalse(Cache::has('furniture_catalog_active'), 'Cache was not invalidated after item creation.');
        $this->assertFalse(Cache::has('furniture_categories_with_items'), 'Category cache was not invalidated after item creation.');
    }

    /**
     * Test tenant isolation is preserved during eager loading.
     */
    public function test_tenant_isolation_is_preserved_during_eager_loading(): void
    {
        $userA = User::factory()->create(['email' => 'user_a@test.local']);
        $userB = User::factory()->create(['email' => 'user_b@test.local']);

        $action = app(CreateOrganizationAction::class);
        $orgA = $action->execute(['name' => 'Org Alpha'], $userA);
        $orgB = $action->execute(['name' => 'Org Beta'], $userB);

        // User A views Org Alpha dashboard
        $response = $this->actingAs($userA)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Org Alpha');
        $response->assertDontSee('Org Beta');
    }
}
