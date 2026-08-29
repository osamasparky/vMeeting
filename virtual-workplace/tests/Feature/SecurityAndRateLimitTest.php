<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\RealtimeTokenService;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_global_security_headers_are_present_in_responses(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Content-Security-Policy-Report-Only');

        $csp = $response->headers->get('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("report-uri /csp-violation-report", $csp);
    }

    public function test_csp_violation_report_endpoint_accepts_reports(): void
    {
        $payload = [
            'csp-report' => [
                'document-uri' => 'https://nextspace.munazzah.com/dashboard',
                'referrer' => '',
                'violated-directive' => 'script-src',
                'effective-directive' => 'script-src',
                'original-policy' => "default-src 'self'",
                'blocked-uri' => 'https://unauthorized-cdn.com/evil.js',
                'status-code' => 200,
            ],
        ];

        $response = $this->postJson('/csp-violation-report', $payload);
        $response->assertStatus(204);
    }

    public function test_realtime_token_service_generates_signed_jwt_with_tenant_claims(): void
    {
        $user = User::factory()->create(['name' => 'Sara Al-Ghamdi']);
        $action = app(CreateOrganizationAction::class);
        $org = $action->execute(['name' => 'Saudi Tech Hub'], $user);

        $tokenService = app(RealtimeTokenService::class);
        $token = $tokenService->generateToken($user, $org);

        $this->assertNotEmpty($token);
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'Generated realtime token must be a valid 3-part JWT.');

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $this->assertEquals($user->id, $payload['sub']);
        $this->assertEquals($org->id, $payload['organization_id']);
        $this->assertEquals('Sara Al-Ghamdi', $payload['name']);
        $this->assertGreaterThan(time(), $payload['exp']);
    }
}
