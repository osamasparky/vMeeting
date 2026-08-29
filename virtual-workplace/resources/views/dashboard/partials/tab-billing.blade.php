                    <div id="tab-billing" class="tab-view">
            <div class="page-header" style="margin-bottom: 24px;">
                <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">💎 {{ __('Billing & Subscription') }}</h1>
                <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Manage your plan tier, seat capacity, renewal period, and workspace upgrade.') }}</p>
            </div>

            <!-- Pending Subscription Request Banner -->
            @if(isset($pendingSubscriptionRequest) && $pendingSubscriptionRequest)
            <div class="card" style="margin-bottom: 24px; border-radius: var(--radius-xl); padding: 22px; border: 2px solid #D6A23A; background: linear-gradient(135deg, rgba(214, 162, 58, 0.08) 0%, rgba(214, 162, 58, 0.02) 100%); box-shadow: var(--shadow-card);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(214, 162, 58, 0.2); color: #D6A23A; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            ⏳
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <span style="font-size: 11px; font-weight: 900; color: #996D12; text-transform: uppercase;">{{ __('Pending Wire Transfer Approval') }}</span>
                                <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.25); color: #996D12; font-size: 10px; font-weight: 900;">{{ __('Under SuperAdmin Review') }}</span>
                            </div>
                            <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary); margin: 0;">
                                💎 {{ __('Upgrade to') }} {{ $pendingSubscriptionRequest->plan?->name ?? __('Plan') }} — {{ number_format($pendingSubscriptionRequest->amount, 2) }} {{ $pendingSubscriptionRequest->currency }}
                            </h3>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px; display: flex; gap: 14px; flex-wrap: wrap;">
                                <span>🏦 <strong>{{ $pendingSubscriptionRequest->bank_name }}</strong></span>
                                <span>📋 {{ __('Ref') }}: <strong style="font-family: monospace;">{{ $pendingSubscriptionRequest->transfer_reference }}</strong></span>
                                <span>📅 {{ $pendingSubscriptionRequest->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="{{ route('subscription.payment', $pendingSubscriptionRequest->plan_id) }}" class="tactile-btn" style="padding: 9px 16px; font-size: 12px; text-decoration: none;">
                            📄 {{ __('View Transfer Details') }}
                        </a>
                        <form method="POST" action="{{ route('subscription.payment.cancel', $pendingSubscriptionRequest->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to cancel this pending subscription request?') }}');" style="margin: 0;">
                            @csrf
                            <button type="submit" class="tactile-btn" style="padding: 9px 14px; font-size: 12px; color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);">
                                ✕ {{ __('Cancel') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @php
                $currentPlan = $organization->plan ?? \App\Domains\Tenancy\Models\Plan::where('slug', 'free')->first();
                $seatLimit = $currentPlan?->seat_limit ?? 5;
                $roomLimit = $currentPlan?->room_limit ?? 3;
                $maxOffices = $currentPlan?->max_offices ?? 1;
                $maxGuests = $currentPlan?->max_guest_invitations ?? 5;
                $storageLimit = $currentPlan?->storage_limit_gb ?? 1;

                $usedSeats = $members->count();
                $usedRooms = $rooms->count();
                $usedOffices = $offices->count();
                $usedGuests = $guestInvitations->count();

                $isSeatsExceeded = ($seatLimit > 0 && $usedSeats > $seatLimit);
                $isRoomsExceeded = ($roomLimit > 0 && $usedRooms > $roomLimit);
                $isOfficesExceeded = ($maxOffices > 0 && $usedOffices > $maxOffices);
                $isGuestsExceeded = ($maxGuests > 0 && $usedGuests > $maxGuests);
                $isAnyExceeded = ($isSeatsExceeded || $isRoomsExceeded || $isOfficesExceeded || $isGuestsExceeded);

                $isUnlimitedSeats = ($seatLimit === 0);
                $seatPercent = $isUnlimitedSeats ? 20 : min(100, round(($usedSeats / max(1, $seatLimit)) * 100));

                $subscription = $organization->subscription;
                $startDate = $subscription?->created_at ?? $organization->created_at;
                $endDate = $subscription?->current_period_end ?? ($startDate ? (clone $startDate)->addMonth() : now()->addMonth());
                $status = $subscription?->status ?? 'active';

                $priceUSD = (float)($currentPlan->price ?? 0);
                $priceSAR = round($priceUSD * 3.75, 2);
            @endphp

            @if($isAnyExceeded)
            <!-- Exceeded Plan Quota Warning Banner -->
            <div class="card" style="margin-bottom: 24px; border-radius: var(--radius-xl); padding: 18px 24px; border: 2px solid #D96B5F; background: linear-gradient(135deg, rgba(217, 107, 95, 0.12) 0%, rgba(217, 107, 95, 0.03) 100%); box-shadow: var(--shadow-card);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(217, 107, 95, 0.2); color: #D96B5F; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                            ⚠️
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: #D96B5F; margin: 0 0 4px 0;">
                                {{ __('Plan Limit Exceeded') }} ({{ __('تجاوزت الحد المسموح للباقة') }})
                            </h3>
                            <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">
                                @if($isRoomsExceeded)
                                    {{ __('Your workspace currently has :used rooms, which exceeds your :plan plan quota (:limit rooms). Please upgrade your subscription plan below.', ['used' => $usedRooms, 'plan' => $currentPlan->name ?? 'Free', 'limit' => $roomLimit]) }}
                                @elseif($isSeatsExceeded)
                                    {{ __('Your workspace currently has :used members, which exceeds your :plan seat quota (:limit seats). Please upgrade your plan below.', ['used' => $usedSeats, 'plan' => $currentPlan->name ?? 'Free', 'limit' => $seatLimit]) }}
                                @else
                                    {{ __('Some workplace resources exceed your current plan limits. Please upgrade below.') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <a href="#available-plans-section" class="tactile-btn" style="background: #D96B5F; color: white; border: none; padding: 10px 20px; font-size: 13px; font-weight: 800; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                        <span>🚀</span> {{ __('Upgrade Plan Now (ترقية الباقة)') }}
                    </a>
                </div>
            </div>
            @endif

            <!-- Current Plan Card (3D Soft Neumorphic) -->
            <div class="card" style="margin-bottom: 28px; border-radius: var(--radius-xl); padding: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); background: var(--bg-surface);">
                <!-- Top Row: Plan info, SAR/USD Price, and Status -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid var(--border-color);">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <span style="font-size: 11px; font-weight: 800; color: var(--brand-forest); text-transform: uppercase; letter-spacing: 0.5px;">{{ __('Current Plan') }}</span>
                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F; font-size: 10px; text-transform: uppercase;">{{ ucfirst($status) }}</span>
                            @if($isAnyExceeded)
                                <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; font-size: 10px; font-weight: 900;">⚠️ {{ __('Limit Exceeded') }}</span>
                            @endif
                        </div>
                        <h2 style="font-size: 26px; font-weight: 900; color: var(--text-primary); margin: 4px 0;">💎 {{ $currentPlan->name ?? __('Free Tier') }}</h2>
                        <div style="display: flex; align-items: baseline; gap: 10px; margin-top: 6px;">
                            <span style="font-size: 24px; font-weight: 900; color: var(--brand-forest);">
                                {{ number_format($priceSAR, 2) }} <span style="font-size: 14px; font-weight: 700; color: var(--text-secondary);">{{ __('SAR (ر.س)') }}</span>
                            </span>
                            <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">
                                (${{ number_format($priceUSD, 2) }} USD / {{ __('month') }})
                            </span>
                        </div>
                    </div>

                    <!-- Dates & Period Box -->
                    <div style="display: flex; gap: 20px; background: var(--bg-surface-subtle); padding: 14px 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                        <div>
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px;">📅 {{ __('Start Date') }}</div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); font-family: monospace;">{{ $startDate ? $startDate->format('Y-m-d') : '—' }}</div>
                        </div>
                        <div style="width: 1px; background: var(--border-color);"></div>
                        <div>
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px;">🔄 {{ __('Renewal / End Date') }}</div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--brand-forest); font-family: monospace;">{{ $endDate ? $endDate->format('Y-m-d') : '—' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Plan Details & Limits Breakdown -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 20px;">
                    <!-- User Capacity -->
                    <div class="kpi-card" style="padding: 14px; margin-bottom: 0; border-color: {{ $isSeatsExceeded ? '#D96B5F' : 'var(--border-color)' }};">
                        <div class="kpi-header">
                            <span class="kpi-title">{{ __('User Capacity') }}</span>
                            <div class="kpi-icon-box">👥</div>
                        </div>
                        <div class="kpi-value" style="font-size: 18px; color: {{ $isSeatsExceeded ? '#D96B5F' : 'inherit' }};">
                            {{ $usedSeats }} / {{ $isUnlimitedSeats ? __('Unlimited') : $seatLimit . ' ' . __('Seats') }}
                            @if($isSeatsExceeded)
                                <div style="font-size: 10px; font-weight: 800; color: #D96B5F; margin-top: 4px;">⚠️ {{ __('Exceeded') }}</div>
                            @endif
                        </div>
                    </div>

                    <!-- Meeting Rooms -->
                    <div class="kpi-card" style="padding: 14px; margin-bottom: 0; border-color: {{ $isRoomsExceeded ? '#D96B5F' : 'var(--border-color)' }};">
                        <div class="kpi-header">
                            <span class="kpi-title">{{ __('Meeting Rooms') }}</span>
                            <div class="kpi-icon-box">🏢</div>
                        </div>
                        <div class="kpi-value" style="font-size: 18px; color: {{ $isRoomsExceeded ? '#D96B5F' : 'inherit' }};">
                            {{ $usedRooms }} / {{ $roomLimit === 0 ? __('Unlimited') : $roomLimit . ' ' . __('Rooms') }}
                            @if($isRoomsExceeded)
                                <div style="font-size: 10px; font-weight: 800; color: #D96B5F; margin-top: 4px;">⚠️ {{ __('Exceeded Limit') }}</div>
                            @endif
                        </div>
                    </div>

                    <!-- Office Branches -->
                    <div class="kpi-card" style="padding: 14px; margin-bottom: 0; border-color: {{ $isOfficesExceeded ? '#D96B5F' : 'var(--border-color)' }};">
                        <div class="kpi-header">
                            <span class="kpi-title">{{ __('Office Branches') }}</span>
                            <div class="kpi-icon-box">📍</div>
                        </div>
                        <div class="kpi-value" style="font-size: 18px; color: {{ $isOfficesExceeded ? '#D96B5F' : 'inherit' }};">
                            {{ $usedOffices }} / {{ $maxOffices === 0 ? __('Unlimited') : $maxOffices . ' ' . __('Branch') }}
                        </div>
                    </div>

                    <!-- Guest Links -->
                    <div class="kpi-card" style="padding: 14px; margin-bottom: 0;">
                        <div class="kpi-header">
                            <span class="kpi-title">{{ __('Guest Links') }}</span>
                            <div class="kpi-icon-box">🔗</div>
                        </div>
                        <div class="kpi-value" style="font-size: 18px;">
                            {{ $usedGuests }} / {{ $maxGuests === 0 ? __('Unlimited') : $maxGuests . ' ' . __('Links') }}
                        </div>
                    </div>

                    <!-- Cloud Storage -->
                    <div class="kpi-card" style="padding: 14px; margin-bottom: 0;">
                        <div class="kpi-header">
                            <span class="kpi-title">{{ __('Cloud Storage') }}</span>
                            <div class="kpi-icon-box">💾</div>
                        </div>
                        <div class="kpi-value" style="font-size: 18px;">
                            {{ $storageLimit === 0 ? __('Unlimited') : $storageLimit . ' GB' }}
                        </div>
                    </div>
                </div>

                <!-- Seat Progress Bar -->
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; margin-bottom: 6px;">
                        <span style="color: var(--text-secondary);">{{ __('Seat Utilization') }}</span>
                        <span style="color: {{ $seatPercent > 90 ? '#D96B5F' : 'var(--brand-forest)' }};">{{ $seatPercent }}% {{ __('Consumed') }}</span>
                    </div>
                    <div class="progress-bar-bg" style="background: var(--bg-surface-subtle); height: 8px; border-radius: 9999px; overflow: hidden; border: 1px solid var(--border-color);">
                        <div class="progress-bar-fill" style="width: {{ $seatPercent }}%; background: {{ $seatPercent > 90 ? '#D96B5F' : 'var(--accent-gradient)' }}; height: 100%; border-radius: 9999px; transition: width 0.4s ease;"></div>
                    </div>
                </div>
            </div>

            <!-- Available Upgrade Plans Grid -->
            <div id="available-plans-section">
                <h3 style="font-size: 18px; font-weight: 900; margin-bottom: 16px; color: var(--text-primary);">{{ __('Available Subscription Plans') }}</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
                    @foreach($allPlans as $p)
                    @php
                        $isCurrent = ($organization->plan_id == $p->id);
                        $pPriceUSD = (float)$p->price;
                        $pPriceSAR = round($pPriceUSD * 3.75, 2);
                        $isPaid = $pPriceUSD > 0;
                    @endphp
                    <div class="card plan-selection-card" style="padding: 24px; border-radius: var(--radius-xl); border: 2px solid {{ $isCurrent ? 'var(--brand-forest)' : 'var(--border-color)' }}; position: relative; display: flex; flex-direction: column; justify-content: space-between; box-shadow: {{ $isCurrent ? 'var(--shadow-hover)' : 'var(--shadow-card)' }}; background: var(--bg-surface);">
                        @if($isCurrent)
                            <div style="position: absolute; top: -12px; right: 20px; background: var(--brand-forest); color: white; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 800; text-transform: uppercase; box-shadow: 0 4px 10px rgba(36,92,58,0.3);">
                                ⭐ {{ __('Current Active') }}
                            </div>
                        @endif
                        <div>
                            <div style="font-size: 12px; font-weight: 800; color: var(--brand-forest); text-transform: uppercase; margin-bottom: 6px;">{{ $p->slug }}</div>
                            <h4 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin-bottom: 10px;">{{ $p->name }}</h4>
                            <div style="margin-bottom: 16px;">
                                <span style="font-size: 28px; font-weight: 900; color: var(--text-primary);">
                                    {{ number_format($pPriceSAR, 2) }} <span style="font-size: 13px; font-weight: 700; color: var(--text-secondary);">SAR</span>
                                </span>
                                <span style="font-size: 12px; color: var(--text-muted);">
                                    (${{ number_format($pPriceUSD, 2) }} / {{ __('mo') }})
                                </span>
                            </div>
                            <ul style="list-style: none; padding: 0; margin: 0 0 20px 0; font-size: 13px; color: var(--text-secondary); display: flex; flex-direction: column; gap: 8px;">
                                <li style="display: flex; align-items: center; gap: 8px;">
                                    <span>👥</span> <strong>{{ $p->seat_limit === 0 ? __('Unlimited') : $p->seat_limit }}</strong> {{ __('Team Members') }}
                                </li>
                                <li style="display: flex; align-items: center; gap: 8px;">
                                    <span>🏢</span> <strong>{{ $p->room_limit === 0 ? __('Unlimited') : $p->room_limit }}</strong> {{ __('Meeting Rooms') }}
                                </li>
                                <li style="display: flex; align-items: center; gap: 8px;">
                                    <span>📍</span> <strong>{{ ($p->max_offices ?? 1) === 0 ? __('Unlimited') : ($p->max_offices ?? 1) }}</strong> {{ __('Office Branches') }}
                                </li>
                                <li style="display: flex; align-items: center; gap: 8px;">
                                    <span>🔗</span> <strong>{{ ($p->max_guest_invitations ?? 5) === 0 ? __('Unlimited') : ($p->max_guest_invitations ?? 5) }}</strong> {{ __('Guest Meeting Links') }}
                                </li>
                                <li style="display: flex; align-items: center; gap: 8px;">
                                    <span>💾</span> <strong>{{ $p->storage_limit_gb === 0 ? __('Unlimited') : $p->storage_limit_gb . ' GB' }}</strong> {{ __('Storage') }}
                                </li>
                            </ul>
                        </div>

                        @if($isCurrent)
                            <button disabled class="tactile-btn btn-secondary" style="width: 100%; padding: 12px; opacity: 0.6; cursor: not-allowed; justify-content: center;">
                                ✓ {{ __('Current Plan') }}
                            </button>
                        @elseif($isPaid)
                            <a href="{{ route('subscription.payment', $p->id) }}" class="tactile-btn btn-primary" style="width: 100%; padding: 12px; font-weight: 800; text-align: center; text-decoration: none; justify-content: center;">
                                💳 {{ __('Subscribe & Bank Transfer') }}
                            </a>
                        @else
                            <form method="POST" action="{{ route('organization.upgrade_plan') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $p->id }}">
                                <button type="submit" class="tactile-btn btn-primary" style="width: 100%; padding: 12px; font-weight: 800; justify-content: center;">
                                    🚀 {{ __('Switch to') }} {{ $p->name }}
                                </button>
                            </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

