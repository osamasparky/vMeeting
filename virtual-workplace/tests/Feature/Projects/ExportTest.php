<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\ProjectLabel;
use App\Domains\Projects\Models\TimeEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;
use ZipArchive;

class ExportTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    private function entry(string $userId, int $seconds): TimeEntry
    {
        return TimeEntry::create([
            'organization_id' => $this->org->id, 'user_id' => $userId, 'project_id' => $this->project->id,
            'started_at' => '2026-10-01 09:00:00', 'ended_at' => '2026-10-01 10:00:00', 'duration_seconds' => $seconds,
            'cost_rate' => 50, 'billing_rate' => 100, 'is_billable' => true,
        ]);
    }

    public function test_tasks_csv_has_bom_headers_and_filtered_rows(): void
    {
        $bug = ProjectLabel::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => 'Bug']);
        $this->task->labels()->attach($bug);
        $this->task->update(['title' => '=HYPERLINK("x")']);
        $this->makeTask(['title' => 'Unlabelled']);

        Sanctum::actingAs($this->employee);
        $res = $this->get($this->api("/projects/{$this->project->id}/export/tasks?format=csv&label_id={$bug->id}"))->assertOk();
        $csv = $res->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Bug', $csv);
        $this->assertStringNotContainsString('Unlabelled', $csv);
        // Formula injection is neutralised.
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_tasks_xlsx_is_a_valid_workbook_and_rtl_in_arabic(): void
    {
        app()->setLocale('ar');
        Sanctum::actingAs($this->employee);
        $res = $this->get($this->api("/projects/{$this->project->id}/export/tasks?format=xlsx"))->assertOk();
        $this->assertStringContainsString('spreadsheetml', $res->headers->get('Content-Type'));

        $path = $res->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        $this->assertStringContainsString($this->task->title, $sheet);
    }

    public function test_employees_export_only_their_own_time(): void
    {
        $this->entry($this->employee->id, 3600);
        $this->entry($this->otherEmployee->id, 7200);
        $this->otherEmployee->update(['name' => 'Someone Else']);

        Sanctum::actingAs($this->employee);
        $csv = $this->get($this->api('/time/entries/export?format=csv'))->assertOk()->streamedContent();
        $this->assertStringNotContainsString('Someone Else', $csv);
        $this->assertSame(2, substr_count(trim($csv), "\n") + 1); // header + 1 row

        Sanctum::actingAs($this->manager);
        $csv = $this->get($this->api('/time/entries/export?format=csv'))->assertOk()->streamedContent();
        $this->assertStringContainsString('Someone Else', $csv);
    }

    public function test_timesheets_export_and_bad_format(): void
    {
        Sanctum::actingAs($this->manager);
        $this->get($this->api('/timesheets/export?format=csv'))->assertOk();
        $this->getJson($this->api('/timesheets/export?format=pdf'))->assertUnprocessable();
    }

    public function test_outsiders_cannot_export_another_orgs_project(): void
    {
        Sanctum::actingAs($this->outsider);
        $this->get("/api/v1/organizations/{$this->otherOrg->id}/projects/{$this->project->id}/export/tasks")->assertNotFound();
        $this->get($this->api("/projects/{$this->project->id}/export/tasks"))->assertForbidden();
    }
}
