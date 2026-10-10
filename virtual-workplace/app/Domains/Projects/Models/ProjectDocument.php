<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProjectDocument extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'project_id',
        'created_by',
        'parent_document_id',
        'title',
        'slug',
        'content',
        'icon',
        'is_pinned',
        'version',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'version' => 'integer',
    ];

    protected $attributes = [
        'version' => 1,
        'is_pinned' => false,
        'icon' => '📄',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($doc) {
            if (empty($doc->slug)) {
                $doc->slug = Str::slug($doc->title).'-'.Str::random(5);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProjectDocumentVersion::class, 'document_id')->orderByDesc('version');
    }

    /**
     * Save new text, keeping the current one as a version first. No-op when
     * nothing changed, so repeated saves don't flood the history.
     */
    public function saveRevision(string $title, string $content, ?string $userId): bool
    {
        if ($title === $this->title && $content === (string) $this->content) {
            return false;
        }

        $this->versions()->create([
            'version' => $this->version,
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => $this->last_edited_by ?? $this->created_by,
            'created_at' => $this->updated_at ?? now(),
        ]);

        $this->forceFill([
            'title' => $title,
            'content' => $content,
            'version' => $this->version + 1,
            'last_edited_by' => $userId,
        ])->save();

        return true;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class, 'parent_document_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProjectDocument::class, 'parent_document_id');
    }
}
