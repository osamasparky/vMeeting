<?php

namespace Tests\Feature\Office;

use App\Domains\Chat\Models\Channel;
use App\Domains\Chat\Models\Message;
use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Meetings\Models\MeetingParticipant;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Projects\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

/**
 * The office assistant shows a person only their own meetings, tasks,
 * messages and notifications, answers typed requests from that data, and
 * never acts on its own.
 */
class AssistantTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    private function meeting(string $title, $creator, array $invitees = [], array $attrs = []): Meeting
    {
        $m = Meeting::create($attrs + [
            'organization_id' => $this->org->id, 'created_by' => $creator->id, 'title' => $title,
            'status' => 'scheduled', 'scope' => 'general', 'type' => 'scheduled', 'scheduled_at' => now()->addMinutes(15), 'duration_minutes' => 30,
        ]);
        foreach ($invitees as $u) {
            MeetingParticipant::create(['meeting_id' => $m->id, 'user_id' => $u->id, 'role' => 'participant']);
        }

        return $m;
    }

    public function test_the_briefing_has_only_my_meetings_tasks_messages_and_alerts(): void
    {
        $this->meeting('اجتماع الفريق', $this->manager, [$this->employee]);
        $this->meeting('Board review', $this->owner, [$this->otherEmployee]); // not mine
        $this->meeting('Planning', $this->manager, [$this->employee], ['scheduled_at' => now()->addDays(3)]);
        $this->task->update(['due_date' => now()->subDays(2)->toDateString()]);
        $this->makeTask(['assignee_id' => $this->otherEmployee->id, 'due_date' => now()->subDay()->toDateString()]); // someone else's

        $dm = Channel::create(['organization_id' => $this->org->id, 'type' => 'dm']);
        $dm->members()->attach([$this->employee->id, $this->manager->id]);
        Message::create(['channel_id' => $dm->id, 'organization_id' => $this->org->id, 'sender_id' => $this->manager->id, 'body' => 'Can you check the report?']);
        $other = Channel::create(['organization_id' => $this->org->id, 'type' => 'dm']);
        $other->members()->attach([$this->owner->id, $this->otherEmployee->id]);
        Message::create(['channel_id' => $other->id, 'organization_id' => $this->org->id, 'sender_id' => $this->owner->id, 'body' => 'private']);

        WorkplaceNotification::create(['user_id' => $this->employee->id, 'organization_id' => $this->org->id, 'type' => 'task_mention', 'title' => 'Mentioned', 'is_read' => false, 'action_url' => '/projects/x']);
        WorkplaceNotification::create(['user_id' => $this->otherEmployee->id, 'organization_id' => $this->org->id, 'type' => 'task_assigned', 'title' => 'Not yours', 'is_read' => false]);

        $b = $this->actingAs($this->employee)->getJson('/assistant/briefing')->assertOk()->json();

        $this->assertSame(['اجتماع الفريق'], array_column($b['meetings'], 'title'));
        $this->assertSame(['Planning'], array_column($b['invitations'], 'title'));
        $this->assertSame([$this->task->id], array_column($b['tasks']['overdue'], 'id'));
        $this->assertSame(1, $b['tasks']['overdue_count']);
        $this->assertCount(1, $b['messages']);
        $this->assertSame('Can you check the report?', $b['messages'][0]['preview']);
        $this->assertSame(1, $b['notifications']['unread']);
        $this->assertSame(1, $b['notifications']['counts']['task']);
        $this->assertStringNotContainsString('Board review', json_encode($b));
        $this->assertStringNotContainsString('private', json_encode($b));
        $this->assertStringNotContainsString('Not yours', json_encode($b));
        // A meeting in 15 minutes comes first in the headline.
        $this->assertStringContainsString('اجتماع الفريق', $b['headline']['text']);
    }

    public function test_an_empty_day_has_no_headline_and_no_fake_items(): void
    {
        $this->task->update(['due_date' => now()->addWeek()->toDateString()]);

        $b = $this->actingAs($this->otherEmployee)->getJson('/assistant/briefing')->assertOk()->json();

        $this->assertNull($b['headline']);
        $this->assertSame([], $b['meetings']);
        $this->assertSame(0, $b['notifications']['unread']);
    }

    public function test_typed_requests_are_answered_from_my_data(): void
    {
        $team = $this->meeting('اجتماع الفريق', $this->manager, [$this->employee]);
        $this->meeting('Design sync', $this->employee);
        $this->task->update(['due_date' => now()->subDay()->toDateString()]);

        $ask = fn (string $m) => $this->actingAs($this->employee)->postJson('/assistant/ask', ['message' => $m])->assertOk()->json();

        $r = $ask('ما اجتماعاتي اليوم؟');
        $this->assertSame('meetings', $r['intent']);
        $this->assertEqualsCanonicalizing(['اجتماع الفريق', 'Design sync'], array_column($r['items'], 'title'));

        $r = $ask('ما المهام المتأخرة؟');
        $this->assertSame('overdue', $r['intent']);
        $this->assertSame($this->task->title, $r['items'][0]['title']);

        $r = $ask('أرني تنبيهات المكتب');
        $this->assertSame('alerts', $r['action']['type']);
        $this->assertSame('office', $r['action']['filter']);

        $r = $ask('افتح اجتماع الفريق');
        $this->assertSame('open_meeting', $r['intent']);
        $this->assertCount(1, $r['items']);
        $this->assertSame('اجتماع الفريق', $r['items'][0]['title']);
        $this->assertSame('url', $r['items'][0]['action']['type'], 'a meeting without a room points at the meetings list');

        $team->update(['status' => 'active']);
        $r = $ask('Join the team meeting');
        $this->assertSame('open_meeting', $r['intent']);

        $this->assertSame('unknown', $ask('أخبرني نكتة')['intent']);
    }

    public function test_requests_are_validated_and_non_members_are_refused(): void
    {
        $this->actingAs($this->employee)->postJson('/assistant/ask', ['message' => str_repeat('a', 301)])->assertStatus(422);
        $this->actingAs($this->employee)->postJson('/assistant/ask', [])->assertStatus(422);

        // Someone with no active membership anywhere gets nothing.
        $loner = User::factory()->create();
        $this->actingAs($loner)->getJson('/assistant/briefing')->assertForbidden();
        $this->actingAs($loner)->postJson('/assistant/ask', ['message' => 'meetings'])->assertForbidden();
    }

    public function test_the_bell_filters_still_work_through_the_shared_categories(): void
    {
        foreach (['task_assigned', 'meeting_scheduled', 'wave', 'project_update'] as $type) {
            WorkplaceNotification::create(['user_id' => $this->employee->id, 'organization_id' => $this->org->id, 'type' => $type, 'title' => $type, 'is_read' => false]);
        }
        $titles = fn (string $f) => array_column($this->actingAs($this->employee)->getJson("/api/notifications?page=1&filter={$f}")->assertOk()->json('notifications'), 'title');

        $this->assertSame(['task_assigned'], $titles('task'));
        $this->assertSame(['meeting_scheduled'], $titles('meeting'));
        $this->assertSame(['wave'], $titles('office'));
        $this->assertCount(4, $titles('all'));
    }

    public function test_every_member_page_has_the_assistant(): void
    {
        foreach (['/office', '/dashboard', '/projects/'.$this->project->id, '/editor'] as $url) {
            $html = $this->actingAs($this->owner)->get($url)->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, 'id="ula-assistant"'), "{$url} has the assistant once");
            $this->assertStringContainsString('/js/assistant.js', $html, $url);
            $this->assertStringContainsString('/images/assistant/ula-assistant-avatar.webp', $html, $url);
        }
    }

    public function test_mentions_point_the_bubble_at_task_alerts(): void
    {
        WorkplaceNotification::create(['user_id' => $this->employee->id, 'organization_id' => $this->org->id, 'type' => 'task_mention', 'title' => 'Mentioned', 'is_read' => false]);
        $this->task->update(['due_date' => now()->addWeek()->toDateString()]);

        $h = $this->actingAs($this->employee)->getJson('/assistant/briefing')->assertOk()->json('headline');

        $this->assertSame(['type' => 'alerts', 'filter' => 'task'], array_intersect_key($h['action'], ['type' => 1, 'filter' => 1]));
    }
}
