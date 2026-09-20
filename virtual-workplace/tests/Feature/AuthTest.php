<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_register_and_create_organization(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Sara Al-Mansour',
            'email' => 'sara@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'sara@example.com']);
        $token = $response->json('token');

        // Now create organization using the returned token
        $orgResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/organizations', [
                'name' => 'Sara Labs',
            ]);

        $orgResponse->assertStatus(201)
            ->assertJsonPath('organization.name', 'Sara Labs');

        $this->assertDatabaseHas('organizations', ['name' => 'Sara Labs']);
    }

    public function test_web_signup_form_creates_user_and_organization_on_the_free_plan(): void
    {
        $response = $this->post('/register', [
            'name' => 'Layla Founder',
            'email' => 'layla@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'organization_name' => 'Layla Co',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'layla@example.com']);
        $this->assertDatabaseHas('organizations', ['name' => 'Layla Co']);
    }

    public function test_web_signup_form_with_a_paid_plan_redirects_to_payment(): void
    {
        $starter = \App\Domains\Tenancy\Models\Plan::where('slug', 'starter')->firstOrFail();

        $response = $this->post('/register', [
            'name' => 'Omar Founder',
            'email' => 'omar@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'organization_name' => 'Omar Co',
            'plan_id' => $starter->id,
        ]);

        $response->assertRedirect(route('subscription.payment', ['plan' => $starter->id]));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('organizations', ['name' => 'Omar Co']);
    }

    public function test_user_can_login_and_fetch_me(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);

        $token = $loginResponse->json('token');

        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('user.email', 'testuser@example.com');
    }
}
