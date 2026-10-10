<?php

namespace Tests\Feature\Notifications;

use App\Domains\Meetings\Models\Meeting;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Notifications\Services\NotificationService;
use App\Mail\TaskActivityMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class NotificationHousekeepingTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    private function notice(int $daysAgo, bool $read): WorkplaceNotification
    {
        $n = WorkplaceNotification::create(['user_id' => $this->employee->id, 'organization_id' => $this->org->id, 'type' => 'task_assigned', 'title' => "t$daysAgo", 'data' => [], 'is_read' => $read]);
        $n->forceFill(['created_at' => now()->subDays($daysAgo)])->save();

        return $n;
    }

    public function test_old_notifications_are_pruned(): void
    {
        $recentRead = $this->notice(10, true);
        $oldRead = $this->notice(100, true);
        $oldUnread = $this->notice(100, false);
        $ancientUnread = $this->notice(200, false);

        $this->artisan('model:prune', ['--model' => [WorkplaceNotification::class]])->assertSuccessful();

        $left = WorkplaceNotification::pluck('id')->all();
        $this->assertEqualsCanonicalizing([$recentRead->id, $oldUnread->id], $left);
        $this->assertNotContains($oldRead->id, $left);
        $this->assertNotContains($ancientUnread->id, $left);
    }

    public function test_meeting_invitations_are_emailed_unless_turned_off(): void
    {
        Mail::fake();
        $meeting = new Meeting(['title' => 'Kickoff', 'organization_id' => $this->org->id]);
        $meeting->id = (string) Str::uuid();

        $this->withoutDefer();
        NotificationService::notifyMeetingScheduled($meeting, $this->employee, $this->manager);
        Mail::assertSent(TaskActivityMail::class, fn ($m) => $m->hasTo($this->employee->email));

        $this->employee->forceFill(['notification_preferences' => ['meetings' => false]])->save();
        Mail::fake();
        NotificationService::notifyMeetingScheduled($meeting, $this->employee, $this->manager);
        Mail::assertNothingSent();
    }
}
