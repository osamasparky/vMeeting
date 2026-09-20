<?php

namespace App\Domains\Tenancy\Scopes;

use App\Domains\Tenancy\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts a tenant-owned model's queries to the organization bound to the
 * current request, when one is bound and the feature is enabled. See
 * config/tenancy.php and Architecture Audit §7/§15, ADR-002.
 *
 * Deliberately a no-op when either:
 *  - config('tenancy.enforce_global_scope') is false (the default), or
 *  - no "current_organization" is bound to the request (SuperAdmin routes,
 *    console commands, queue jobs — none of which have one tenant to scope
 *    to, and none of which should be silently restricted to zero rows).
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! config('tenancy.enforce_global_scope', false)) {
            return;
        }

        $organization = $this->currentOrganizationId();
        if ($organization !== null) {
            $builder->where($model->qualifyColumn('organization_id'), $organization);
        }
    }

    private function currentOrganizationId(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $current = request()->get('current_organization');
        if ($current instanceof Organization) {
            return $current->id;
        }

        return is_string($current) ? $current : null;
    }
}
