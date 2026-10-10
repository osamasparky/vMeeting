<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A previous text of a project document. */
class ProjectDocumentVersion extends Model
{
    public $timestamps = false;

    protected $fillable = ['document_id', 'version', 'title', 'content', 'user_id', 'created_at'];

    protected $casts = ['version' => 'integer', 'created_at' => 'datetime'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class, 'document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
