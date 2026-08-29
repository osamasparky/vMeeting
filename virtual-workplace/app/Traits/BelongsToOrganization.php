<?php

namespace App\Traits;

use App\Domains\Tenancy\Models\Organization;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait BelongsToOrganization
 *
 * Provides organization relationship, creation context assignment,
 * and explicit scoping helper (scopeForOrganization) for multi-tenant models.
 */
trait BelongsToOrganization
{
    /**
     * Boot the trait — auto-set organization_id upon model creation from request route if present.
     */
    protected static function bootBelongsToOrganization(): void
    {
        // Auto-set organization_id on creation if available in request context
        static::creating(function ($model) {
            if (empty($model->organization_id) && request()->route('organization')) {
                $org = request()->route('organization');
                $model->organization_id = $org instanceof Organization ? $org->id : $org;
            }
        });
    }

    /**
     * Relationship to the owning organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope query to a specific organization.
     */
    public function scopeForOrganization(Builder $query, string $organizationId): Builder
    {
        return $query->where('organization_id', $organizationId);
    }
}
