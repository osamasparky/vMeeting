<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\ProjectDocument;
use App\Support\HtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class DocumentEditorTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    private function doc(): ProjectDocument
    {
        return ProjectDocument::create([
            'organization_id' => $this->org->id, 'project_id' => $this->project->id, 'created_by' => $this->owner->id,
            'title' => 'Spec', 'content' => '<p>First</p>',
        ]);
    }

    public function test_sanitizer_keeps_formatting_and_strips_danger(): void
    {
        $clean = HtmlSanitizer::clean(
            '<h2 onclick="x()">Title</h2><p style="color:red">Hi <strong>there</strong><script>alert(1)</script></p>'
            .'<a href="javascript:alert(1)">bad</a><a href="https://ok.example">good</a><img src=x onerror=alert(1)>'
            .'<span>kept text</span><iframe src="https://evil"></iframe><p dir="rtl">مرحبا</p>'
        );

        $this->assertStringContainsString('<h2>Title</h2>', $clean);
        $this->assertStringContainsString('<strong>there</strong>', $clean);
        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('style=', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('<img', $clean);
        $this->assertStringNotContainsString('iframe', $clean);
        $this->assertStringContainsString('href="https://ok.example"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $clean);
        $this->assertStringContainsString('kept text', $clean);
        $this->assertStringContainsString('<p dir="rtl">مرحبا</p>', $clean);
    }

    public function test_legacy_plain_text_renders_as_paragraphs(): void
    {
        $this->assertSame("<p dir=\"auto\">Line one<br>\nline two</p><p dir=\"auto\">&lt;b&gt;</p>", HtmlSanitizer::forDisplay("Line one\nline two\n\n<b>"));
    }

    public function test_saving_keeps_the_previous_version_and_sanitizes(): void
    {
        $doc = $this->doc();

        Sanctum::actingAs($this->manager);
        $this->putJson($this->api("/projects/{$this->project->id}/docs/{$doc->id}"), [
            'title' => 'Spec v2', 'content' => '<p>Second<script>x</script></p>', 'base_version' => 1,
        ])->assertOk()->assertJsonPath('document.version', 2)->assertJsonPath('document.html', '<p>Second</p>');

        $versions = $this->getJson($this->api("/projects/{$this->project->id}/docs/{$doc->id}/versions"))->assertOk()->json('versions');
        $this->assertCount(1, $versions);
        $this->assertSame(1, $versions[0]['version']);

        $old = $this->getJson($this->api("/projects/{$this->project->id}/docs/{$doc->id}/versions/{$versions[0]['id']}"))->json('version');
        $this->assertSame('Spec', $old['title']);
        $this->assertSame('<p>First</p>', $old['html']);
    }

    public function test_unchanged_saves_do_not_create_versions(): void
    {
        $doc = $this->doc();

        Sanctum::actingAs($this->manager);
        $this->putJson($this->api("/projects/{$this->project->id}/docs/{$doc->id}"), ['title' => 'Spec', 'content' => '<p>First</p>'])
            ->assertOk()->assertJsonPath('document.version', 1);
        $this->assertSame(0, $doc->versions()->count());
    }

    public function test_stale_saves_are_rejected_instead_of_overwriting(): void
    {
        $doc = $this->doc();

        Sanctum::actingAs($this->manager);
        $this->putJson($this->api("/projects/{$this->project->id}/docs/{$doc->id}"), ['title' => 'A', 'content' => '<p>A</p>', 'base_version' => 1])->assertOk();
        $this->putJson($this->api("/projects/{$this->project->id}/docs/{$doc->id}"), ['title' => 'B', 'content' => '<p>B</p>', 'base_version' => 1])
            ->assertStatus(409)->assertJsonPath('document.title', 'A');
    }

    public function test_restore_creates_a_new_version(): void
    {
        $doc = $this->doc();

        Sanctum::actingAs($this->manager);
        $this->putJson($this->api("/projects/{$this->project->id}/docs/{$doc->id}"), ['title' => 'Spec', 'content' => '<p>Second</p>']);
        $v1 = $doc->versions()->first();

        $this->postJson($this->api("/projects/{$this->project->id}/docs/{$doc->id}/versions/{$v1->id}/restore"))->assertOk()
            ->assertJsonPath('document.version', 3)->assertJsonPath('document.html', '<p>First</p>');
        $this->assertSame(2, $doc->versions()->count());
    }

    public function test_employees_read_but_cannot_edit_or_restore(): void
    {
        $doc = $this->doc();

        Sanctum::actingAs($this->employee);
        $this->getJson($this->api("/projects/{$this->project->id}/docs/{$doc->id}"))->assertOk();
        $this->putJson($this->api("/projects/{$this->project->id}/docs/{$doc->id}"), ['title' => 'x'])->assertForbidden();
    }

    public function test_creating_a_doc_without_icon_works(): void
    {
        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/projects/{$this->project->id}/docs"), ['title' => 'New', 'icon' => '📄', 'content' => '<p onclick="x">Hi</p>'])
            ->assertCreated();
        $this->assertSame('<p>Hi</p>', ProjectDocument::latest('id')->first()->content);
    }
}
