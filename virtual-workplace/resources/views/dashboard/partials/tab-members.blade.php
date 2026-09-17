<div id="tab-members" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 class="page-title" style="font-size: 22px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 24px; color: var(--ula-highlight-default);">group</span>
                <span>{{ __('Team Members & Roles') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: 13px; color: var(--ula-text-secondary);">{{ __('Manage organization membership, departments, teams, and security roles.') }}</p>
        </div>
        @if($membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
        <x-btn variant="primary" size="md" onclick="openInviteModal()" icon="person_add">
            {{ __('Invite Member') }}
        </x-btn>
        @endif
    </div>

    <div class="card" style="border-radius: var(--ula-radius-lg); overflow: hidden; padding: 0;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--ula-surface-card);">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-highlight-default);">badge</span>
                <span>{{ __('Workspace Roster') }} ({{ $members->count() }})</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Member') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Department & Team') }}</th>
                        <th>{{ __('Job Title') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Status') }}</th>
                        @if($membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
                        <th>{{ __('Actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $m)
                        @php
                            $profile = $m->user->profiles->where('organization_id', $organization->id)->first();
                            $memberDept = $departments->where('id', $profile?->department_id)->first();
                            $memberTeam = $teams->where('id', $profile?->team_id)->first();
                        @endphp
                        <tr>
                            <td>
                                <div onclick="openMemberProfileModal('{{ $m->id }}')" style="display: flex; align-items: center; gap: 10px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'" title="{{ __('Click to view member profile, tasks & work time') }}">
                                    <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--ula-palm-900); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: white; box-shadow: var(--ula-shadow-sm); flex-shrink: 0;">
                                        {{ strtoupper(substr($m->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div style="color: var(--ula-palm-900); font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 4px;">
                                            <span>{{ $m->user->name }}</span>
                                            <span class="material-symbols-rounded" style="font-size: 13px; opacity: 0.6;">visibility</span>
                                        </div>
                                        @if($m->user->nickname)
                                            <div style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">{{ $m->user->nickname }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 12px; color: var(--ula-text-secondary); font-family: 'IBM Plex Mono', monospace;">{{ $m->user->email }}</td>
                            <td>
                                @if($memberDept)
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span class="nav-badge-pill" style="font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-rounded" style="font-size: 13px;">corporate_fare</span>
                                            <span>{{ $memberDept->name }}</span>
                                        </span>
                                        @if($memberTeam)
                                            <span style="font-size: 10px; color: var(--ula-text-muted); font-weight: 600; display: inline-flex; align-items: center; gap: 3px; margin-inline-start: 8px;">
                                                <span class="material-symbols-rounded" style="font-size: 11px;">groups</span>
                                                <span>{{ $memberTeam->name }}</span>
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span style="color: var(--ula-text-muted); font-size: 11px; font-style: italic;">— {{ __('Not Assigned') }} —</span>
                                @endif
                            </td>
                            <td style="font-size: 12px; font-weight: 500; color: var(--ula-text-secondary);">
                                {{ $profile?->job_title ?? '—' }}
                            </td>
                            <td>
                                <span class="nav-badge-pill" style="background: rgba(60, 107, 76, 0.12); color: var(--ula-palm-700); font-weight: 600;">{{ $m->role->name ?? __('Company Admin') }}</span>
                            </td>
                            <td>
                                @if($m->status === 'active')
                                    <x-badge variant="live" dot="true">{{ __('Active') }}</x-badge>
                                @elseif($m->status === 'invited')
                                    <x-badge variant="scheduled" icon="mail">{{ __('Invited / Pending') }}</x-badge>
                                @elseif($m->status === 'suspended')
                                    <x-badge variant="attention">{{ __('Suspended') }}</x-badge>
                                @else
                                    <x-badge variant="default">{{ ucfirst($m->status) }}</x-badge>
                                @endif
                            </td>
                            @if($membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
                            <td>
                                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                    @if($m->user_id !== $user->id)
                                        <form method="POST" action="{{ route('organization.members.impersonate', $m->id) }}" style="display: inline;" onsubmit="return confirm('{{ __('Are you sure you want to log in as :name?', ['name' => addslashes($m->user->name)]) }}');">
                                            @csrf
                                            <button type="submit" class="nx-btn nx-btn-secondary nx-btn-sm" style="padding: 5px 8px; font-size: 11px;" title="{{ __('Log in as this member') }}">
                                                <span class="material-symbols-rounded" style="font-size: 13px;">switch_account</span>
                                                <span>{{ __('Login As') }}</span>
                                            </button>
                                        </form>
                                    @endif
                                    <button onclick="openEditMemberModal('{{ $m->id }}', '{{ addslashes($m->user->name) }}', '{{ addslashes($m->user->email) }}', '{{ $profile?->department_id }}', '{{ $profile?->team_id }}', '{{ $m->role_id }}', '{{ addslashes($profile?->job_title ?? '') }}', '{{ $m->status }}')" class="nx-btn nx-btn-secondary nx-btn-sm" style="padding: 5px 8px; font-size: 11px;" title="{{ __('Edit Member') }}">
                                        <span class="material-symbols-rounded" style="font-size: 13px;">edit</span>
                                        <span>{{ __('Edit') }}</span>
                                    </button>
                                    <button onclick="openChangeMemberPasswordModal('{{ $m->id }}', '{{ addslashes($m->user->name) }}')" class="nx-btn nx-btn-outline nx-btn-sm" style="padding: 5px 8px; font-size: 11px;" title="{{ __('Change Password') }}">
                                        <span class="material-symbols-rounded" style="font-size: 13px;">key</span>
                                        <span>{{ __('Password') }}</span>
                                    </button>
                                    @if($m->user_id !== $user->id)
                                        <form method="POST" action="{{ route('organization.members.delete', $m->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this member from your company?') }}');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="nx-btn nx-btn-danger nx-btn-sm" style="padding: 5px 7px;" title="{{ __('Remove Member') }}">
                                                <span class="material-symbols-rounded" style="font-size: 13px;">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>