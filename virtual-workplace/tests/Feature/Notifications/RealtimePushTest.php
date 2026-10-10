<?php

namespace Tests\Feature\Notifications;

use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Actions\SetTaskAssigneesAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class RealtimePushTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
        config(['services.realtime.secret' => 'test-secret', 'services.realtime.http_url' => 'http://realtime.test']);
        Http::fake(['realtime.test/*' => Http::response(['delivered' => 1], 202)]);
    }

    public function test_new_notifications_ping_the_realtime_service_once_per_request(): void
    {
        // Two people assigned in one action → one call naming both.
        $task = $this->makeTask();
        Sanctum::actingAs($this->manager);
        $this->putJson(
            "/api/v1/organizations/{$this->org->id}/tasks/{$task->id}/assignees",
            ['user_ids' => [$this->employee->id, $this->otherEmployee->id]],
        )->assertOk();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $r) => $r->url() === 'http://realtime.test/internal/notifications'
            && $r->hasHeader('X-Realtime-Secret', 'test-secret')
            && collect($r['user_ids'])->sort()->values()->all() === collect([$this->employee->id, $this->otherEmployee->id])->sort()->values()->all()
            && ! str_contains($r->body(), 'title'));
    }

    public function test_muted_notices_and_missing_config_send_nothing(): void
    {
        $this->withoutDefer();

        $this->employee->forceFill(['notification_preferences' => ['inapp' => ['assigned' => false]]])->save();
        NotificationService::notifyTaskAssigned($this->makeTask(), $this->employee->id, $this->manager);
        Http::assertNothingSent();

        config(['services.realtime.secret' => null]);
        app(SetTaskAssigneesAction::class)->execute($this->makeTask(), [$this->otherEmployee->id], $this->manager);
        Http::assertNothingSent();
    }

    public function test_an_unreachable_service_never_breaks_the_request(): void
    {
        Http::fake(fn () => throw new ConnectionException('refused'));
        $this->withoutDefer();

        $n = NotificationService::notifyTaskAssigned($this->makeTask(), $this->employee->id, $this->manager);

        $this->assertTrue($n->exists);
    }

    public function test_bell_gets_a_notifications_channel_token(): void
    {
        $html = $this->actingAs($this->employee)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('channel=notifications', $html);
        $this->assertMatchesRegularExpression('/"wsToken":"[^"]+\.[^"]+\.[^"]+"/', $html);
    }
}
