        <div id="tab-offices" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">🏢 {{ __('Offices & Virtual Branches (الفروع ومكاتب العمل)') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Manage multiple branches (e.g. Cairo Branch, Riyadh HQ, Dubai Hub), their blueprints, and member access permissions.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    @if(!$organization->hasReachedOfficeLimit())
                    <button onclick="openNewOfficeModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>➕</span> {{ __('Add Office Branch (إضافة فرع جديد)') }}
                    </button>
                    @else
                    <button onclick="switchAdminTab('billing')" class="tactile-btn" style="padding: 10px 18px; font-size: 13px; background: linear-gradient(180deg, #D6A23A 0%, #B4831B 100%); color: white; border: 1px solid #996D12;">
                        <span>👑</span> {{ __('Upgrade Plan for More Offices') }}
                    </button>
                    @endif
                </div>
            </div>

            <!-- Quota Indicator Banner -->
            <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">💎</span>
                    <div>
                        <strong style="color: var(--text-primary); font-size: 13px;">{{ __('Offices Quota:') }} {{ $offices->count() }} / {{ $organization->plan?->isUnlimitedOffices() ? __('Unlimited (غير محدود)') : ($organization->plan?->max_offices ?? 1) }}</strong>
                        <div style="font-size: 11px; color: var(--text-secondary);">{{ __('Your organization is subscribed to :plan plan.', ['plan' => $organization->plan?->name ?? 'Default']) }}</div>
                    </div>
                </div>
                <span class="badge-status" style="background: rgba(36, 92, 58, 0.12); color: var(--brand-forest); font-weight: 800; font-size: 12px;">
                    {{ $offices->count() }} {{ __('Active Branches') }}
                </span>
            </div>

            <!-- Offices Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                @forelse($offices as $off)
                <div class="card" style="border-radius: var(--radius-xl); padding: 22px; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); transition: all 0.25s ease;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 44px; height: 44px; border-radius: 14px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: var(--shadow-soft-3d);">
                                    🏢
                                </div>
                                <div>
                                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin: 0 0 2px 0;">{{ $off->name }}</h3>
                                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                                        📍 {{ $off->city_location ?: __('Primary Location') }}
                                    </span>
                                </div>
                            </div>
                            @if($off->is_default)
                                <span class="badge-status" style="background: rgba(79, 155, 95, 0.15); color: #2E6B40; font-size: 11px; font-weight: 900;">
                                    ⭐ {{ __('Main HQ (الرئيسي)') }}
                                </span>
                            @endif
                        </div>

                        @if($off->description)
                            <p style="font-size: 12px; color: var(--text-secondary); margin: 0 0 16px 0; line-height: 1.5;">
                                {{ $off->description }}
                            </p>
                        @endif

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; padding: 12px; background: var(--bg-surface-subtle); border-radius: 12px; border: 1px solid var(--border-color);">
                            <div>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ __('Configured Rooms') }}</span>
                                <div style="font-size: 15px; font-weight: 900; color: var(--text-primary); margin-top: 2px;">
                                    🚪 {{ $off->rooms->count() }}
                                </div>
                            </div>
                            <div>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ __('Assigned Staff') }}</span>
                                <div style="font-size: 15px; font-weight: 900; color: var(--brand-forest); margin-top: 2px;">
                                    👥 {{ $off->members->count() > 0 ? $off->members->count() : __('All (الكل)') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px; flex-wrap: wrap; pt-2; border-top: 1px solid var(--border-color); padding-top: 14px;">
                        <a href="{{ route('office', ['office' => $off->id]) }}" class="tactile-btn btn-primary" style="flex: 1; justify-content: center; padding: 8px 12px; font-size: 12px; text-decoration: none;">
                            <span>🚀</span> {{ __('Enter Office') }}
                        </a>
                        <button onclick="openEditOfficeModal('{{ $off->id }}', '{{ addslashes($off->name) }}', '{{ addslashes($off->city_location ?? '') }}', '{{ addslashes($off->description ?? '') }}', {{ $off->is_default ? 'true' : 'false' }})" class="tactile-btn" style="padding: 8px 12px; font-size: 12px; background: var(--bg-surface-subtle);" title="{{ __('Edit Branch Details') }}">
                            <span>✏️</span>
                        </button>
                        @if($offices->count() > 1)
                        <form method="POST" action="{{ route('offices.delete', $off->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to permanently delete this office branch and its blueprint?') }}');" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tactile-btn" style="padding: 8px 12px; font-size: 12px; background: rgba(217, 107, 95, 0.12); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);" title="{{ __('Delete Branch') }}">
                                <span>🗑️</span>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
                    <p>{{ __('No office branches configured yet.') }}</p>
                </div>
                @endforelse
            </div>
        </div>
