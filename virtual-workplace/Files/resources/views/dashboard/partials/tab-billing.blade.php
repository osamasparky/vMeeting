<div id="tab-billing" class="tab-view">
    <div class="page-header" style="margin-bottom: 24px;">
        <h1 class="page-title" style="font-size: 22px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="font-size: 24px; color: var(--ula-highlight-default);">credit_card</span>
            <span>{{ __('Billing & Subscription') }}</span>
        </h1>
        <p class="page-subtitle" style="font-size: 13px; color: var(--ula-text-secondary);">{{ __('Manage your plan tier, seat capacity, renewal period, and workspace upgrade.') }}</p>
    </div>

    <!-- Pending Subscription Request Banner -->
    @if(isset($pendingSubscriptionRequest) && $pendingSubscriptionRequest)
    <div class="card" style="margin-bottom: 24px; border-radius: var(--ula-radius-xl); padding: 22px; border: 2px solid var(--ula-gold-400); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(211, 165, 83, 0.15); color: var(--ula-gold-600); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <span class="material-symbols-rounded">hourglass_top</span>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--ula-gold-600); text-transform: uppercase;">{{ __('Pending Wire Transfer Approval') }}</span>
                        <x-badge variant="scheduled">{{ __('Under SuperAdmin Review') }}</x-badge>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 800; color: var(--ula-text-primary); margin: 0;">
                        {{ __('Upgrade to') }} {{ $pendingSubscriptionRequest->plan?->name ?? __('Plan') }} — {{ number_format($pendingSubscriptionRequest->amount, 2) }} {{ $pendingSubscriptionRequest->currency }}
                    </h3>
                    <div style="font-size: 12px; color: var(--ula-text-secondary); margin-top: 4px; display: flex; gap: 14px; flex-wrap: wrap;">
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">account_balance</span>
                            <strong>{{ $pendingSubscriptionRequest->bank_name }}</strong>
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">receipt_long</span>
                            <span>{{ __('Ref') }}: <strong style="font-family: 'IBM Plex Mono', monospace;">{{ $pendingSubscriptionRequest->transfer_reference }}</strong></span>
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">schedule</span>
                            <span>{{ $pendingSubscriptionRequest->created_at->diffForHumans() }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <x-btn variant="outline" size="sm" href="{{ route('subscription.payment', $pendingSubscriptionRequest->plan_id) }}" icon="visibility">
                    {{ __('View Transfer Details') }}
                </x-btn>
                <form method="POST" action="{{ route('subscription.payment.cancel', $pendingSubscriptionRequest->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to cancel this pending subscription request?') }}');" style="margin: 0;">
                    @csrf
                    <x-btn variant="danger" size="sm" type="submit" icon="close">
                        {{ __('Cancel') }}
                    </x-btn>
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
    <div class="card" style="margin-bottom: 24px; border-radius: var(--ula-radius-xl); padding: 18px 24px; border: 2px solid var(--ula-status-danger); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(201, 116, 58, 0.15); color: var(--ula-status-danger); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <span class="material-symbols-rounded">warning</span>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-status-danger); margin: 0 0 4px 0;">
                        {{ __('Plan Limit Exceeded') }}
                    </h3>
                    <p style="font-size: 13px; color: var(--ula-text-secondary); margin: 0;">
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
            <x-btn variant="danger" size="md" href="#available-plans-section" icon="rocket_launch">
                {{ __('Upgrade Plan Now') }}
            </x-btn>
        </div>
    </div>
    @endif

    <!-- Current Plan Card -->
    <div class="card" style="margin-bottom: 28px; border-radius: var(--ula-radius-xl); padding: 24px; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm); background: var(--ula-surface-card);">
        <!-- Top Row: Plan info, SAR/USD Price, and Status -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid var(--ula-border-subtle);">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--ula-palm-900); text-transform: uppercase; letter-spacing: 0.5px;">{{ __('Current Plan') }}</span>
                    <x-badge variant="live">{{ ucfirst($status) }}</x-badge>
                    @if($isAnyExceeded)
                        <x-badge variant="attention">{{ __('Limit Exceeded') }}</x-badge>
                    @endif
                </div>
                <h2 style="font-size: 26px; font-weight: 800; color: var(--ula-text-primary); margin: 4px 0;">{{ $currentPlan->name ?? __('Free Tier') }}</h2>
                <div style="display: flex; align-items: baseline; gap: 10px; margin-top: 6px;">
                    <span style="font-size: 24px; font-weight: 800; color: var(--ula-palm-900); font-family: 'IBM Plex Mono', monospace;">
                        {{ number_format($priceSAR, 2) }} <span style="font-size: 14px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('SAR') }}</span>
                    </span>
                    <span style="font-size: 13px; font-weight: 500; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                        (${{ number_format($priceUSD, 2) }} USD / {{ __('month') }})
                    </span>
                </div>
            </div>

            <!-- Dates & Period Box -->
            <div style="display: flex; gap: 20px; background: var(--ula-sand-100); padding: 14px 20px; border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                <div>
                    <div style="font-size: 10px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; margin-bottom: 2px; display: flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-rounded" style="font-size: 12px;">calendar_today</span>
                        <span>{{ __('Start Date') }}</span>
                    </div>
                    <div style="font-size: 13px; font-weight: 700; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $startDate ? $startDate->format('Y-m-d') : '—' }}</div>
                </div>
                <div style="width: 1px; background: var(--ula-border-subtle);"></div>
                <div>
                    <div style="font-size: 10px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; margin-bottom: 2px; display: flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-rounded" style="font-size: 12px;">event_repeat</span>
                        <span>{{ __('Renewal / End Date') }}</span>
                    </div>
                    <div style="font-size: 13px; font-weight: 700; color: var(--ula-palm-900); font-family: 'IBM Plex Mono', monospace;">{{ $endDate ? $endDate->format('Y-m-d') : '—' }}</div>
                </div>
            </div>
        </div>

        <!-- Plan Details & Limits Breakdown -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 20px;">
            <!-- User Capacity -->
            <div class="kpi-card" style="padding: 14px; margin-bottom: 0; border-color: {{ $isSeatsExceeded ? 'var(--ula-status-danger)' : 'var(--ula-border-subtle)' }};">
                <div class="kpi-header">
                    <span class="kpi-title">{{ __('User Capacity') }}</span>
                    <div class="kpi-icon-box">
                        <span class="material-symbols-rounded">group</span>
                    </div>
                </div>
                <div class="kpi-value" style="font-size: 18px; color: {{ $isSeatsExceeded ? 'var(--ula-status-danger)' : 'inherit' }}; font-family: 'IBM Plex Mono', monospace;">
                    {{ $usedSeats }} / {{ $isUnlimitedSeats ? __('Unlimited') : $seatLimit . ' ' . __('Seats') }}
                    @if($isSeatsExceeded)
                        <div style="font-size: 10px; font-weight: 700; color: var(--ula-status-danger); margin-top: 4px;">{{ __('Exceeded') }}</div>
                    @endif
                </div>
            </div>

            <!-- Meeting Rooms -->
            <div class="kpi-card" style="padding: 14px; margin-bottom: 0; border-color: {{ $isRoomsExceeded ? 'var(--ula-status-danger)' : 'var(--ula-border-subtle)' }};">
                <div class="kpi-header">
                    <span class="kpi-title">{{ __('Meeting Rooms') }}</span>
                    <div class="kpi-icon-box">
                        <span class="material-symbols-rounded">meeting_room</span>
                    </div>
                </div>
                <div class="kpi-value" style="font-size: 18px; color: {{ $isRoomsExceeded ? 'var(--ula-status-danger)' : 'inherit' }}; font-family: 'IBM Plex Mono', monospace;">
                    {{ $usedRooms }} / {{ $roomLimit === 0 ? __('Unlimited') : $roomLimit . ' ' . __('Rooms') }}
                    @if($isRoomsExceeded)
                        <div style="font-size: 10px; font-weight: 700; color: var(--ula-status-danger); margin-top: 4px;">{{ __('Exceeded Limit') }}</div>
                    @endif
                </div>
            </div>

            <!-- Office Branches -->
            <div class="kpi-card" style="padding: 14px; margin-bottom: 0; border-color: {{ $isOfficesExceeded ? 'var(--ula-status-danger)' : 'var(--ula-border-subtle)' }};">
                <div class="kpi-header">
                    <span class="kpi-title">{{ __('Office Branches') }}</span>
                    <div class="kpi-icon-box">
                        <span class="material-symbols-rounded">corporate_fare</span>
                    </div>
                </div>
                <div class="kpi-value" style="font-size: 18px; color: {{ $isOfficesExceeded ? 'var(--ula-status-danger)' : 'inherit' }}; font-family: 'IBM Plex Mono', monospace;">
                    {{ $usedOffices }} / {{ $maxOffices === 0 ? __('Unlimited') : $maxOffices . ' ' . __('Branch') }}
                </div>
            </div>

            <!-- Guest Links -->
            <div class="kpi-card" style="padding: 14px; margin-bottom: 0;">
                <div class="kpi-header">
                    <span class="kpi-title">{{ __('Guest Links') }}</span>
                    <div class="kpi-icon-box">
                        <span class="material-symbols-rounded">link</span>
                    </div>
                </div>
                <div class="kpi-value" style="font-size: 18px; font-family: 'IBM Plex Mono', monospace;">
                    {{ $usedGuests }} / {{ $maxGuests === 0 ? __('Unlimited') : $maxGuests . ' ' . __('Links') }}
                </div>
            </div>

            <!-- Cloud Storage -->
            <div class="kpi-card" style="padding: 14px; margin-bottom: 0;">
                <div class="kpi-header">
                    <span class="kpi-title">{{ __('Cloud Storage') }}</span>
                    <div class="kpi-icon-box">
                        <span class="material-symbols-rounded">cloud</span>
                    </div>
                </div>
                <div class="kpi-value" style="font-size: 18px; font-family: 'IBM Plex Mono', monospace;">
                    {{ $storageLimit === 0 ? __('Unlimited') : $storageLimit . ' GB' }}
                </div>
            </div>
        </div>

        <!-- Seat Progress Bar -->
        <div>
            <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; margin-bottom: 6px;">
                <span style="color: var(--ula-text-secondary);">{{ __('Seat Utilization') }}</span>
                <span style="color: {{ $seatPercent > 90 ? 'var(--ula-status-danger)' : 'var(--ula-palm-900)' }}; font-family: 'IBM Plex Mono', monospace;">{{ $seatPercent }}% {{ __('Consumed') }}</span>
            </div>
            <div class="progress-bar-bg" style="background: var(--ula-sand-200); height: 8px; border-radius: 9999px; overflow: hidden; border: 1px solid var(--ula-border-subtle);">
                <div class="progress-bar-fill" style="width: {{ $seatPercent }}%; background: {{ $seatPercent > 90 ? 'var(--ula-status-danger)' : 'var(--ula-palm-900)' }}; height: 100%; border-radius: 9999px; transition: width 0.4s ease;"></div>
            </div>
        </div>
    </div>

    <!-- Available Upgrade Plans Grid -->
    <div id="available-plans-section">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="color: var(--ula-highlight-default);">verified</span>
            <span>{{ __('Available Subscription Plans') }}</span>
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            @foreach($allPlans as $p)
            @php
                $isCurrent = ($organization->plan_id == $p->id);
                $pPriceUSD = (float)$p->price;
                $pPriceSAR = round($pPriceUSD * 3.75, 2);
                $isPaid = $pPriceUSD > 0;
            @endphp
            <div class="card plan-selection-card" style="padding: 24px; border-radius: var(--ula-radius-xl); border: 2px solid {{ $isCurrent ? 'var(--ula-palm-900)' : 'var(--ula-border-subtle)' }}; position: relative; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--ula-shadow-sm); background: var(--ula-surface-card);">
                @if($isCurrent)
                    <div style="position: absolute; top: -12px; inset-inline-end: 20px; background: var(--ula-palm-900); color: white; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; box-shadow: var(--ula-shadow-sm); display: inline-flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-rounded" style="font-size: 13px;">star</span>
                        <span>{{ __('Current Active') }}</span>
                    </div>
                @endif
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: var(--ula-palm-900); text-transform: uppercase; margin-bottom: 6px;">{{ $p->slug }}</div>
                    <h4 style="font-size: 20px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 10px;">{{ $p->name }}</h4>
                    <div style="margin-bottom: 16px;">
                        <span style="font-size: 28px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
                            {{ number_format($pPriceSAR, 2) }} <span style="font-size: 13px; font-weight: 600; color: var(--ula-text-secondary);">SAR</span>
                        </span>
                        <span style="font-size: 12px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                            (${{ number_format($pPriceUSD, 2) }} / {{ __('mo') }})
                        </span>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0 0 20px 0; font-size: 13px; color: var(--ula-text-secondary); display: flex; flex-direction: column; gap: 8px;">
                        <li style="display: flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-highlight-default);">group</span>
                            <span><strong>{{ $p->seat_limit === 0 ? __('Unlimited') : $p->seat_limit }}</strong> {{ __('Team Members') }}</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-highlight-default);">meeting_room</span>
                            <span><strong>{{ $p->room_limit === 0 ? __('Unlimited') : $p->room_limit }}</strong> {{ __('Meeting Rooms') }}</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-highlight-default);">corporate_fare</span>
                            <span><strong>{{ ($p->max_offices ?? 1) === 0 ? __('Unlimited') : ($p->max_offices ?? 1) }}</strong> {{ __('Office Branches') }}</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-highlight-default);">link</span>
                            <span><strong>{{ ($p->max_guest_invitations ?? 5) === 0 ? __('Unlimited') : ($p->max_guest_invitations ?? 5) }}</strong> {{ __('Guest Meeting Links') }}</span>
                        </li>
                        <li style="display: flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-highlight-default);">cloud</span>
                            <span><strong>{{ $p->storage_limit_gb === 0 ? __('Unlimited') : $p->storage_limit_gb . ' GB' }}</strong> {{ __('Storage') }}</span>
                        </li>
                    </ul>
                </div>

                @if($isCurrent)
                    <button disabled class="nx-btn nx-btn-secondary" style="width: 100%; padding: 12px; opacity: 0.6; cursor: not-allowed; justify-content: center;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">check</span>
                        <span>{{ __('Current Plan') }}</span>
                    </button>
                @elseif($isPaid)
                    <x-btn variant="primary" size="md" href="{{ route('subscription.payment', $p->id) }}" icon="payment" style="width: 100%; justify-content: center;">
                        {{ __('Subscribe & Bank Transfer') }}
                    </x-btn>
                @else
                    <form method="POST" action="{{ route('organization.upgrade_plan') }}">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $p->id }}">
                        <x-btn variant="primary" size="md" type="submit" icon="rocket_launch" style="width: 100%; justify-content: center;">
                            {{ __('Switch to') }} {{ $p->name }}
                        </x-btn>
                    </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

