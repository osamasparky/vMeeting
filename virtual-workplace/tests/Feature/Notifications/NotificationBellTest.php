<?php

namespace Tests\Feature\Notifications;

use App\Domains\Notifications\Models\WorkplaceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    public function test_project_page_has_the_live_bell(): void
    {
        $this->actingAs($this->manager)->get('/projects/'.$this->project->id)
            ->assertOk()
            ->assertSee('id="notifWrapper"', false)
            ->assertSee('id="notifListContainer"', false)
            ->assertSee('window.toggleNotificationDropdown', false);
    }

    public function test_dashboard_has_exactly_one_bell(): void
    {
        $html = $this->actingAs($this->manager)->get('/dashboard')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'id="notifWrapper"'));
        $this->assertSame(1, substr_count($html, 'window.toggleNotificationDropdown'));
    }

    public function test_feed_returns_raw_text_for_the_client_to_escape(): void
    {
        WorkplaceNotification::create([
            'user_id' => $this->manager->id, 'organization_id' => $this->org->id, 'type' => 'task_intake',
            'title' => '<img src=x onerror=alert(1)>', 'body' => 'b', 'action_url' => '/x', 'data' => [], 'is_read' => false,
        ]);

        // The JSON feed carries text, and the bell escapes it before rendering (see notification-bell.blade.php).
        $this->actingAs($this->manager)->getJson('/api/notifications')
            ->assertOk()->assertJsonPath('unread_count', 1)->assertJsonPath('notifications.0.title', '<img src=x onerror=alert(1)>');
    }
}
