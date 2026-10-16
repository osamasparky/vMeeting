<?php

namespace Tests\Feature\Office;

use App\Domains\Workspace\Games\QuizTicket;
use App\Domains\Workspace\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

/**
 * The office quiz machine: everyone lists quizzes and starts one with a sealed
 * ticket; only the company owner writes, edits and deletes the company's own.
 */
class QuizTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.realtime.secret' => 'test-realtime-secret']);
        $this->buildProjectWorkspace();
    }

    private function payload(array $over = []): array
    {
        return $over + [
            'title' => 'Onboarding',
            'questions' => [
                ['text' => 'Where is the kitchen?', 'choices' => ['2nd floor', 'Roof', 'Basement'], 'correct_index' => 0, 'seconds' => 15],
                ['text' => 'Our founding year?', 'choices' => ['2019', '2021'], 'correct_index' => 1],
            ],
        ];
    }

    public function test_the_owner_writes_a_quiz_and_everyone_sees_it_next_to_the_built_ins(): void
    {
        $id = $this->actingAs($this->owner)->postJson('/games/quizzes', $this->payload())->assertCreated()->json('id');

        $list = $this->actingAs($this->employee)->getJson('/games/quizzes')->assertOk()->json('quizzes');
        $this->assertTrue(collect($list)->contains(fn ($q) => $q['id'] === 'builtin:general' && $q['builtIn']));
        $this->assertTrue(collect($list)->contains(fn ($q) => $q['id'] === $id && $q['count'] === 2));
        $this->assertArrayNotHasKey('questions', collect($list)->firstWhere('id', $id), 'players never get the answers');
    }

    public function test_a_ticket_seals_the_questions_for_the_realtime_server(): void
    {
        $id = $this->actingAs($this->owner)->postJson('/games/quizzes', $this->payload())->json('id');

        $res = $this->actingAs($this->employee)->postJson("/games/quizzes/{$id}/ticket")->assertOk();
        $this->assertStringNotContainsString('kitchen', $res->getContent(), 'the questions are not readable by the player');
        $data = QuizTicket::open($res->json('ticket'));
        $this->assertSame($this->org->id, $data['org']);
        $this->assertSame(['q' => 'Where is the kitchen?', 'c' => ['2nd floor', 'Roof', 'Basement'], 'a' => 0, 's' => 15], $data['questions'][0]);
        $this->assertSame(20, $data['questions'][1]['s'], 'default time per question');

        $builtIn = QuizTicket::open($this->actingAs($this->employee)->postJson('/games/quizzes/builtin:capitals/ticket')->assertOk()->json('ticket'));
        $this->assertCount(10, $builtIn['questions']);
    }

    public function test_another_companys_quiz_cannot_be_started(): void
    {
        $foreign = Quiz::create(['organization_id' => $this->otherOrg->id, 'title' => 'Theirs']);
        $foreign->questions()->create(['position' => 0, 'text' => 'x', 'choices' => ['a', 'b'], 'correct_index' => 0, 'seconds' => 20]);

        $this->actingAs($this->employee)->postJson("/games/quizzes/{$foreign->id}/ticket")->assertNotFound();
        $this->actingAs($this->employee)->postJson('/games/quizzes/builtin:nope/ticket')->assertNotFound();
    }

    public function test_only_the_owner_manages_quizzes(): void
    {
        $this->actingAs($this->employee)->postJson('/games/quizzes', $this->payload())->assertForbidden();
        $this->actingAs($this->manager)->postJson('/games/quizzes', $this->payload())->assertForbidden();

        $id = $this->actingAs($this->owner)->postJson('/games/quizzes', $this->payload())->json('id');
        $this->actingAs($this->employee)->putJson("/games/quizzes/{$id}", $this->payload(['title' => 'x']))->assertForbidden();
        $this->actingAs($this->employee)->deleteJson("/games/quizzes/{$id}")->assertForbidden();

        $this->actingAs($this->owner)->putJson("/games/quizzes/{$id}", $this->payload(['title' => 'Renamed', 'questions' => [
            ['text' => 'Only one now', 'choices' => ['a', 'b', 'c', 'd'], 'correct_index' => 3],
        ]]))->assertOk();
        $quiz = Quiz::with('questions')->find($id);
        $this->assertSame('Renamed', $quiz->title);
        $this->assertSame(['Only one now'], $quiz->questions->pluck('text')->all());

        $this->actingAs($this->owner)->deleteJson("/games/quizzes/{$id}")->assertOk();
        $this->assertNull(Quiz::find($id));
    }

    public function test_bad_questions_are_refused(): void
    {
        $this->actingAs($this->owner)->postJson('/games/quizzes', $this->payload(['questions' => [
            ['text' => 'Q', 'choices' => ['a', 'b'], 'correct_index' => 2],
        ]]))->assertStatus(422)->assertJsonValidationErrors('questions.0.correct_index');

        $this->actingAs($this->owner)->postJson('/games/quizzes', $this->payload(['questions' => [
            ['text' => 'Q', 'choices' => ['only one'], 'correct_index' => 0],
        ]]))->assertStatus(422);

        $this->actingAs($this->owner)->postJson('/games/quizzes', $this->payload(['questions' => []]))->assertStatus(422);
    }

    public function test_the_owner_sees_the_quizzes_tab(): void
    {
        $this->actingAs($this->owner)->get('/dashboard')->assertOk()->assertSee('id="tab-quizzes"', false);
        $this->actingAs($this->employee)->get('/dashboard')->assertOk()->assertDontSee('id="tab-quizzes"', false);
    }
}
