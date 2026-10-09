<?php

namespace Tests\Feature\Projects;

use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Mail\TaskActivityMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class TaskNotificationsTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->buildProjectWorkspace();
    }

    private function noticesFor(string $userId, string $type): int
    {
        return WorkplaceNotification::where('user_id', $userId)->where('type', $type)->count();
    }

    public function test_reporter_and_assignee_follow_a_new_task(): void
    {
        $task = $this->makeTask(['reporter_id' => $this->manager->id, 'assignee_id' => $this->employee->id]);

        $this->assertEqualsCanonicalizing([$this->manager->id, $this->employee->id], $task->watchers()->pluck('users.id')->all());
    }

    public function test_members_can_follow_and_unfollow(): void
    {
        Sanctum::actingAs($this->otherEmployee);
        $this->postJson($this->api("/tasks/{$this->task->id}/watch"))->assertOk();
        $this->assertTrue($this->task->watchers()->where('users.id', $this->otherEmployee->id)->exists());

        $this->deleteJson($this->api("/tasks/{$this->task->id}/watch"))->assertOk();
        $this->assertFalse($this->task->watchers()->where('users.id', $this->otherEmployee->id)->exists());

        Sanctum::actingAs($this->outsider);
        $this->postJson("/api/v1/organizations/{$this->org->id}/tasks/{$this->task->id}/watch")->assertForbidden();
    }

    public function test_a_comment_notifies_followers_except_the_author_and_by_email(): void
    {
        $this->task->addWatchers([$this->manager->id]);

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/tasks/{$this->task->id}/comments"), ['body' => 'Pushed a fix'])->assertCreated();

        $this->assertSame(1, $this->noticesFor($this->manager->id, 'task_comment'));
        $this->assertSame(0, $this->noticesFor($this->employee->id, 'task_comment'));
        Mail::assertSent(TaskActivityMail::class, fn ($m) => $m->hasTo($this->manager->email));
    }

    public function test_a_mentioned_follower_gets_one_mention_not_a_duplicate_comment_notice(): void
    {
        $this->task->addWatchers([$this->manager->id]);
        $this->manager->update(['name' => 'Layla Manager']);

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/tasks/{$this->task->id}/comments"), ['body' => 'Please check @Layla'])->assertCreated();

        $this->assertSame(1, $this->noticesFor($this->manager->id, 'task_mention'));
        $this->assertSame(0, $this->noticesFor($this->manager->id, 'task_comment'));
    }

    public function test_commenter_starts_following(): void
    {
        Sanctum::actingAs($this->otherEmployee);
        $this->postJson($this->api("/tasks/{$this->task->id}/comments"), ['body' => 'Joining in'])->assertCreated();

        $this->assertTrue($this->task->watchers()->where('users.id', $this->otherEmployee->id)->exists());
    }

    public function test_status_change_notifies_followers(): void
    {
        $this->task->addWatchers([$this->otherEmployee->id]);

        Sanctum::actingAs($this->employee);
        $this->patchJson($this->api("/tasks/{$this->task->id}/status"), ['status' => 'review'])->assertOk();

        $this->assertSame(1, $this->noticesFor($this->otherEmployee->id, 'task_status'));
        $this->assertSame(0, $this->noticesFor($this->employee->id, 'task_status'));
    }

    public function test_email_respects_the_users_preferences(): void
    {
        $this->manager->forceFill(['notification_preferences' => ['comments' => false]])->save();
        $this->task->addWatchers([$this->manager->id]);

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/tasks/{$this->task->id}/comments"), ['body' => 'Quiet please'])->assertCreated();

        // In-app always; email suppressed.
        $this->assertSame(1, $this->noticesFor($this->manager->id, 'task_comment'));
        Mail::assertNotSent(TaskActivityMail::class, fn ($m) => $m->hasTo($this->manager->email));
    }

    public function test_preferences_are_saved_from_the_profile_form(): void
    {
        $this->actingAs($this->employee)
            ->post('/profile/notifications', ['email_assigned' => '1', 'email_due' => '1'])
            ->assertRedirect();

        $prefs = $this->employee->fresh()->notification_preferences;
        $this->assertTrue($prefs['assigned']);
        $this->assertFalse($prefs['comments']);
        $this->assertTrue($prefs['due']);
    }

    public function test_due_reminders_go_out_once_per_due_date(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 10)->setTime(9, 0));
        $soon = $this->makeTask(['assignee_id' => $this->employee->id, 'due_date' => '2026-10-11', 'status' => 'ready']);
        $late = $this->makeTask(['assignee_id' => $this->otherEmployee->id, 'due_date' => '2026-10-08', 'status' => 'ready']);
        $this->makeTask(['assignee_id' => $this->employee->id, 'due_date' => '2026-10-08', 'status' => 'done']);

        $this->artisan('tasks:due-reminders')->assertSuccessful();
        $this->artisan('tasks:due-reminders')->assertSuccessful();

        $this->assertSame(1, $this->noticesFor($this->employee->id, 'task_due_soon'));
        $this->assertSame(1, $this->noticesFor($this->otherEmployee->id, 'task_overdue'));
        $this->assertSame(0, $this->noticesFor($this->employee->id, 'task_overdue'));

        // Moving the due date earns a fresh reminder.
        $soon->update(['due_date' => '2026-10-11']);
        $late->update(['due_date' => '2026-10-09']);
        $this->artisan('tasks:due-reminders')->assertSuccessful();
        $this->assertSame(2, $this->noticesFor($this->otherEmployee->id, 'task_overdue'));
    }

    public function test_notification_links_open_the_task_in_its_project(): void
    {
        Sanctum::actingAs($this->manager);
        $this->patchJson($this->api("/tasks/{$this->task->id}/assign"), ['assignee_id' => $this->otherEmployee->id])->assertOk();

        $notice = WorkplaceNotification::where('user_id', $this->otherEmployee->id)->where('type', 'task_assigned')->first();
        $this->assertSame("/projects/{$this->project->id}?task={$this->task->id}", $notice->action_url);
    }
}
