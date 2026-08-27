<?php

namespace App\Domains\Tenancy\Actions;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;

class InviteMemberAction
{
    /**
     * Invite a user to an organization. If user doesn't exist by email, create an invited membership placeholder.
     */
    public function execute(Organization $organization, array $data): OrganizationMember
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            // Create a user with specified password or temporary random password
            $user = User::create([
                'name' => $data['name'] ?? 'Invited User',
                'email' => $data['email'],
                'password' => bcrypt(! empty($data['password']) ? $data['password'] : str()->random(32)),
            ]);
        } elseif (! empty($data['password'])) {
            $user->password = bcrypt($data['password']);
            if (! empty($data['name'])) {
                $user->name = $data['name'];
            }
            $user->save();
        }

        // Check if already a member
        $existing = OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Check seat limit
        if ($organization->hasReachedSeatLimit()) {
            throw new \Exception('Organization has reached its seat limit. Please upgrade your plan.');
        }

        // Default role is Employee unless specified
        $role = Role::where('slug', $data['role'] ?? 'employee')
            ->whereNull('organization_id')
            ->first();

        return OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }
}
