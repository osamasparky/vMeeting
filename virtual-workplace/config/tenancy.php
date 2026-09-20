<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enforce Tenant Scope Globally
    |--------------------------------------------------------------------------
    |
    | See Architecture Audit §7/§15, ADR-002. Historically, every model using
    | App\Traits\BelongsToOrganization required each query to manually filter
    | by organization_id (via ->forOrganization() or a hand-written ->where()).
    | Nothing prevented a new or existing query from forgetting that filter.
    |
    | When this is true, BelongsToOrganization registers a global scope that
    | automatically restricts every query on a tenant-owned model to the
    | organization bound to the current request by EnsureOrganizationMember
    | middleware (via $request->get('current_organization')). Requests with
    | no such context (SuperAdmin routes, console commands, queue jobs) are
    | left unrestricted, since they have no single tenant to scope to.
    |
    | Defaults to false so enabling this is a deliberate, reversible,
    | per-environment decision rather than a behavior change bundled into a
    | code deploy — flip it on by environment once every domain's test suite
    | passes with it enabled, per the audit's rollout plan.
    |
    */

    'enforce_global_scope' => env('TENANCY_ENFORCE_GLOBAL_SCOPE', false),

];
