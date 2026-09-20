<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProjectFileUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Organization $organization;

    protected Project $project;

    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create(['email' => 'owner@tenant.test']);
        $this->organization = app(CreateOrganizationAction::class)->execute(
            ['name' => 'Upload Security Co'],
            $this->user
        );

        $this->project = Project::create([
            'organization_id' => $this->organization->id,
            'name' => 'Attachment Testing',
            'code' => 'ATT-01',
            'owner_id' => $this->user->id,
            'status' => 'active',
            'priority' => 'medium',
        ]);

        $this->task = Task::create([
            'organization_id' => $this->organization->id,
            'project_id' => $this->project->id,
            'title' => 'Upload a file',
            'task_number' => 1,
            'status' => 'backlog',
            'reporter_id' => $this->user->id,
        ]);
    }

    public function test_a_php_file_disguised_as_a_project_attachment_is_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]); ?>');

        $response = $this->actingAs($this->user)->post(
            route('projects.files.store', $this->project),
            ['file' => $file]
        );

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseMissing('project_files', ['file_name' => 'shell.php']);
    }

    public function test_a_php_file_disguised_as_a_task_attachment_is_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent('shell.phtml', '<?php system($_GET["c"]); ?>');

        $response = $this->actingAs($this->user)->postJson(
            route('tasks.attachments.store', $this->task),
            ['file' => $file]
        );

        $response->assertStatus(422);
        $this->assertDatabaseMissing('task_attachments', ['file_name' => 'shell.phtml']);
    }

    public function test_an_svg_with_an_embedded_script_is_rejected_as_a_project_attachment(): void
    {
        $maliciousSvg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert("XSS")</script></svg>';
        $file = UploadedFile::fake()->createWithContent('logo.svg', $maliciousSvg);

        $response = $this->actingAs($this->user)->post(
            route('projects.files.store', $this->project),
            ['file' => $file]
        );

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseMissing('project_files', ['file_name' => 'logo.svg']);
    }

    public function test_an_ordinary_document_is_still_accepted_as_a_project_attachment(): void
    {
        $file = UploadedFile::fake()->create('spec.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->user)->post(
            route('projects.files.store', $this->project),
            ['file' => $file]
        );

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('project_files', [
            'project_id' => $this->project->id,
            'file_name' => 'spec.pdf',
        ]);
    }

    public function test_the_project_upload_directory_gets_an_execution_denying_htaccess(): void
    {
        $file = UploadedFile::fake()->create('notes.txt', 10, 'text/plain');

        $this->actingAs($this->user)->post(
            route('projects.files.store', $this->project),
            ['file' => $file]
        );

        $this->assertFileExists(public_path('uploads/projects/'.$this->project->id.'/.htaccess'));
    }
}
