<?php

namespace Tests\Feature\Notifications;

use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Projects\Actions\SetTaskAssigneesAction;
use App\Mail\TaskActivityMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class NotificationLanguageTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    public function test_the_language_used_is_saved_on_the_account_and_restored_on_a_new_browser(): void
    {
        $this->actingAs($this->employee)->get('/dashboard?lang=ar')->assertOk();
        $this->assertSame('ar', $this->employee->fresh()->locale);

        // A fresh session with no cookie falls back to the account, not the app default.
        $this->flushSession();
        $this->actingAs($this->employee->fresh())->get('/dashboard')->assertOk();
        $this->assertSame('ar', app()->getLocale());
        $this->assertSame('ar', $this->employee->fresh()->locale);
    }

    public function test_notifications_are_written_in_the_recipients_language(): void
    {
        $this->employee->forceFill(['locale' => 'ar'])->save();
        $this->otherEmployee->forceFill(['locale' => 'en'])->save();

        // The person acting uses English.
        app()->setLocale('en');
        app(SetTaskAssigneesAction::class)->execute($this->makeTask(['title' => 'Plan']), [$this->employee->id, $this->otherEmployee->id], $this->manager);

        $ar = WorkplaceNotification::where('user_id', $this->employee->id)->where('type', 'task_assigned')->value('title');
        $en = WorkplaceNotification::where('user_id', $this->otherEmployee->id)->where('type', 'task_assigned')->value('title');
        $this->assertSame(__('New Task Assigned: :task', ['task' => 'Plan'], 'ar'), $ar);
        $this->assertSame('New Task Assigned: Plan', $en);
        $this->assertNotSame($ar, $en);
        $this->assertSame('en', app()->getLocale(), 'the request language is restored afterwards');
    }

    public function test_scheduler_reminders_and_emails_follow_the_recipient(): void
    {
        Mail::fake();
        $this->withoutDefer();
        $this->travelTo(now()->setDate(2026, 10, 10)->setTime(9, 0));
        $this->employee->forceFill(['locale' => 'ar'])->save();
        $this->task->forceDelete();
        $this->makeTask(['title' => 'Report', 'assignee_id' => $this->employee->id, 'due_date' => '2026-10-11', 'status' => 'ready']);

        $this->artisan('tasks:due-reminders')->assertSuccessful();

        $title = WorkplaceNotification::where('user_id', $this->employee->id)->where('type', 'task_due_soon')->value('title');
        $this->assertSame(__('⏰ ":task" is due tomorrow', ['task' => 'Report'], 'ar'), $title);
        $this->assertStringNotContainsString('due tomorrow', $title);
        Mail::assertSent(TaskActivityMail::class, fn ($mail) => $mail->hasTo($this->employee->email) && $mail->locale === 'ar');
    }
}
