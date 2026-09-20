<?php

namespace App\Traits;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait BelongsToOrganization
 *
 * Provides organization relationship, creation context assignment, an
 * explicit scoping helper (scopeForOrganization), and — when
 * config('tenancy.enforce_global_scope') is enabled — an automatic
 * TenantScope global scope. See Architecture Audit §7/§15, ADR-002.
 */
trait BelongsToOrganization
{
    /**
     * Boot the trait — auto-set organization_id upon model creation from
     * request route if present, and register the (flag-gated) tenant scope.
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

        static::addGlobalScope(new TenantScope);
    }

    /**
     * Relationship to the owning organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope query to a specific organization. Still works exactly as before,
     * regardless of whether the global scope is enabled.
     */
    public function scopeForOrganization(Builder $query, string $organizationId): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('organization_id'), $organizationId);
    }

    /**
     * Explicit, named escape hatch: run a query without the automatic
     * TenantScope, for the rare legitimate case where a query inside an
     * org-scoped request genuinely needs to see other tenants' rows (e.g.
     * checking a slug is globally unique). A no-op when the global scope
     * isn't registered (i.e. the feature flag is off).
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
