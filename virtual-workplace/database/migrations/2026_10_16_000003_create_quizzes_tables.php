<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Company quizzes for the office quiz machine (Kahoot-style). A quiz has
 * questions with 2–4 choices, one correct, and a time limit each. Built-in
 * quizzes ship in code (App\Domains\Workspace\Games\BuiltInQuizzes) and are
 * not stored here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('description', 300)->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('organization_id');
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('text', 300);
            $table->json('choices');
            $table->unsignedTinyInteger('correct_index');
            $table->unsignedTinyInteger('seconds')->default(20);
            $table->timestamps();
            $table->index(['quiz_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
    }
};
