<?php

namespace Tests\Feature;

use App\Domains\Administration\Jobs\RecordAuditLogJob;
use App\Domains\Administration\Models\AuditLog;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AuditLogJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
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
