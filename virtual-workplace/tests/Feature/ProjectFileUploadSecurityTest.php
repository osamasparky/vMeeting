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

    /**
     * The absolute server path of an upload is ~192 chars on the live host, past the 191-char
     * string column (Schema::defaultStringLength): MySQL refused the row ("Data too long for
     * column 'file_path'"). SQLite does not enforce the length, so the stored value is checked.
     */
    public function test_a_task_attachment_stores_a_short_relative_path_and_can_be_deleted(): void
    {
        $file = UploadedFile::fake()->image('WhatsApp Image 2026-10-18 at 2.05.55 PM.jpeg');

        $res = $this->actingAs($this->user)->postJson(route('tasks.attachments.store', $this->task), ['file' => $file])
            ->assertCreated();

        $attachment = \App\Domains\Projects\Models\TaskAttachment::findOrFail($res->json('attachment.id'));
        $this->assertStringStartsWith('uploads/tasks/'.$this->task->id.'/', $attachment->file_path);
        $this->assertLessThanOrEqual(191, strlen($attachment->file_path));
        $this->assertSame('WhatsApp Image 2026-10-18 at 2.05.55 PM.jpeg', $attachment->file_name);
        $this->assertFileExists(public_path($attachment->file_path));

        $this->actingAs($this->user)->deleteJson(route('tasks.attachments.destroy', [$this->task, $attachment]))->assertOk();
        $this->assertFileDoesNotExist(public_path($attachment->file_path));
    }

    public function test_a_project_file_stores_a_short_relative_path_and_can_be_deleted(): void
    {
        $file = UploadedFile::fake()->create('Client requirements specification.docx', 12, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->actingAs($this->user)->postJson(route('projects.files.store', $this->project), ['file' => $file])->assertCreated();

        $stored = $this->project->files()->latest()->firstOrFail();
        $this->assertStringStartsWith('uploads/projects/'.$this->project->id.'/', $stored->file_path);
        $this->assertLessThanOrEqual(191, strlen($stored->file_path));
        $this->assertFileExists(public_path($stored->file_path));

        $this->actingAs($this->user)->deleteJson(route('projects.files.destroy', [$this->project, $stored]))->assertOk();
        $this->assertFileDoesNotExist(public_path($stored->file_path));
    }

    /** Rows saved before the fix hold the absolute path; deleting them still removes the file. */
    public function test_an_older_attachment_with_an_absolute_path_is_still_deleted_from_disk(): void
    {
        $dir = public_path('uploads/tasks/'.$this->task->id);
        @mkdir($dir, 0755, true);
        $absolute = $dir.'/legacy.png';
        file_put_contents($absolute, 'x');
        $attachment = \App\Domains\Projects\Models\TaskAttachment::create([
            'organization_id' => $this->organization->id, 'task_id' => $this->task->id, 'user_id' => $this->user->id,
            'file_name' => 'legacy.png', 'file_path' => $absolute, 'file_url' => '/uploads/tasks/'.$this->task->id.'/legacy.png',
            'file_size' => 1, 'mime_type' => 'image/png',
        ]);

        $this->actingAs($this->user)->deleteJson(route('tasks.attachments.destroy', [$this->task, $attachment]))->assertOk();
        $this->assertFileDoesNotExist($absolute);
    }

    protected function tearDown(): void
    {
        foreach (['uploads/tasks/'.$this->task->id, 'uploads/projects/'.$this->project->id] as $dir) {
            \Illuminate\Support\Facades\File::deleteDirectory(public_path($dir));
        }
        parent::tearDown();
    }
}
