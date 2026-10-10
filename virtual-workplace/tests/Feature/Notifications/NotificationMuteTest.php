<?php

namespace Tests\Feature\Notifications;

use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Notifications\Services\SpatialNotifier;
use App\Mail\TaskActivityMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class NotificationMuteTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    public function test_profile_form_saves_bell_and_email_switches(): void
    {
        $this->actingAs($this->employee)
            ->post('/profile/notifications', ['email_assigned' => '1', 'inapp_comments' => '1', 'inapp_office' => '1'])
            ->assertRedirect();

        $prefs = $this->employee->fresh()->notification_preferences;
        $this->assertTrue($prefs['assigned']);
        $this->assertFalse($prefs['inapp']['assigned']);
        $this->assertTrue($prefs['inapp']['comments']);
        $this->assertTrue($prefs['inapp']['office']);

        $this->actingAs($this->employee)->get('/dashboard')->assertOk()
            ->assertSee('name="inapp_office"', false)->assertDontSee('name="email_office"', false);
    }

    public function test_a_muted_type_skips_the_bell_but_still_emails(): void
    {
        Mail::fake();
        $this->withoutDefer();
        $this->employee->forceFill(['notification_preferences' => ['assigned' => true, 'inapp' => ['assigned' => false]]])->save();

        NotificationService::notifyTaskAssigned($this->makeTask(), $this->employee->id, $this->manager);

        $this->assertSame(0, WorkplaceNotification::where('user_id', $this->employee->id)->where('type', 'task_assigned')->count());
        Mail::assertSent(TaskActivityMail::class, fn ($m) => $m->hasTo($this->employee->email));
    }

    public function test_office_pings_can_be_muted(): void
    {
        $this->manager->forceFill(['notification_preferences' => ['inapp' => ['office' => false]]])->save();

        SpatialNotifier::wave($this->employee, $this->org->id, $this->manager->id);
        SpatialNotifier::ring($this->employee, $this->org->id, $this->manager->id);
        $this->assertSame(0, WorkplaceNotification::where('user_id', $this->manager->id)->count());

        // Other types still arrive.
        NotificationService::notifyTaskAssigned($this->makeTask(), $this->manager->id, $this->owner);
        $this->assertSame(1, WorkplaceNotification::where('user_id', $this->manager->id)->count());
    }
}
