<?php

namespace App\Traits;

use App\Domains\Administration\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Trait Auditable
 *
 * Automatically logs create/update/delete events for models.
 */
trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::logAudit($model, 'created');
        });

        static::updated(function ($model) {
            static::logAudit($model, 'updated', $model->getChanges());
        });

        static::deleted(function ($model) {
            static::logAudit($model, 'deleted');
        });
    }

    /**
     * Write an audit log entry.
     */
    protected static function logAudit($model, string $action, array $metadata = []): void
    {
        // Skip if no authenticated user (e.g., during seeding)
        if (!Auth::check()) {
            return;
        }

        $orgId = $model->organization_id ?? null;

        AuditLog::create([
            'organization_id' => $orgId,
            'actor_id' => Auth::id(),
            'action' => $action,
            'target_type' => get_class($model),
            'target_id' => $model->getKey(),
            'metadata' => $metadata,
        ]);
    }
}
