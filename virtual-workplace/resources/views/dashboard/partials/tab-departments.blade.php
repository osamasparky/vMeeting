<div id="tab-departments" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-6); flex-wrap: wrap; gap: var(--nx-spacing-4);">
        <div>
            <h1 class="page-title" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-1); display: flex; align-items: center; gap: var(--nx-spacing-2);">
                <span class="material-symbols-rounded" style="font-size: 28px; color: var(--nx-primary-500);">apartment</span>
                <span>{{ __('Departments & Teams') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: var(--nx-font-size-sm); color: var(--nx-text-secondary);">{{ __('Organize your organization staff, distribute members across departments, and manage sub-teams.') }}</p>
        </div>
        <button onclick="openDepartmentModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: var(--nx-font-size-sm); display: inline-flex; align-items: center; gap: var(--nx-spacing-2);">
            <span class="material-symbols-rounded" style="font-size: 18px;">add</span>
            <span>{{ __('New Department') }}</span>
        </button>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: var(--nx-spacing-5);">
        @forelse($departments as $dept)
            @php
                $deptMembers = $members->filter(function($mem) use ($dept, $organization) {
                    $prof = $mem->user->profiles->where('organization_id', $organization->id)->first();
                    return $prof && $prof->department_id == $dept->id;
                });
            @endphp
            <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-5); background: var(--nx-bg-surface);">
                <div>
                    <!-- Department Header -->
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: var(--nx-spacing-4);">
                        <div style="display: flex; align-items: center; gap: var(--nx-spacing-3);">
                            <div class="kpi-icon-box" style="width: 44px; height: 44px; border-radius: var(--nx-radius-lg); background: var(--nx-primary-surface); color: var(--nx-primary-500); display: flex; align-items: center; justify-content: center; box-shadow: var(--nx-shadow-sm);">
                                <span class="material-symbols-rounded" style="font-size: 24px;">apartment</span>
                            </div>
                            <div>
                                <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: 2px;">{{ $dept->name }}</h3>
                                <span style="font-size: var(--nx-font-size-xs); color: var(--nx-text-muted); font-weight: var(--nx-font-weight-semibold); font-family: var(--nx-font-mono);">{{ $dept->teams->count() }} {{ __('Teams') }} • {{ $deptMembers->count() }} {{ __('Members') }}</span>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: var(--nx-spacing-2);">
                            <button onclick="editDepartment('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" class="tactile-btn btn-secondary" style="padding: 6px 10px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center;" title="{{ __('Edit Department') }}">
                                <span class="material-symbols-rounded" style="font-size: 16px;">edit</span>
                            </button>
                            <form action="{{ route('departments.delete', $dept->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this department?') }}');" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.12); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.25); padding: 6px 10px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; border-radius: var(--nx-radius-md);" title="{{ __('Delete Department') }}">
                                <span class="material-symbols-rounded" style="font-size: 16px;">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Sub-Teams Section -->
                    <div style="background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: var(--nx-spacing-4); margin-bottom: var(--nx-spacing-4); box-shadow: var(--nx-shadow-inset-3d);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--nx-spacing-3);">
                            <span style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); text-transform: uppercase; letter-spacing: 0.04em;">{{ __('Sub-Teams') }}</span>
                            <button onclick="openTeamModal('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" style="background: none; border: none; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-primary-500); cursor: pointer; display: inline-flex; align-items: center; gap: 2px;">
                                <span class="material-symbols-rounded" style="font-size: 14px;">add</span>
                                <span>{{ __('Add Team') }}</span>
                            </button>
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: var(--nx-spacing-2);">
                            @forelse($dept->teams as $t)
                                <div style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 5px 10px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-primary); display: flex; align-items: center; gap: var(--nx-spacing-2); box-shadow: var(--nx-shadow-soft-3d);">
                                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--nx-primary-500);">group</span>
                                    <span>{{ $t->name }}</span>
                                    <form action="{{ route('teams.delete', $t->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this team?') }}');" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: var(--nx-text-muted); cursor: pointer; font-size: 14px; padding: 0; line-height: 1; display: flex; align-items: center;" title="{{ __('Delete Team') }}">
                                            <span class="material-symbols-rounded" style="font-size: 13px;">close</span>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <span style="font-size: var(--nx-font-size-xs); color: var(--nx-text-muted); font-style: italic;">{{ __('No sub-teams created yet.') }}</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Assigned Department Members -->
                    <div>
                        <span style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: var(--nx-spacing-2);">{{ __('Assigned Staff') }} ({{ $deptMembers->count() }})</span>
                        <div style="display: flex; flex-direction: column; gap: var(--nx-spacing-2);">
                            @forelse($deptMembers->take(4) as $dm)
                                @php
                                    $prof = $dm->user->profiles->where('organization_id', $organization->id)->first();
                                    $tObj = $teams->where('id', $prof?->team_id)->first();
                                @endphp
                                <div style="display: flex; align-items: center; justify-content: space-between; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); padding: 8px 12px; border-radius: var(--nx-radius-md);">
                                    <div style="display: flex; align-items: center; gap: var(--nx-spacing-2);">
                                        <div style="width: 28px; height: 28px; border-radius: var(--nx-radius-sm); background: var(--nx-accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: var(--nx-font-weight-bold); font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-soft-3d);">
                                            {{ strtoupper(substr($dm->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-primary);">{{ $dm->user->name }}</span>
                                            @if($prof?->job_title)
                                                <span style="font-size: 10px; color: var(--nx-text-muted);"> • {{ $prof->job_title }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($tObj)
                                        <span class="nav-badge-pill" style="font-size: 10px;">{{ $tObj->name }}</span>
                                    @endif
                                </div>
                            @empty
                                <div style="text-align: center; padding: 12px; font-size: var(--nx-font-size-xs); color: var(--nx-text-muted); background: var(--nx-bg-surface-subtle); border: 1px dashed var(--nx-border-subtle); border-radius: var(--nx-radius-md);">
                                    {{ __('No members assigned to this department yet.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; color: var(--nx-text-muted); padding: var(--nx-spacing-10); border-radius: var(--nx-radius-xl); background: var(--nx-bg-surface); border: 1px dashed var(--nx-border-subtle);">
                <div style="font-size: 40px; margin-bottom: var(--nx-spacing-3); color: var(--nx-primary-500); display: flex; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 48px;">apartment</span>
                </div>
                <h3 style="font-size: var(--nx-font-size-lg); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-2);">{{ __('No departments found') }}</h3>
                <p style="font-size: var(--nx-font-size-sm); color: var(--nx-text-secondary); margin-bottom: var(--nx-spacing-5);">{{ __('Create departments and divide your organization into structured functional teams.') }}</p>
                <button onclick="openDepartmentModal()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: var(--nx-font-size-sm); display: inline-flex; align-items: center; gap: var(--nx-spacing-2); margin: 0 auto;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">add</span>
                    <span>{{ __('New Department') }}</span>
                </button>
            </div>
        @endforelse
    </div>
</div>