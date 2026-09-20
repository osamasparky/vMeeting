<?php

namespace App\Domains\Tenancy\Actions;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\People\Models\UserProfile;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Support\Facades\Hash;

/**
 * Creates or invites a team member: finds-or-creates the User, creates or
 * updates their OrganizationMember, syncs allowed offices/rooms, and
 * updates their profile — then logs it. Extracted from
 * OrganizationSettingsController::storeMember(). See Architecture Audit §8/§15.
 */
class CreateOrInviteOrganizationMemberAction
{
    /**
     * @return array{user: User, member: OrganizationMember}
     */
    public function execute(
        OrganizationMember $creatorMembership,
        User $creator,
        array $validated,
        Role $targetRole,
        ?string $ip,
        ?string $userAgent
    ): array {
        $targetUser = User::where('email', $validated['email'])->first();
        $plainPassword = ($validated['password'] ?? null) ?: 'Password@1234';

        if (! $targetUser) {
            $targetUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($plainPassword),
                'email_verified_at' => now(),
            ]);
        } else {
            $targetUser->name = $validated['name'];
            if (! empty($validated['password'])) {
                $targetUser->password = Hash::make($validated['password']);
            }
            $targetUser->save();
        }

        $memberStatus = $validated['status'] ?? 'active';
        $member = OrganizationMember::updateOrCreate(
            [
                'organization_id' => $creatorMembership->organization_id,
                'user_id' => $targetUser->id,
            ],
            [
                'role_id' => $validated['role_id'],
                'status' => $memberStatus,
            ]
        );

        if (isset($validated['allowed_offices'])) {
            $member->offices()->sync($validated['allowed_offices']);
        }
        if (isset($validated['allowed_rooms'])) {
            $member->rooms()->sync($validated['allowed_rooms']);
        }

        $profile = UserProfile::firstOrNew([
            'user_id' => $targetUser->id,
            'organization_id' => $creatorMembership->organization_id,
        ]);
        $profile->department_id = $validated['department_id'] ?? null;
        $profile->team_id = $validated['team_id'] ?? null;
        $profile->job_title = $validated['job_title'] ?? null;
        $profile->save();

        AuditLog::create([
            'organization_id' => $creatorMembership->organization_id,
            'user_id' => $creator->id,
            'action' => 'member.created',
            'target_type' => 'user',
            'target_id' => $targetUser->id,
            'metadata' => [
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'role' => $targetRole->name,
                'status' => $memberStatus,
            ],
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        return ['user' => $targetUser, 'member' => $member];
    }
}
