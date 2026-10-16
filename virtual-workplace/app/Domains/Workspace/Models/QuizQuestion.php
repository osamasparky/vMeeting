<?php

namespace App\Domains\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    protected $fillable = ['quiz_id', 'position', 'text', 'choices', 'correct_index', 'seconds'];

    protected $casts = [
        'choices' => 'array',
        'correct_index' => 'integer',
        'seconds' => 'integer',
        'position' => 'integer',
    ];

    /** @return BelongsTo<Quiz, $this> */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
