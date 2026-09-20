<?php

namespace App\Domains\Tenancy\Actions;

use App\Domains\Identity\Actions\RegisterUserAction;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\Plan;

/**
 * Orchestrates the web signup flow: create the user, resolve the plan they
 * picked (if any), and create their organization on it. Used only by the
 * combined web registration form — the API register/create-organization
 * endpoints stay separate. See Architecture Audit §8/§15.
 */
class RegisterUserWithOrganizationAction
{
    public function __construct(
        private readonly RegisterUserAction $registerUser,
        private readonly CreateOrganizationAction $createOrganization,
    ) {}

    /**
     * @return array{user: User, organization: Organization, selectedPlan: ?Plan, isPaidPlan: bool}
     */
    public function execute(array $validated): array
    {
        $user = $this->registerUser->execute($validated);

        $selectedPlan = null;
        if (! empty($validated['plan_id'])) {
            $selectedPlan = Plan::find($validated['plan_id']);
        } elseif (! empty($validated['plan_slug'])) {
            $selectedPlan = Plan::where('slug', $validated['plan_slug'])->first();
        }

        $freePlan = Plan::where('slug', 'free')->first() ?? Plan::where('price', 0)->first() ?? Plan::first();
        $isPaidPlan = $selectedPlan && (float) $selectedPlan->price > 0;

        $organization = $this->createOrganization->execute(
            [
                'name' => $validated['organization_name'],
                'plan_id' => $isPaidPlan ? $freePlan?->id : ($selectedPlan?->id ?? $freePlan?->id),
            ],
            $user
        );

        return [
            'user' => $user,
            'organization' => $organization,
            'selectedPlan' => $selectedPlan,
            'isPaidPlan' => $isPaidPlan,
        ];
    }
}
