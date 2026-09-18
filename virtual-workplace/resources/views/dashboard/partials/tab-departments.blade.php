<div id="tab-departments" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--ula-space-7); flex-wrap: wrap; gap: var(--ula-space-5);">
        <div>
            <h1 class="page-title" style="font-size: var(--ula-size-h3); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin-bottom: var(--ula-space-2); display: flex; align-items: center; gap: var(--ula-space-3);">
                <span class="material-symbols-rounded" style="font-size: 28px; color: var(--ula-accent-default);">apartment</span>
                <span>{{ __('Departments & Teams') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: var(--ula-size-sm); color: var(--ula-text-secondary);">{{ __('Organize your organization staff, distribute members across departments, and manage sub-teams.') }}</p>
        </div>
        <button onclick="openDepartmentModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: var(--ula-size-sm); display: inline-flex; align-items: center; gap: var(--ula-space-3);">
            <span class="material-symbols-rounded" style="font-size: 18px;">add</span>
            <span>{{ __('New Department') }}</span>
        </button>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: var(--ula-space-6);">
        @forelse($departments as $dept)
            @php
                $deptMembers = $members->filter(function($mem) use ($dept, $organization) {
                    $prof = $mem->user->profiles->where('organization_id', $organization->id)->first();
                    return $prof && $prof->department_id == $dept->id;
                });
            @endphp
            <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs); border-radius: var(--ula-radius-xl); padding: var(--ula-space-6); background: var(--ula-surface-card);">
                <div>
                    <!-- Department Header -->
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: var(--ula-space-5);">
                        <div style="display: flex; align-items: center; gap: var(--ula-space-4);">
                            <div class="kpi-icon-box" style="width: 44px; height: 44px; border-radius: var(--ula-radius-lg); background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); display: flex; align-items: center; justify-content: center; box-shadow: var(--ula-shadow-sm);">
                                <span class="material-symbols-rounded" style="font-size: 24px;">apartment</span>
                            </div>
                            <div>
                                <h3 style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin-bottom: 2px;">{{ $dept->name }}</h3>
                                <span style="font-size: var(--ula-size-xs); color: var(--ula-text-muted); font-weight: var(--ula-weight-semibold); font-family: var(--ula-font-mono);">{{ $dept->teams->count() }} {{ __('Teams') }} • {{ $deptMembers->count() }} {{ __('Members') }}</span>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: var(--ula-space-3);">
                            <button onclick="editDepartment('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" class="tactile-btn btn-secondary" style="padding: 6px 10px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center;" title="{{ __('Edit Department') }}">
                                <span class="material-symbols-rounded" style="font-size: 16px;">edit</span>
                            </button>
                            <form action="{{ route('departments.delete', $dept->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this department?') }}');" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.12); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.25); padding: 6px 10px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; border-radius: var(--ula-radius-md);" title="{{ __('Delete Department') }}">
                                <span class="material-symbols-rounded" style="font-size: 16px;">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Sub-Teams Section -->
                    <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md); padding: var(--ula-space-5); margin-bottom: var(--ula-space-5); box-shadow: var(--ula-shadow-xs);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--ula-space-4);">
                            <span style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); text-transform: uppercase; letter-spacing: 0.04em;">{{ __('Sub-Teams') }}</span>
                            <button onclick="openTeamModal('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" style="background: none; border: none; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-accent-default); cursor: pointer; display: inline-flex; align-items: center; gap: 2px;">
                                <span class="material-symbols-rounded" style="font-size: 14px;">add</span>
                                <span>{{ __('Add Team') }}</span>
                            </button>
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: var(--ula-space-3);">
                            @forelse($dept->teams as $t)
                                <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md); padding: 5px 10px; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); display: flex; align-items: center; gap: var(--ula-space-3); box-shadow: var(--ula-shadow-xs);">
                                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--ula-accent-default);">group</span>
                                    <span>{{ $t->name }}</span>
                                    <form action="{{ route('teams.delete', $t->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this team?') }}');" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: var(--ula-text-muted); cursor: pointer; font-size: 14px; padding: 0; line-height: 1; display: flex; align-items: center;" title="{{ __('Delete Team') }}">
                                            <span class="material-symbols-rounded" style="font-size: 13px;">close</span>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <span style="font-size: var(--ula-size-xs); color: var(--ula-text-muted); font-style: italic;">{{ __('No sub-teams created yet.') }}</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Assigned Department Members -->
                    <div>
                        <span style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: var(--ula-space-3);">{{ __('Assigned Staff') }} ({{ $deptMembers->count() }})</span>
                        <div style="display: flex; flex-direction: column; gap: var(--ula-space-3);">
                            @forelse($deptMembers->take(4) as $dm)
                                @php
                                    $prof = $dm->user->profiles->where('organization_id', $organization->id)->first();
                                    $tObj = $teams->where('id', $prof?->team_id)->first();
                                @endphp
                                <div style="display: flex; align-items: center; justify-content: space-between; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); padding: 8px 12px; border-radius: var(--ula-radius-md);">
                                    <div style="display: flex; align-items: center; gap: var(--ula-space-3);">
                                        <div style="width: 28px; height: 28px; border-radius: var(--ula-radius-sm); background: var(--ula-gradient-accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: var(--ula-weight-bold); font-family: var(--ula-font-mono); box-shadow: var(--ula-shadow-xs);">
                                            {{ strtoupper(substr($dm->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary);">{{ $dm->user->name }}</span>
                                            @if($prof?->job_title)
                                                <span style="font-size: 10px; color: var(--ula-text-muted);"> • {{ $prof->job_title }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($tObj)
                                        <span class="nav-badge-pill" style="font-size: 10px;">{{ $tObj->name }}</span>
                                    @endif
                                </div>
                            @empty
                                <div style="text-align: center; padding: 12px; font-size: var(--ula-size-xs); color: var(--ula-text-muted); background: var(--ula-surface-page-alt); border: 1px dashed var(--ula-border-subtle); border-radius: var(--ula-radius-md);">
                                    {{ __('No members assigned to this department yet.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; color: var(--ula-text-muted); padding: var(--ula-space-9); border-radius: var(--ula-radius-xl); background: var(--ula-surface-card); border: 1px dashed var(--ula-border-subtle);">
                <div style="font-size: 40px; margin-bottom: var(--ula-space-4); color: var(--ula-accent-default); display: flex; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 48px;">apartment</span>
                </div>
                <h3 style="font-size: var(--ula-size-body-lg); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin-bottom: var(--ula-space-3);">{{ __('No departments found') }}</h3>
                <p style="font-size: var(--ula-size-sm); color: var(--ula-text-secondary); margin-bottom: var(--ula-space-6);">{{ __('Create departments and divide your organization into structured functional teams.') }}</p>
                <button onclick="openDepartmentModal()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: var(--ula-size-sm); display: inline-flex; align-items: center; gap: var(--ula-space-3); margin: 0 auto;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">add</span>
                    <span>{{ __('New Department') }}</span>
                </button>
            </div>
        @endforelse
    </div>
</div>