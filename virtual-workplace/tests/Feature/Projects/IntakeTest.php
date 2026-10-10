<?php

namespace Tests\Feature\Projects;

use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Projects\Models\IntakeRequest;
use App\Domains\Projects\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class IntakeTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->buildProjectWorkspace();
    }

    private function enable(): string
    {
        Sanctum::actingAs($this->manager);
        $url = $this->patchJson($this->api("/projects/{$this->project->id}/intake/settings"), ['enabled' => true])->assertOk()->json('settings.url');

        return basename(parse_url($url, PHP_URL_PATH));
    }

    public function test_public_form_only_works_while_enabled(): void
    {
        $this->get('/intake/nope')->assertNotFound();
        $token = $this->enable();
        $this->get("/intake/{$token}")->assertOk()->assertSee($this->project->name);

        $this->patchJson($this->api("/projects/{$this->project->id}/intake/settings"), ['enabled' => false]);
        $this->get("/intake/{$token}")->assertNotFound();
    }

    public function test_a_guest_submits_a_request_and_the_manager_is_told(): void
    {
        $token = $this->enable();
        auth()->forgetGuards();

        $this->post("/intake/{$token}", ['requester_name' => 'Client', 'requester_email' => 'c@example.com', 'title' => 'Add dark mode', 'description' => 'Please'])
            ->assertRedirect("/intake/{$token}");

        $this->assertDatabaseHas('intake_requests', ['project_id' => $this->project->id, 'title' => 'Add dark mode', 'status' => 'pending']);
        $this->assertSame(1, WorkplaceNotification::where('user_id', $this->manager->id)->where('type', 'task_intake')->count());
    }

    public function test_honeypot_submissions_are_dropped(): void
    {
        $token = $this->enable();
        $this->post("/intake/{$token}", ['requester_name' => 'Bot', 'title' => 'Spam', 'website' => 'http://spam'])->assertRedirect();
        $this->assertDatabaseCount('intake_requests', 0);
    }

    public function test_regenerating_the_link_kills_the_old_one(): void
    {
        $old = $this->enable();
        $new = basename(parse_url($this->patchJson($this->api("/projects/{$this->project->id}/intake/settings"), ['regenerate' => true])->json('settings.url'), PHP_URL_PATH));

        $this->assertNotSame($old, $new);
        $this->get("/intake/{$old}")->assertNotFound();
        $this->get("/intake/{$new}")->assertOk();
    }

    private function request(array $attrs = []): IntakeRequest
    {
        return IntakeRequest::create(array_merge([
            'organization_id' => $this->org->id, 'project_id' => $this->project->id,
            'title' => 'Export to PDF', 'description' => 'Need it', 'requester_name' => 'Client', 'requester_email' => 'c@example.com',
        ], $attrs));
    }

    public function test_accepting_creates_a_task_with_the_requester_credited(): void
    {
        $r = $this->request();

        Sanctum::actingAs($this->employee);
        $taskId = $this->postJson($this->api("/projects/{$this->project->id}/intake/{$r->id}/accept"), ['priority' => 'high'])->assertOk()->json('task.id');

        $task = Task::find($taskId);
        $this->assertSame('Export to PDF', $task->title);
        $this->assertSame('high', $task->priority);
        $this->assertStringContainsString('c@example.com', $task->description);
        $this->assertSame('accepted', $r->fresh()->status);

        // Handled requests can't be handled twice.
        $this->postJson($this->api("/projects/{$this->project->id}/intake/{$r->id}/decline"))->assertUnprocessable();
    }

    public function test_decline_snooze_and_duplicate(): void
    {
        $a = $this->request();
        $b = $this->request(['title' => 'B']);
        $c = $this->request(['title' => 'C']);

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/projects/{$this->project->id}/intake/{$a->id}/decline"), ['reason' => 'Out of scope'])->assertOk();
        $this->postJson($this->api("/projects/{$this->project->id}/intake/{$b->id}/snooze"), ['until' => now()->addDays(3)->toDateString()])->assertOk();
        $this->postJson($this->api("/projects/{$this->project->id}/intake/{$c->id}/duplicate"), ['task_id' => $this->task->id])->assertOk();

        $this->getJson($this->api("/projects/{$this->project->id}/intake"))->assertJsonPath('open_count', 0);

        // A snoozed request comes back when its day arrives.
        $this->travel(4)->days();
        $this->getJson($this->api("/projects/{$this->project->id}/intake"))->assertJsonPath('open_count', 1)->assertJsonPath('requests.0.id', $b->id);
    }

    public function test_triage_is_scoped_and_permissioned(): void
    {
        $r = $this->request();

        Sanctum::actingAs($this->employee);
        $this->patchJson($this->api("/projects/{$this->project->id}/intake/settings"), ['enabled' => true])->assertForbidden();
        $this->postJson($this->api("/projects/{$this->project->id}/intake/{$r->id}/duplicate"), ['task_id' => Task::create(['organization_id' => $this->otherOrg->id, 'project_id' => $this->foreignProject->id, 'title' => 'x', 'reporter_id' => $this->outsider->id])->id])
            ->assertUnprocessable();

        Sanctum::actingAs($this->outsider);
        $this->postJson("/api/v1/organizations/{$this->otherOrg->id}/projects/{$this->project->id}/intake/{$r->id}/accept")->assertNotFound();
    }
}
