<?php

namespace App\Domains\Workspace\Controllers;

use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Games\BuiltInQuizzes;
use App\Domains\Workspace\Games\QuizTicket;
use App\Domains\Workspace\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Quizzes for the office quiz machine: everyone lists them and starts one
 * (receiving a sealed ticket for the realtime server); the company owner
 * writes, edits and deletes the company's own.
 */
class QuizController extends Controller
{
    private function membership(): OrganizationMember
    {
        $m = OrganizationMember::forUser(Auth::id())->where('status', 'active')->with('role')->first();
        abort_unless($m, 403);

        return $m;
    }

    private function canManage(OrganizationMember $m): bool
    {
        return $m->isCompanyOwner() || Auth::user()->isSuperAdmin();
    }

    /** Built-in quizzes first, then the company's; ?manage=1 adds the full questions for the owner's editor. */
    public function index(Request $request): JsonResponse
    {
        $m = $this->membership();
        $manage = $request->boolean('manage') && $this->canManage($m);

        $builtIn = collect(BuiltInQuizzes::all())->map(fn ($q, $key) => [
            'id' => "builtin:{$key}", 'title' => $q['title'], 'count' => count($q['questions']), 'builtIn' => true,
        ])->values();

        $own = Quiz::where('organization_id', $m->organization_id)->withCount('questions')
            ->when($manage, fn ($q) => $q->with('questions'))
            ->orderByDesc('updated_at')->get()
            ->map(fn (Quiz $q) => [
                'id' => $q->id, 'title' => $q->title, 'description' => $q->description, 'count' => $q->questions_count, 'builtIn' => false,
            ] + ($manage ? ['questions' => $q->questions->map(fn ($x) => [
                'text' => $x->text, 'choices' => $x->choices, 'correct_index' => $x->correct_index, 'seconds' => $x->seconds,
            ])->all()] : []));

        return response()->json(['quizzes' => $manage ? $own->values() : $builtIn->concat($own)->values(), 'canManage' => $this->canManage($m)]);
    }

    /** The sealed quiz the host hands to the realtime server when starting. */
    public function ticket(string $id): JsonResponse
    {
        $m = $this->membership();

        if (str_starts_with($id, 'builtin:')) {
            $quiz = BuiltInQuizzes::find(substr($id, 8));
        } else {
            $model = Quiz::where('organization_id', $m->organization_id)->whereKey($id)->with('questions')->first();
            $quiz = $model ? [
                'title' => $model->title,
                'questions' => $model->questions->map(fn ($x) => ['q' => $x->text, 'c' => $x->choices, 'a' => $x->correct_index, 's' => $x->seconds])->all(),
            ] : null;
        }
        abort_unless($quiz && count($quiz['questions']) > 0, 404);

        return response()->json(['ticket' => QuizTicket::seal($quiz, $m->organization_id), 'title' => $quiz['title'], 'count' => count($quiz['questions'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $m = $this->membership();
        abort_unless($this->canManage($m), 403);
        $data = $this->validated($request);

        $quiz = DB::transaction(function () use ($m, $data) {
            $quiz = Quiz::create(['organization_id' => $m->organization_id, 'title' => $data['title'], 'description' => $data['description'] ?? null, 'created_by' => Auth::id()]);
            $this->saveQuestions($quiz, $data['questions']);

            return $quiz;
        });

        return response()->json(['id' => $quiz->id], 201);
    }

    public function update(Request $request, string $quiz): JsonResponse
    {
        $m = $this->membership();
        abort_unless($this->canManage($m), 403);
        $model = Quiz::where('organization_id', $m->organization_id)->findOrFail($quiz);
        $data = $this->validated($request);

        DB::transaction(function () use ($model, $data) {
            $model->update(['title' => $data['title'], 'description' => $data['description'] ?? null]);
            $model->questions()->delete();
            $this->saveQuestions($model, $data['questions']);
        });

        return response()->json(['id' => $model->id]);
    }

    public function destroy(string $quiz): JsonResponse
    {
        $m = $this->membership();
        abort_unless($this->canManage($m), 403);
        Quiz::where('organization_id', $m->organization_id)->findOrFail($quiz)->delete();

        return response()->json(['deleted' => true]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:300'],
            'questions' => ['required', 'array', 'min:1', 'max:50'],
            'questions.*.text' => ['required', 'string', 'max:300'],
            'questions.*.choices' => ['required', 'array', 'min:2', 'max:4'],
            'questions.*.choices.*' => ['required', 'string', 'max:80'],
            'questions.*.correct_index' => ['required', 'integer', 'min:0'],
            'questions.*.seconds' => ['nullable', 'integer', 'between:5,60'],
        ]);
        foreach ($data['questions'] as $i => $q) {
            if ($q['correct_index'] >= count($q['choices'])) {
                throw ValidationException::withMessages(["questions.$i.correct_index" => __('games.quiz.pick_correct')]);
            }
        }

        return $data;
    }

    private function saveQuestions(Quiz $quiz, array $questions): void
    {
        foreach (array_values($questions) as $i => $q) {
            $quiz->questions()->create([
                'position' => $i,
                'text' => $q['text'],
                'choices' => array_values($q['choices']),
                'correct_index' => (int) $q['correct_index'],
                'seconds' => (int) ($q['seconds'] ?? BuiltInQuizzes::SECONDS),
            ]);
        }
    }
}
