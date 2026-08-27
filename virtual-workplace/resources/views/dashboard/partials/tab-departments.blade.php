<div id="tab-departments" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">🏛️ {{ __('Departments & Teams') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Organize your organization staff, distribute members across departments, and manage sub-teams.') }}</p>
                </div>
                <button onclick="openDepartmentModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                    <span>+</span> {{ __('New Department') }}
                </button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px;">
                @forelse($departments as $dept)
                    @php
                        $deptMembers = $members->filter(function($mem) use ($dept, $organization) {
                            $prof = $mem->user->profiles->where('organization_id', $organization->id)->first();
                            return $prof && $prof->department_id == $dept->id;
                        });
                    @endphp
                    <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); border-radius: var(--radius-xl); padding: 22px;">
                        <div>
                            <!-- Department Header -->
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 14px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="kpi-icon-box" style="font-size: 22px;">
                                        🏛️
                                    </div>
                                    <div>
                                        <h3 style="font-size: 17px; font-weight: 900; color: var(--text-primary); margin-bottom: 2px;">{{ $dept->name }}</h3>
                                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 700;">{{ $dept->teams->count() }} {{ __('Teams') }} • {{ $deptMembers->count() }} {{ __('Members') }}</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button onclick="editDepartment('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" class="tactile-btn btn-secondary" style="padding: 6px 10px; font-size: 12px;" title="{{ __('Edit Department') }}">✏️</button>
                                    <form action="{{ route('departments.delete', $dept->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this department?') }}');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 6px 10px; font-size: 12px;" title="{{ __('Delete Department') }}">🗑️</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Sub-Teams Section -->
                            <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 14px; box-shadow: var(--shadow-inset-3d);">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                    <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase;">{{ __('Sub-Teams') }}</span>
                                    <button onclick="openTeamModal('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" style="background: none; border: none; font-size: 11px; font-weight: 800; color: var(--brand-forest); cursor: pointer;">+ {{ __('Add Team') }}</button>
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    @forelse($dept->teams as $t)
                                        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 8px; padding: 5px 10px; font-size: 11px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px; box-shadow: var(--shadow-soft-3d);">
                                            <span>👥 {{ $t->name }}</span>
                                            <form action="{{ route('teams.delete', $t->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this team?') }}');" style="display: inline; margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 11px; padding: 0; line-height: 1;" title="{{ __('Delete Team') }}">✕</button>
                                            </form>
                                        </div>
                                    @empty
                                        <span style="font-size: 11px; color: var(--text-muted); font-style: italic;">{{ __('No sub-teams created yet.') }}</span>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Assigned Department Members -->
                            <div>
                                <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; display: block; margin-bottom: 8px;">{{ __('Assigned Staff') }} ({{ $deptMembers->count() }})</span>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    @forelse($deptMembers->take(4) as $dm)
                                        @php
                                            $prof = $dm->user->profiles->where('organization_id', $organization->id)->first();
                                            $tObj = $teams->where('id', $prof?->team_id)->first();
                                        @endphp
                                        <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); padding: 8px 12px; border-radius: 10px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="width: 26px; height: 26px; border-radius: 8px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; box-shadow: var(--shadow-soft-3d);">
                                                    {{ strtoupper(substr($dm->user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <span style="font-size: 12px; font-weight: 800; color: var(--text-primary);">{{ $dm->user->name }}</span>
                                                    @if($prof?->job_title)
                                                        <span style="font-size: 10px; color: var(--text-muted);"> • {{ $prof->job_title }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($tObj)
                                                <span class="nav-badge-pill" style="font-size: 10px;">{{ $tObj->name }}</span>
                                            @endif
                                        </div>
                                    @empty
                                        <div style="text-align: center; padding: 12px; font-size: 11px; color: var(--text-muted); background: var(--bg-surface-subtle); border: 1px dashed var(--border-color); border-radius: 10px;">
                                            {{ __('No members assigned to this department yet.') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card" style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px; border-radius: var(--radius-xl);">
                        <div style="font-size: 36px; margin-bottom: 10px;">🏛️</div>
                        <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary); margin-bottom: 6px;">{{ __('No departments found') }}</h3>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 18px;">{{ __('Create departments and divide your organization into structured functional teams.') }}</p>
                        <button onclick="openDepartmentModal()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: 13px;">
                            <span>+</span> {{ __('New Department') }}
                        </button>
                    </div>
                @endforelse
            </div>
        </div>