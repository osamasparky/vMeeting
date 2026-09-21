<div id="tab-offices" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--ula-space-7); flex-wrap: wrap; gap: var(--ula-space-5);">
        <div>
            <h1 class="page-title" style="font-size: var(--ula-size-h3); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin-bottom: var(--ula-space-2); display: flex; align-items: center; gap: var(--ula-space-3);">
                <span class="material-symbols-rounded" style="font-size: 28px; color: var(--ula-accent-default);">apartment</span>
                <span>{{ __('Offices & Virtual Branches') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: var(--ula-size-sm); color: var(--ula-text-secondary);">{{ __('Manage multiple branches (e.g. Cairo Branch, Riyadh HQ, Dubai Hub), their blueprints, and member access permissions.') }}</p>
        </div>
        <div style="display: flex; gap: var(--ula-space-4); align-items: center;">
            @if(!$organization->hasReachedOfficeLimit())
            <button onclick="openNewOfficeModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: var(--ula-size-sm); display: inline-flex; align-items: center; gap: var(--ula-space-3);">
                <span class="material-symbols-rounded" style="font-size: 18px;">add</span>
                <span>{{ __('Add Office Branch') }}</span>
            </button>
            @else
            <button onclick="switchAdminTab('billing')" class="tactile-btn" style="padding: 10px 18px; font-size: var(--ula-size-sm); background: linear-gradient(180deg, #D6A23A 0%, #B4831B 100%); color: white; border: 1px solid #996D12; display: inline-flex; align-items: center; gap: var(--ula-space-3);">
                <span class="material-symbols-rounded" style="font-size: 18px;">workspace_premium</span>
                <span>{{ __('Upgrade Plan for More Offices') }}</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Quota Indicator Banner -->
    <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: var(--ula-space-5) var(--ula-space-6); display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--ula-space-7); flex-wrap: wrap; gap: var(--ula-space-4); box-shadow: var(--ula-shadow-sm);">
        <div style="display: flex; align-items: center; gap: var(--ula-space-4);">
            <div style="width: 36px; height: 36px; border-radius: var(--ula-radius-md); background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-rounded" style="font-size: 20px;">diamond</span>
            </div>
            <div>
                <strong style="color: var(--ula-text-primary); font-size: var(--ula-size-sm); font-family: var(--ula-font-mono);">{{ __('Offices Quota:') }} {{ $offices->count() }} / {{ $organization->plan?->isUnlimitedOffices() ? __('Unlimited') : ($organization->plan?->max_offices ?? 1) }}</strong>
                <div style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary);">{{ __('Your organization is subscribed to :plan plan.', ['plan' => $organization->plan?->name ?? 'Default']) }}</div>
            </div>
        </div>
        <span class="badge-status" style="background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); font-weight: var(--ula-weight-bold); font-size: var(--ula-size-xs); padding: 4px 12px; border-radius: var(--ula-radius-pill); font-family: var(--ula-font-mono);">
            {{ $offices->count() }} {{ __('Active Branches') }}
        </span>
    </div>

    <!-- Offices Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--ula-space-6);">
        @forelse($offices as $off)
        <div class="card" style="border-radius: var(--ula-radius-xl); padding: var(--ula-space-6); display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs); background: var(--ula-surface-card); transition: all 0.25s ease;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--ula-space-5);">
                    <div style="display: flex; align-items: center; gap: var(--ula-space-4);">
                        <div style="width: 44px; height: 44px; border-radius: var(--ula-radius-lg); background: var(--ula-gradient-accent); color: white; display: flex; align-items: center; justify-content: center; box-shadow: var(--ula-shadow-xs);">
                            <span class="material-symbols-rounded" style="font-size: 24px;">apartment</span>
                        </div>
                        <div>
                            <h3 style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin: 0 0 2px 0;">{{ $off->name }}</h3>
                            <span style="font-size: var(--ula-size-xs); color: var(--ula-text-muted); font-weight: var(--ula-weight-semibold); display: flex; align-items: center; gap: 2px;">
                                <span class="material-symbols-rounded" style="font-size: 14px;">location_on</span>
                                <span>{{ $off->city_location ?: __('Primary Location') }}</span>
                            </span>
                        </div>
                    </div>
                    @if($off->is_default)
                        <span class="badge-status" style="background: rgba(79, 155, 95, 0.15); color: #2E6B40; font-size: 11px; font-weight: var(--ula-weight-bold); display: inline-flex; align-items: center; gap: 2px; padding: 3px 8px; border-radius: var(--ula-radius-pill);">
                            <span class="material-symbols-rounded" style="font-size: 13px;">star</span>
                            <span>{{ __('Main HQ') }}</span>
                        </span>
                    @endif
                </div>

                @if($off->description)
                    <p style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary); margin: 0 0 var(--ula-space-5) 0; line-height: 1.5;">
                        {{ $off->description }}
                    </p>
                @endif

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--ula-space-4); margin-bottom: var(--ula-space-5); padding: var(--ula-space-4); background: var(--ula-surface-page-alt); border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                    <div>
                        <span style="font-size: var(--ula-size-xs); color: var(--ula-text-muted);">{{ __('Configured Rooms') }}</span>
                        <div style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin-top: 2px; display: flex; align-items: center; gap: 4px; font-family: var(--ula-font-mono);">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-accent-default);">meeting_room</span>
                            <span>{{ $off->rooms->count() }}</span>
                        </div>
                    </div>
                    <div>
                        <span style="font-size: var(--ula-size-xs); color: var(--ula-text-muted);">{{ __('Assigned Staff') }}</span>
                        <div style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-accent-default); margin-top: 2px; display: flex; align-items: center; gap: 4px; font-family: var(--ula-font-mono);">
                            <span class="material-symbols-rounded" style="font-size: 16px;">group</span>
                            <span>{{ $off->members->count() > 0 ? $off->members->count() : __('All') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: var(--ula-space-3); flex-wrap: wrap; border-top: 1px solid var(--ula-border-subtle); padding-top: var(--ula-space-4);">
                <a href="{{ route('office', ['office' => $off->id]) }}" class="tactile-btn btn-primary" style="flex: 1; justify-content: center; padding: 8px 12px; font-size: var(--ula-size-xs); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">login</span>
                    <span>{{ __('Enter Office') }}</span>
                </a>
                <button onclick="openEditOfficeModal('{{ $off->id }}', '{{ addslashes($off->name) }}', '{{ addslashes($off->city_location ?? '') }}', '{{ addslashes($off->description ?? '') }}', {{ $off->is_default ? 'true' : 'false' }})" class="tactile-btn btn-secondary" style="padding: 8px 12px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center;" title="{{ __('Edit Branch Details') }}">
                    <span class="material-symbols-rounded" style="font-size: 16px;">edit</span>
                </button>
                @if($offices->count() > 1)
                <form method="POST" action="{{ route('offices.delete', $off->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to permanently delete this office branch and its blueprint?') }}');" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="tactile-btn" style="padding: 8px 12px; font-size: 12px; background: rgba(217, 107, 95, 0.12); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.25); display: inline-flex; align-items: center; justify-content: center; border-radius: var(--ula-radius-md);" title="{{ __('Delete Branch') }}">
                        <span class="material-symbols-rounded" style="font-size: 16px;">delete</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: var(--ula-space-9); color: var(--ula-text-muted); background: var(--ula-surface-card); border: 1px dashed var(--ula-border-subtle); border-radius: var(--ula-radius-xl);">
            <div style="font-size: 32px; margin-bottom: var(--ula-space-3); color: var(--ula-text-muted); display: flex; justify-content: center;">
                <span class="material-symbols-rounded" style="font-size: 40px;">domain_disabled</span>
            </div>
            <p style="margin: 0; font-size: var(--ula-size-sm);">{{ __('No office branches configured yet.') }}</p>
        </div>
        @endforelse
    </div>
</div>
