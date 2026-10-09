<?php

namespace App\Domains\Projects\Requests\Concerns;

use App\Domains\Tenancy\Models\Organization;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Validation rules that only accept records from the organization in the
 * route. A bare `exists:projects,id` lets a member point a task, timer or
 * assignment at another tenant's records.
 */
trait ScopesToOrganization
{
    protected function organizationId(): ?string
    {
        $organization = $this->route('organization');

        return $organization instanceof Organization ? $organization->id : $organization;
    }

    /** A row in $table owned by the current organization. */
    protected function existsInOrganization(string $table): Exists
    {
        return Rule::exists($table, 'id')->where('organization_id', $this->organizationId());
    }

    /** A user who belongs to the current organization. */
    protected function organizationMember(): Exists
    {
        return Rule::exists('organization_members', 'user_id')->where('organization_id', $this->organizationId());
    }
}
