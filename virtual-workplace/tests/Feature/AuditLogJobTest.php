<?php

namespace Tests\Feature;

use App\Domains\Administration\Jobs\RecordAuditLogJob;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_audit_log_job_dispatches_and_records_entry(): void
    {
        $user = User::factory()->create();
        $action = app(CreateOrganizationAction::class);
        $org = $action->execute(['name' => 'Audit Test Org'], $user);

        // Execute job synchronously for testing
        $job = new RecordAuditLogJob(
            organizationId: $org->id,
            actorId: $user->id,
            action: 'room.created',
            targetType: 'Room',
            targetId: 'room-123',
            metadata: ['room_name' => 'Boardroom Alpha']
        );

        $job->handle();

        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $org->id,
            'actor_id' => $user->id,
            'action' => 'room.created',
            'target_type' => 'Room',
            'target_id' => 'room-123',
        ]);
    }
}
