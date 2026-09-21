<div id="tab-billing" class="tab-view">

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
                        @if($pendingSubscriptionRequest->seats)
                            <span class="badge" style="font-size: 11px; padding: 2px 8px; background: rgba(30, 41, 59, 0.1); border-radius: 6px; font-weight: 700; font-family: 'IBM Plex Mono', monospace;">
                                {{ $pendingSubscriptionRequest->seats }} {{ __('Seats') }}
                            </span>
                        @endif
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
        $isPerSeat = (bool)($currentPlan?->isPerSeat() ?? false);
        $seatLimit = $organization->getEffectiveSeatLimit();
        $roomLimit = $currentPlan?->room_limit ?? 3;
        $maxOffices = $currentPlan?->max_offices ?? 1;
        $maxGuests = $currentPlan?->max_guest_invitations ?? 5;
        $storageLimit = $currentPlan?->storage_limit_gb ?? 1;

        $usedSeats = $members->count();
        $availableSeats = $currentPlan && $currentPlan->isUnlimitedSeats() ? 9999 : max(0, $seatLimit - $usedSeats);
        $usedRooms = $rooms->count();
        $usedOffices = $offices->count();
        $usedGuests = $guestInvitations->count();

        $isSeatsExceeded = ($seatLimit > 0 && !($currentPlan?->isUnlimitedSeats()) && $usedSeats > $seatLimit);
        $isRoomsExceeded = ($roomLimit > 0 && $usedRooms > $roomLimit);
        $isOfficesExceeded = ($maxOffices > 0 && $usedOffices > $maxOffices);
        $isGuestsExceeded = ($maxGuests > 0 && $usedGuests > $maxGuests);
        $isAnyExceeded = ($isSeatsExceeded || $isRoomsExceeded || $isOfficesExceeded || $isGuestsExceeded);

        $isUnlimitedSeats = ($currentPlan && $currentPlan->isUnlimitedSeats());
        $seatPercent = $isUnlimitedSeats ? 20 : min(100, round(($usedSeats / max(1, $seatLimit)) * 100));

        $subscription = $organization->subscription;
        $startDate = $subscription?->created_at ?? $organization->created_at;
        $endDate = $subscription?->current_period_end ?? ($startDate ? (clone $startDate)->addMonth() : now()->addMonth());
        $status = $subscription?->status ?? 'active';

        $unitPriceUSD = (float)($currentPlan->price ?? 0);
        $unitPriceSAR = round($unitPriceUSD * 3.75, 2);

        $totalMonthlyUSD = $isPerSeat ? ($unitPriceUSD * $seatLimit) : $unitPriceUSD;
        $totalMonthlySAR = round($totalMonthlyUSD * 3.75, 2);
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
                        @if($isSeatsExceeded)
                            {{ __('Your workspace currently has :used members, which exceeds your :plan seat quota (:limit seats). Please add more seats or upgrade below.', ['used' => $usedSeats, 'plan' => $currentPlan->name ?? 'Free', 'limit' => $seatLimit]) }}
                        @elseif($isRoomsExceeded)
                            {{ __('Your workspace currently has :used rooms, which exceeds your :plan plan quota (:limit rooms). Please upgrade your subscription plan below.', ['used' => $usedRooms, 'plan' => $currentPlan->name ?? 'Free', 'limit' => $roomLimit]) }}
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
    <div class="card" style="margin-bottom: 28px; border-radius: var(--ula-radius-xl); padding: 26px; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm); background: var(--ula-surface-card);">
        <!-- Top Row: Plan info, Price, Status & Actions -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 22px; padding-bottom: 20px; border-bottom: 1px solid var(--ula-border-subtle);">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--ula-text-primary); text-transform: uppercase; letter-spacing: 0.5px;">{{ __('Current Plan') }}</span>
                    <x-badge variant="live">{{ ucfirst($status) }}</x-badge>
                    @if($isPerSeat)
                        <span class="badge" style="font-size: 11px; padding: 2px 8px; background: rgba(30, 41, 59, 0.08); color: var(--ula-palm-900); border-radius: 6px; font-weight: 700;">
                            {{ __('Seat-Based Plan') }}
                        </span>
                    @endif
                    @if($isAnyExceeded)
                        <x-badge variant="attention">{{ __('Limit Exceeded') }}</x-badge>
                    @endif
                </div>
                <h2 style="font-size: 26px; font-weight: 800; color: var(--ula-text-primary); margin: 4px 0;">{{ $currentPlan->name ?? __('Free Tier') }}</h2>
                
                <!-- Pricing details -->
                <div style="display: flex; align-items: baseline; gap: 10px; margin-top: 6px; flex-wrap: wrap;">
                    <span style="font-size: 24px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
                        {{ number_format($totalMonthlySAR, 2) }} <span style="font-size: 14px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('SAR') }}</span>
                    </span>
                    <span style="font-size: 13px; font-weight: 500; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                        (${{ number_format($totalMonthlyUSD, 2) }} USD / {{ __('month') }})
                    </span>
                    @if($isPerSeat)
                        <span style="font-size: 12px; font-weight: 600; color: var(--ula-palm-900); background: rgba(30, 41, 59, 0.06); padding: 3px 8px; border-radius: 6px;">
                            ${{ number_format($unitPriceUSD, 2) }} / {{ __('person') }} · {{ $seatLimit }} {{ __('seats purchased') }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Dates Box & Manage Seats Button -->
            <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; gap: 16px; background: var(--ula-sand-100); padding: 12px 18px; border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                    <div>
                        <div style="font-size: 10px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; margin-bottom: 2px; display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 12px;">calendar_today</span>
                            <span>{{ __('Start Date') }}</span>
                        </div>
                        <div style="font-size: 12px; font-weight: 700; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $startDate ? $startDate->format('Y-m-d') : '—' }}</div>
                    </div>
                    <div style="width: 1px; background: var(--ula-border-subtle);"></div>
                    <div>
                        <div style="font-size: 10px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; margin-bottom: 2px; display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 12px;">event_repeat</span>
                            <span>{{ __('Renewal Date') }}</span>
                        </div>
                        <div style="font-size: 12px; font-weight: 700; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $endDate ? $endDate->format('Y-m-d') : '—' }}</div>
                    </div>
                </div>

                @if($isPerSeat || ($currentPlan && (float)$currentPlan->price > 0))
                <button type="button" onclick="openManageSeatsModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px; border-radius: var(--ula-radius-md); display: inline-flex; align-items: center; gap: 6px; box-shadow: var(--ula-shadow-sm);">
                    <span class="material-symbols-rounded" style="font-size: 18px;">group_add</span>
                    <span>{{ __('Manage Seats') }}</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Seat Visualization & Overview Widget -->
        <div style="background: var(--ula-surface-page-alt); border-radius: var(--ula-radius-lg); padding: 18px 20px; margin-bottom: 22px; border: 1px solid var(--ula-border-subtle);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: var(--ula-palm-900); font-size: 20px;">airline_seat_recline_normal</span>
                    <strong style="font-size: 14px; color: var(--ula-text-primary);">{{ __('Seat Allocation & Usage') }}</strong>
                </div>
                <div style="display: flex; gap: 16px; font-size: 12px; font-weight: 700; font-family: 'IBM Plex Mono', monospace;">
                    <span style="color: var(--ula-palm-900); display: inline-flex; align-items: center; gap: 4px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--ula-palm-900);"></span>
                        {{ __('Used') }}: <strong>{{ $usedSeats }}</strong>
                    </span>
                    <span style="color: var(--ula-text-primary); display: inline-flex; align-items: center; gap: 4px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--ula-gold-400);"></span>
                        {{ __('Purchased') }}: <strong>{{ $isUnlimitedSeats ? __('Unlimited') : $seatLimit }}</strong>
                    </span>
                    @if(!$isUnlimitedSeats)
                    <span style="color: {{ $availableSeats === 0 ? 'var(--ula-status-danger)' : 'var(--ula-status-success)' }}; display: inline-flex; align-items: center; gap: 4px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $availableSeats === 0 ? 'var(--ula-status-danger)' : 'var(--ula-status-success)' }};"></span>
                        {{ __('Available') }}: <strong>{{ $availableSeats }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <!-- Seat Avatars Grid Preview -->
            @if(!$isUnlimitedSeats && $seatLimit <= 40)
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; align-items: center;">
                @for($i = 0; $i < $seatLimit; $i++)
                    @if($i < $usedSeats)
                        <div title="{{ __('Active Seat (Occupied)') }}" style="width: 28px; height: 28px; border-radius: 50%; background: var(--ula-palm-900); color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; box-shadow: var(--ula-shadow-xs);">
                            <span class="material-symbols-rounded" style="font-size: 16px;">person</span>
                        </div>
                    @else
                        <div title="{{ __('Available Seat (Unassigned)') }}" style="width: 28px; height: 28px; border-radius: 50%; background: transparent; border: 2px dashed var(--ula-border-subtle); color: var(--ula-text-muted); display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">person_outline</span>
                        </div>
                    @endif
                @endfor
            </div>
            @endif

            <!-- Progress Bar -->
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; margin-bottom: 6px;">
                    <span style="color: var(--ula-text-secondary);">{{ __('Utilization Rate') }}</span>
                    <span style="color: {{ $seatPercent > 90 ? 'var(--ula-status-danger)' : 'var(--ula-palm-900)' }}; font-family: 'IBM Plex Mono', monospace;">{{ $seatPercent }}% {{ __('Consumed') }}</span>
                </div>
                <div class="progress-bar-bg" style="background: var(--ula-sand-200); height: 8px; border-radius: 9999px; overflow: hidden; border: 1px solid var(--ula-border-subtle);">
                    <div class="progress-bar-fill" style="width: {{ $seatPercent }}%; background: {{ $seatPercent > 90 ? 'var(--ula-status-danger)' : 'var(--ula-palm-900)' }}; height: 100%; border-radius: 9999px; transition: width 0.4s ease;"></div>
                </div>
            </div>
        </div>

        <!-- Plan Details & Limits Breakdown -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px;">
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
    </div>

    <!-- Available Upgrade Plans Grid -->
    <div id="available-plans-section" style="margin-bottom: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="font-size: 20px; font-weight: 800; color: var(--ula-text-primary); margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: var(--ula-highlight-default);">verified</span>
                    <span>{{ __('Available Subscription Plans') }}</span>
                </h3>
                <p style="font-size: 13px; color: var(--ula-text-secondary); margin: 0;">
                    {{ __('Choose the right plan for your team size. Increase or decrease seats as your team changes.') }}
                </p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
            @foreach($allPlans as $p)
            @php
                $isCurrent = ($organization->plan_id == $p->id);
                $pIsPerSeat = (bool)($p->isPerSeat() ?? false);
                $pMinSeats = $p->getEffectiveMinSeats();
                $pUnitUSD = (float)$p->price;
                $pUnitSAR = round($pUnitUSD * 3.75, 2);
                $isPaid = $pUnitUSD > 0;
                $defaultSeats = $pIsPerSeat ? max($pMinSeats, $isCurrent ? $seatLimit : $pMinSeats) : 1;
                $initialTotalUSD = $pIsPerSeat ? ($pUnitUSD * $defaultSeats) : $pUnitUSD;
                $initialTotalSAR = round($initialTotalUSD * 3.75, 2);
            @endphp
            <div class="card plan-selection-card" 
                 id="plan-card-{{ $p->id }}"
                 data-plan-id="{{ $p->id }}"
                 data-is-per-seat="{{ $pIsPerSeat ? '1' : '0' }}"
                 data-unit-usd="{{ $pUnitUSD }}"
                 data-unit-sar="{{ $pUnitSAR }}"
                 data-min-seats="{{ $pMinSeats }}"
                 style="padding: 26px; border-radius: var(--ula-radius-xl); border: 2px solid {{ $isCurrent ? 'var(--ula-palm-900)' : 'var(--ula-border-subtle)' }}; position: relative; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--ula-shadow-sm); background: var(--ula-surface-card);">
                
                @if($isCurrent)
                    <div style="position: absolute; top: -12px; inset-inline-end: 20px; background: var(--ula-palm-900); color: white; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; box-shadow: var(--ula-shadow-sm); display: inline-flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-rounded" style="font-size: 13px;">star</span>
                        <span>{{ __('Current Active') }}</span>
                    </div>
                @endif

                <div>
                    <!-- Badge & Plan Name -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; font-family: 'IBM Plex Mono', monospace;">{{ $p->slug }}</span>
                        @if($pIsPerSeat)
                            <span class="badge" style="font-size: 10px; font-weight: 700; background: rgba(30, 41, 59, 0.08); color: var(--ula-palm-900); padding: 2px 8px; border-radius: 9999px;">
                                {{ __('Per-Person Pricing') }}
                            </span>
                        @endif
                    </div>
                    <h4 style="font-size: 22px; font-weight: 800; color: var(--ula-text-primary); margin: 0 0 12px 0;">{{ $p->name }}</h4>

                    <!-- Price Header -->
                    <div style="margin-bottom: 16px; padding: 14px; background: var(--ula-surface-page-alt); border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                        @if($pIsPerSeat)
                            <div style="display: flex; align-items: baseline; gap: 6px;">
                                <span style="font-size: 28px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
                                    ${{ number_format($pUnitUSD, 0) }}
                                </span>
                                <span style="font-size: 13px; font-weight: 700; color: var(--ula-text-secondary);">
                                    / {{ __('person / month') }}
                                </span>
                            </div>
                            <div style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px; font-family: 'IBM Plex Mono', monospace;">
                                ({{ number_format($pUnitSAR, 2) }} {{ __('SAR') }} / {{ __('شخص شهرياً') }})
                            </div>
                        @else
                            <div style="display: flex; align-items: baseline; gap: 6px;">
                                <span style="font-size: 28px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
                                    {{ number_format($pUnitSAR, 0) }}
                                </span>
                                <span style="font-size: 13px; font-weight: 700; color: var(--ula-text-secondary);">
                                    {{ __('SAR / month') }}
                                </span>
                            </div>
                            <div style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px; font-family: 'IBM Plex Mono', monospace;">
                                (${{ number_format($pUnitUSD, 0) }} USD / {{ __('شهرياً') }})
                            </div>
                        @endif
                    </div>

                    <!-- Per-Seat Interactive Stepper -->
                    @if($pIsPerSeat)
                    <div style="margin-bottom: 18px; padding: 12px 14px; background: var(--ula-sand-100); border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 12px; font-weight: 700; color: var(--ula-text-primary);">{{ __('People / Seats') }}</span>
                            <span style="font-size: 10px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Min :min people', ['min' => $pMinSeats]) }}</span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                            <button type="button" 
                                    onclick="adjustPlanSeats({{ $p->id }}, -1)"
                                    class="tactile-btn btn-outline"
                                    style="width: 34px; height: 34px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 18px; font-weight: bold;">
                                −
                            </button>
                            <input type="number" 
                                   id="plan-seats-input-{{ $p->id }}" 
                                   value="{{ $defaultSeats }}" 
                                   min="{{ $pMinSeats }}"
                                   onchange="updatePlanTotal({{ $p->id }})"
                                   style="width: 60px; text-align: center; font-weight: 800; font-size: 16px; border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 6px; background: var(--ula-surface-card); color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
                            <button type="button" 
                                    onclick="adjustPlanSeats({{ $p->id }}, 1)"
                                    class="tactile-btn btn-outline"
                                    style="width: 34px; height: 34px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 18px; font-weight: bold;">
                                +
                            </button>
                        </div>
                        
                        <!-- Dynamic Total Box -->
                        <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: baseline;">
                            <span style="font-size: 11px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Estimated Total') }}:</span>
                            <div style="text-align: end;">
                                <strong id="plan-total-sar-{{ $p->id }}" style="font-size: 15px; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
                                    {{ number_format($initialTotalSAR, 2) }} SAR
                                </strong>
                                <div id="plan-total-usd-{{ $p->id }}" style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                                    (${{ number_format($initialTotalUSD, 2) }} USD / {{ __('mo') }})
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Features List -->
                    <ul style="list-style: none; padding: 0; margin: 0 0 20px 0; font-size: 13px; color: var(--ula-text-secondary); display: flex; flex-direction: column; gap: 8px;">
                        <li style="display: flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-highlight-default);">group</span>
                            @if($pIsPerSeat)
                                <span><strong id="plan-feature-seats-{{ $p->id }}">{{ $defaultSeats }}</strong> {{ __('Team Members / Seats') }}</span>
                            @else
                                <span><strong>{{ $p->seat_limit === 0 ? __('Unlimited') : $p->seat_limit }}</strong> {{ __('Team Members') }}</span>
                            @endif
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

                <!-- Action Button -->
                <div>
                    @if($isCurrent)
                        <x-btn variant="secondary" size="md" :disabled="true" icon="check" style="width: 100%; justify-content: center;">
                            {{ __('Current Plan') }}
                        </x-btn>
                    @elseif($isPaid)
                        <a id="plan-checkout-btn-{{ $p->id }}" 
                           href="{{ route('subscription.payment', $p->id) }}?seats={{ $defaultSeats }}&cycle=monthly" 
                           class="tactile-btn btn-primary" 
                           style="width: 100%; justify-content: center; padding: 12px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border-radius: var(--ula-radius-pill);">
                            <span class="material-symbols-rounded" style="font-size: 18px;">payment</span>
                            <span>{{ __('Choose :name', ['name' => $p->name]) }}</span>
                        </a>
                    @else
                        <form method="POST" action="{{ route('organization.upgrade_plan') }}">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $p->id }}">
                            <button type="submit" class="tactile-btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 13px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
                                <span class="material-symbols-rounded" style="font-size: 18px;">rocket_launch</span>
                                <span>{{ __('Switch to') }} {{ $p->name }}</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Plan Comparison Table -->
    <div class="card" style="border-radius: var(--ula-radius-xl); padding: 26px; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-sm); overflow-x: auto;">
        <h4 style="font-size: 18px; font-weight: 800; color: var(--ula-text-primary); margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="color: var(--ula-gold-600);">compare_arrows</span>
            <span>{{ __('Plan Feature Comparison') }}</span>
        </h4>
        <table style="width: 100%; border-collapse: collapse; text-align: start; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--ula-border-subtle); color: var(--ula-text-muted); font-size: 12px; text-transform: uppercase;">
                    <th style="padding: 12px 16px; text-align: start;">{{ __('Feature / Quota') }}</th>
                    @foreach($allPlans as $p)
                        <th style="padding: 12px 16px; text-align: center; color: var(--ula-text-primary); font-weight: 800;">
                            {{ $p->name }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                    <td style="padding: 12px 16px; font-weight: 600; color: var(--ula-text-primary);">{{ __('Pricing Model') }}</td>
                    @foreach($allPlans as $p)
                        <td style="padding: 12px 16px; text-align: center; color: var(--ula-text-secondary);">
                            {{ $p->isPerSeat() ? __('Per Person / Seat') : __('Flat Rate') }}
                        </td>
                    @endforeach
                </tr>
                <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                    <td style="padding: 12px 16px; font-weight: 600; color: var(--ula-text-primary);">{{ __('Price / person / mo') }}</td>
                    @foreach($allPlans as $p)
                        <td style="padding: 12px 16px; text-align: center; font-family: 'IBM Plex Mono', monospace; font-weight: 700; color: var(--ula-text-primary);">
                            {{ $p->isPerSeat() ? '$' . number_format($p->price, 0) : '—' }}
                        </td>
                    @endforeach
                </tr>
                <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                    <td style="padding: 12px 16px; font-weight: 600; color: var(--ula-text-primary);">{{ __('Minimum Seats') }}</td>
                    @foreach($allPlans as $p)
                        <td style="padding: 12px 16px; text-align: center; font-family: 'IBM Plex Mono', monospace;">
                            {{ $p->isPerSeat() ? $p->getEffectiveMinSeats() . ' ' . __('Seats') : '1' }}
                        </td>
                    @endforeach
                </tr>
                <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                    <td style="padding: 12px 16px; font-weight: 600; color: var(--ula-text-primary);">{{ __('Meeting Rooms') }}</td>
                    @foreach($allPlans as $p)
                        <td style="padding: 12px 16px; text-align: center; font-family: 'IBM Plex Mono', monospace;">
                            {{ $p->room_limit === 0 ? __('Unlimited') : $p->room_limit }}
                        </td>
                    @endforeach
                </tr>
                <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                    <td style="padding: 12px 16px; font-weight: 600; color: var(--ula-text-primary);">{{ __('Office Branches') }}</td>
                    @foreach($allPlans as $p)
                        <td style="padding: 12px 16px; text-align: center; font-family: 'IBM Plex Mono', monospace;">
                            {{ ($p->max_offices ?? 1) === 0 ? __('Unlimited') : ($p->max_offices ?? 1) }}
                        </td>
                    @endforeach
                </tr>
                <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                    <td style="padding: 12px 16px; font-weight: 600; color: var(--ula-text-primary);">{{ __('Guest Meeting Links') }}</td>
                    @foreach($allPlans as $p)
                        <td style="padding: 12px 16px; text-align: center; font-family: 'IBM Plex Mono', monospace;">
                            {{ ($p->max_guest_invitations ?? 5) === 0 ? __('Unlimited') : ($p->max_guest_invitations ?? 5) }}
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="padding: 12px 16px; font-weight: 600; color: var(--ula-text-primary);">{{ __('Cloud Storage') }}</td>
                    @foreach($allPlans as $p)
                        <td style="padding: 12px 16px; text-align: center; font-family: 'IBM Plex Mono', monospace;">
                            {{ $p->storage_limit_gb === 0 ? __('Unlimited') : $p->storage_limit_gb . ' GB' }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

</div>

<!-- Manage Seats Modal for Existing Customers -->
<div id="manageSeatsModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 480px; border-radius: var(--ula-radius-xl); padding: 28px; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-lg); animation: modalFadeIn 0.25s ease-out;">
        
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px;">
            <div>
                <h3 style="font-size: 19px; font-weight: 800; color: var(--ula-text-primary); margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: var(--ula-palm-900);">airline_seat_recline_normal</span>
                    <span>{{ __('Manage Workspace Seats') }}</span>
                </h3>
                <p style="font-size: 13px; color: var(--ula-text-secondary); margin: 0;">
                    {{ __('Adjust your active subscription seat count. Seats take effect immediately.') }}
                </p>
            </div>
            <button type="button" onclick="closeManageSeatsModal()" style="background: transparent; border: none; font-size: 22px; cursor: pointer; color: var(--ula-text-muted);">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('subscription.seats.update') }}" id="manageSeatsForm">
            @csrf
            <div style="background: var(--ula-surface-page-alt); border-radius: var(--ula-radius-lg); padding: 16px; margin-bottom: 20px; border: 1px solid var(--ula-border-subtle);">
                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 10px;">
                    <span style="color: var(--ula-text-secondary);">{{ __('Current Plan') }}:</span>
                    <strong style="color: var(--ula-text-primary);">{{ $currentPlan->name ?? 'Free' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 10px;">
                    <span style="color: var(--ula-text-secondary);">{{ __('Current Purchased Seats') }}:</span>
                    <strong style="font-family: 'IBM Plex Mono', monospace; color: var(--ula-text-primary);">{{ $seatLimit }} {{ __('Seats') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 10px;">
                    <span style="color: var(--ula-text-secondary);">{{ __('Currently Active Users') }}:</span>
                    <strong style="font-family: 'IBM Plex Mono', monospace; color: var(--ula-palm-900);">{{ $usedSeats }} {{ __('Users') }}</strong>
                </div>
                <div style="font-size: 11px; color: var(--ula-text-muted); padding-top: 8px; border-top: 1px dashed var(--ula-border-subtle);">
                    ℹ️ {{ __('You cannot reduce your seats below your :count active members.', ['count' => $usedSeats]) }}
                </div>
            </div>

            <!-- Seat Counter -->
            <div style="margin-bottom: 22px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: var(--ula-text-primary); margin-bottom: 8px;">
                    {{ __('New Total Purchased Seats') }}
                </label>
                <div style="display: flex; align-items: center; justify-content: center; gap: 16px;">
                    <button type="button" 
                            onclick="adjustManageModalSeats(-1)"
                            class="tactile-btn btn-outline"
                            style="width: 44px; height: 44px; border-radius: 12px; font-size: 22px; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                        −
                    </button>
                    <input type="number" 
                           id="modal-seats-input" 
                           name="seats"
                           value="{{ $seatLimit }}" 
                           min="{{ max(1, $usedSeats) }}"
                           onchange="updateManageModalTotal()"
                           style="width: 100px; height: 44px; text-align: center; font-size: 20px; font-weight: 800; border: 2px solid var(--ula-border-subtle); border-radius: 12px; background: var(--ula-surface-card); color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
                    <button type="button" 
                            onclick="adjustManageModalSeats(1)"
                            class="tactile-btn btn-outline"
                            style="width: 44px; height: 44px; border-radius: 12px; font-size: 22px; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                        +
                    </button>
                </div>
            </div>

            <!-- Warning if user attempts to go below active users -->
            <div id="modal-seats-warning" style="display: none; margin-bottom: 16px; padding: 10px 14px; background: rgba(201, 116, 58, 0.12); border: 1px solid var(--ula-status-danger); border-radius: 8px; font-size: 12px; color: var(--ula-status-danger); font-weight: 600;">
                {{ __('You currently have :count active users. You must remove users before reducing your seats below :count.', ['count' => $usedSeats]) }}
            </div>

            <!-- Dynamic Price Preview -->
            <div style="background: var(--ula-sand-100); border-radius: var(--ula-radius-lg); padding: 14px 18px; margin-bottom: 22px; border: 1px solid var(--ula-border-subtle);">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                    <span style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('New Monthly Price') }}:</span>
                    <strong id="modal-new-price" style="font-size: 17px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
                        {{ number_format($totalMonthlySAR, 2) }} SAR
                    </strong>
                </div>
                <div id="modal-diff-container" style="display: flex; justify-content: space-between; align-items: baseline; font-size: 12px; color: var(--ula-text-muted);">
                    <span>{{ __('Seat Difference') }}:</span>
                    <span id="modal-seat-diff" style="font-weight: 700; font-family: 'IBM Plex Mono', monospace;">0 {{ __('seats') }}</span>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeManageSeatsModal()" class="tactile-btn btn-secondary" style="padding: 10px 18px; border-radius: var(--ula-radius-pill); font-size: 13px;">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" id="modal-submit-btn" class="tactile-btn btn-primary" style="padding: 10px 22px; border-radius: var(--ula-radius-pill); font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">check</span>
                    <span>{{ __('Confirm Seat Change') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // State & constants for live JS calculations
    const ACTIVE_MEMBERS_FLOOR = {{ $usedSeats }};
    const CURRENT_SEATS = {{ $seatLimit }};
    const CURRENT_PLAN_UNIT_USD = {{ $unitPriceUSD }};
    const CURRENT_PLAN_IS_PER_SEAT = {{ $isPerSeat ? 1 : 0 }};
    const SAR_RATE = 3.75;

    function adjustPlanSeats(planId, delta) {
        const input = document.getElementById(`plan-seats-input-${planId}`);
        if (!input) return;
        const min = parseInt(input.getAttribute('min')) || 1;
        let val = parseInt(input.value) || min;
        val += delta;
        if (val < min) val = min;
        input.value = val;
        updatePlanTotal(planId);
    }

    function updatePlanTotal(planId) {
        const card = document.getElementById(`plan-card-${planId}`);
        const input = document.getElementById(`plan-seats-input-${planId}`);
        if (!card || !input) return;

        const isPerSeat = card.getAttribute('data-is-per-seat') === '1';
        const unitUSD = parseFloat(card.getAttribute('data-unit-usd')) || 0;
        const unitSAR = parseFloat(card.getAttribute('data-unit-sar')) || (unitUSD * SAR_RATE);
        const minSeats = parseInt(card.getAttribute('data-min-seats')) || 1;

        let seats = parseInt(input.value) || minSeats;
        if (seats < minSeats) {
            seats = minSeats;
            input.value = seats;
        }

        const totalUSD = isPerSeat ? (unitUSD * seats) : unitUSD;
        const totalSAR = isPerSeat ? (unitSAR * seats) : unitSAR;

        // Update displayed labels
        const totalSarEl = document.getElementById(`plan-total-sar-${planId}`);
        const totalUsdEl = document.getElementById(`plan-total-usd-${planId}`);
        const featureSeatsEl = document.getElementById(`plan-feature-seats-${planId}`);
        const checkoutBtn = document.getElementById(`plan-checkout-btn-${planId}`);

        if (totalSarEl) totalSarEl.innerText = `${totalSAR.toFixed(2)} SAR`;
        if (totalUsdEl) totalUsdEl.innerText = `($${totalUSD.toFixed(2)} USD / {{ __('mo') }})`;
        if (featureSeatsEl) featureSeatsEl.innerText = seats;

        if (checkoutBtn) {
            checkoutBtn.href = `{{ url('/billing/payment') }}/${planId}?seats=${seats}&cycle=monthly`;
        }
    }

    function openManageSeatsModal() {
        const modal = document.getElementById('manageSeatsModal');
        if (modal) {
            modal.style.display = 'flex';
            updateManageModalTotal();
        }
    }

    function closeManageSeatsModal() {
        const modal = document.getElementById('manageSeatsModal');
        if (modal) modal.style.display = 'none';
    }

    function adjustManageModalSeats(delta) {
        const input = document.getElementById('modal-seats-input');
        if (!input) return;
        let val = parseInt(input.value) || CURRENT_SEATS;
        val += delta;
        if (val < ACTIVE_MEMBERS_FLOOR) {
            showModalFloorWarning();
            val = ACTIVE_MEMBERS_FLOOR;
        } else {
            hideModalFloorWarning();
        }
        input.value = val;
        updateManageModalTotal();
    }

    function showModalFloorWarning() {
        const warn = document.getElementById('modal-seats-warning');
        if (warn) warn.style.display = 'block';
    }

    function hideModalFloorWarning() {
        const warn = document.getElementById('modal-seats-warning');
        if (warn) warn.style.display = 'none';
    }

    function updateManageModalTotal() {
        const input = document.getElementById('modal-seats-input');
        if (!input) return;

        let seats = parseInt(input.value) || CURRENT_SEATS;
        if (seats < ACTIVE_MEMBERS_FLOOR) {
            showModalFloorWarning();
            seats = ACTIVE_MEMBERS_FLOOR;
            input.value = seats;
        } else {
            hideModalFloorWarning();
        }

        const newTotalUSD = CURRENT_PLAN_IS_PER_SEAT ? (CURRENT_PLAN_UNIT_USD * seats) : CURRENT_PLAN_UNIT_USD;
        const newTotalSAR = newTotalUSD * SAR_RATE;

        const priceEl = document.getElementById('modal-new-price');
        const diffEl = document.getElementById('modal-seat-diff');
        const submitBtn = document.getElementById('modal-submit-btn');

        if (priceEl) priceEl.innerText = `${newTotalSAR.toFixed(2)} SAR ($${newTotalUSD.toFixed(2)} USD)`;

        const diff = seats - CURRENT_SEATS;
        if (diffEl) {
            if (diff > 0) {
                diffEl.innerText = `+${diff} {{ __('additional seats') }}`;
                diffEl.style.color = 'var(--ula-palm-900)';
            } else if (diff < 0) {
                diffEl.innerText = `${diff} {{ __('seats reduction') }}`;
                diffEl.style.color = 'var(--ula-status-danger)';
            } else {
                diffEl.innerText = `0 {{ __('change') }}`;
                diffEl.style.color = 'var(--ula-text-muted)';
            }
        }
    }
</script>
