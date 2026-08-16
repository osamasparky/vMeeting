<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
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
