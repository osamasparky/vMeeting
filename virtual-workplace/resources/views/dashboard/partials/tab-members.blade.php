<div id="tab-members" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: gap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">👥 {{ __('Team Members & Roles') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Manage organization membership, departments, teams, and security roles.') }}</p>
                </div>
                @if($membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
                <button onclick="openInviteModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                    <span>+</span> {{ __('Invite Member') }}
                </button>
                @endif
            </div>

            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">👥 {{ __('Workspace Roster') }} ({{ $members->count() }})</h3>
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
                                            <div style="width: 34px; height: 34px; border-radius: 10px; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; color: white; box-shadow: var(--shadow-soft-3d); flex-shrink: 0;">
                                                {{ strtoupper(substr($m->user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div style="color: var(--brand-forest); font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 4px;">
                                                    <span>{{ $m->user->name }}</span>
                                                    <span style="font-size: 10px; opacity: 0.7;">👁️</span>
                                                </div>
                                                @if($m->user->nickname)
                                                    <div style="font-size: 10px; color: var(--text-muted); font-family: monospace;">{{ $m->user->nickname }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-muted);">{{ $m->user->email }}</td>
                                    <td>
                                        @if($memberDept)
                                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                                <span class="nav-badge-pill" style="font-size: 11px;">🏛️ {{ $memberDept->name }}</span>
                                                @if($memberTeam)
                                                    <span style="font-size: 10px; color: var(--text-muted); font-weight: 700;">↳ 👥 {{ $memberTeam->name }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 11px; font-style: italic;">— {{ __('Not Assigned') }} —</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 12px; font-weight: 600; color: var(--text-secondary);">
                                        {{ $profile?->job_title ?? '—' }}
                                    </td>
                                    <td>
                                        <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">{{ $m->role->name ?? __('Company Admin') }}</span>
                                    </td>
                                    <td>
                                        @if($m->status === 'active')
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F; font-weight: 800;">🟢 {{ __('Active') }}</span>
                                        @elseif($m->status === 'invited')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.2); color: #D6A23A; font-weight: 800;">✉️ {{ __('Invited / Pending') }}</span>
                                        @elseif($m->status === 'suspended')
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; font-weight: 800;">🔴 {{ __('Suspended') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($m->status) }}</span>
                                        @endif
                                    </td>
                                    @if($membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
                                    <td>
                                        <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                            @if($m->user_id !== $user->id)
                                                <form method="POST" action="{{ route('organization.members.impersonate', $m->id) }}" style="display: inline;" onsubmit="return confirm('{{ __('Are you sure you want to log in as :name?', ['name' => addslashes($m->user->name)]) }}');">
                                                    @csrf
                                                    <button type="submit" class="tactile-btn" style="background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.3); padding: 5px 10px; font-size: 11px; font-weight: 800;" title="{{ __('Log in as this member (تسجيل الدخول كعضو)') }}">
                                                        <span>👤</span> {{ __('Login As') }}
                                                    </button>
                                                </form>
                                            @endif
                                            <button onclick="openEditMemberModal('{{ $m->id }}', '{{ addslashes($m->user->name) }}', '{{ addslashes($m->user->email) }}', '{{ $profile?->department_id }}', '{{ $profile?->team_id }}', '{{ $m->role_id }}', '{{ addslashes($profile?->job_title ?? '') }}', '{{ $m->status }}')" class="tactile-btn btn-secondary" style="padding: 5px 10px; font-size: 11px; font-weight: 800;" title="{{ __('Edit Member Data (تعديل البيانات)') }}">
                                                <span>✏️</span> {{ __('Edit') }}
                                            </button>
                                            <button onclick="openChangeMemberPasswordModal('{{ $m->id }}', '{{ addslashes($m->user->name) }}')" class="tactile-btn" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; border: 1px solid rgba(214, 162, 58, 0.3); padding: 5px 10px; font-size: 11px; font-weight: 800;" title="{{ __('Change Password (تغيير كلمة المرور)') }}">
                                                <span>🔑</span> {{ __('Password') }}
                                            </button>
                                            @if($m->user_id !== $user->id)
                                                <form method="POST" action="{{ route('organization.members.delete', $m->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this member from your company?') }}');" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 5px 8px; font-size: 11px;" title="{{ __('Remove Member (حذف)') }}">
                                                        🗑️
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