<div id="tab-offices" class="tab-view">
    <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: var(--ula-space-6); gap: var(--ula-space-4);">
        @if(!$organization->hasReachedOfficeLimit())
        <x-btn variant="primary" size="md" onclick="openNewOfficeModal()" icon="add">
            {{ __('Add Office Branch') }}
        </x-btn>
        @else
        <x-btn variant="nav-cta" size="md" onclick="switchAdminTab('billing')" icon="workspace_premium">
            {{ __('Upgrade Plan for More Offices') }}
        </x-btn>
        @endif
    </div>

    <!-- Quota Indicator Banner -->
    <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: var(--ula-space-5) var(--ula-space-6); display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--ula-space-7); flex-wrap: wrap; gap: var(--ula-space-4); box-shadow: var(--ula-shadow-sm);">
        <div style="display: flex; align-items: center; gap: var(--ula-space-4);">
            <div style="width: 36px; height: 36px; border-radius: var(--ula-radius-md); background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-rounded" style="font-size: 20px;">diamond</span>
            </div>
            <div>
                <strong style="color: var(--ula-text-primary); font-size: var(--ula-size-sm); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">{{ __('Offices Quota:') }} {{ $offices->count() }} / {{ $organization->plan?->isUnlimitedOffices() ? __('Unlimited') : ($organization->plan?->max_offices ?? 1) }}</strong>
                <div style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary);">{{ __('Your organization is subscribed to :plan plan.', ['plan' => $organization->plan?->name ?? 'Default']) }}</div>
            </div>
        </div>
        <span class="badge-status" style="background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); font-weight: var(--ula-weight-bold); font-size: var(--ula-size-xs); padding: 4px 12px; border-radius: var(--ula-radius-pill); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">
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
                        <div style="width: 44px; height: 44px; border-radius: var(--ula-radius-lg); background: var(--ula-gradient-accent); color: var(--ula-white); display: flex; align-items: center; justify-content: center; box-shadow: var(--ula-shadow-xs);">
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
                        <span class="badge-status" style="background: rgba(79, 155, 95, 0.15); color: var(--ula-status-success); font-size: 11px; font-weight: var(--ula-weight-bold); display: inline-flex; align-items: center; gap: 2px; padding: 3px 8px; border-radius: var(--ula-radius-pill);">
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
                        <div style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin-top: 2px; display: flex; align-items: center; gap: 4px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-accent-default);">meeting_room</span>
                            <span>{{ $off->rooms->count() }}</span>
                        </div>
                    </div>
                    <div>
                        <span style="font-size: var(--ula-size-xs); color: var(--ula-text-muted);">{{ __('Assigned Staff') }}</span>
                        <div style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-accent-default); margin-top: 2px; display: flex; align-items: center; gap: 4px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">
                            <span class="material-symbols-rounded" style="font-size: 16px;">group</span>
                            <span>{{ $off->members->count() > 0 ? $off->members->count() : __('All') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: var(--ula-space-3); align-items: center; border-top: 1px solid var(--ula-border-subtle); padding-top: var(--ula-space-4);">
                <x-btn variant="primary" size="sm" href="{{ route('office', ['office' => $off->id]) }}" icon="login" style="flex: 1; justify-content: center;">
                    {{ __('Enter Office') }}
                </x-btn>
                <x-btn variant="secondary" size="sm" :iconOnly="true" icon="edit" onclick="openEditOfficeModal('{{ $off->id }}', '{{ addslashes($off->name) }}', '{{ addslashes($off->city_location ?? '') }}', '{{ addslashes($off->description ?? '') }}', {{ $off->is_default ? 'true' : 'false' }})" title="{{ __('Edit Branch Details') }}" />
                @if($offices->count() > 1)
                <form method="POST" action="{{ route('offices.delete', $off->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to permanently delete this office branch and its blueprint?') }}');" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <x-btn variant="danger" size="sm" :iconOnly="true" icon="delete" type="submit" title="{{ __('Delete Branch') }}" />
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
