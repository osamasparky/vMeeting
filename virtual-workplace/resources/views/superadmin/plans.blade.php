@extends('superadmin.layout')

@section('title', __('Subscription Plans'))
@section('page_title', __('Subscription Plans'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--nx-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="color: var(--nx-accent); font-size: 24px;">diamond</span>
            <span>{{ __('Plans & Pricing Tiers') }}</span>
        </h2>
        <p style="font-size: 13px; color: var(--nx-text-secondary); margin: 0;">
            {{ __('Configure seat capacity tiers, room limits, and recurring pricing for companies') }}
        </p>
    </div>
    <button onclick="openCreatePlanModal()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: 13px; border-radius: var(--nx-radius-full, 9999px); display: inline-flex; align-items: center; gap: 6px;">
        <span class="material-symbols-rounded" style="font-size: 18px;">add_circle</span>
        <span>{{ __('Create New Plan') }}</span>
    </button>
</div>

<!-- Plan Cards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 32px;">
    @foreach($plans as $plan)
    <div class="panel-card" style="position: relative; display: flex; flex-direction: column; justify-content: space-between; border-radius: var(--nx-radius-xl, 20px); padding: 24px; background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-top: 4px solid var(--nx-palm-900); box-shadow: var(--nx-shadow-sm);">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: var(--nx-text-primary); margin: 0; display: flex; align-items: center; gap: 6px;">
                        <span>{{ $plan->name }}</span>
                    </h3>
                    <span style="font-size: 11px; color: var(--nx-text-muted); font-weight: 600; font-family: 'IBM Plex Mono', monospace;">{{ $plan->slug }}</span>
                </div>
                @if($plan->is_active)
                    <span class="badge-status badge-active" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(60,107,76,0.12); color: var(--nx-status-live); font-weight: 700;">
                        <span class="material-symbols-rounded" style="font-size: 12px;">check_circle</span>
                        <span>{{ __('Active') }}</span>
                    </span>
                @else
                    <span class="badge-status badge-suspended" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(217,107,95,0.12); color: var(--nx-status-attention); font-weight: 700;">
                        <span class="material-symbols-rounded" style="font-size: 12px;">pause_circle</span>
                        <span>{{ __('Draft') }}</span>
                    </span>
                @endif
            </div>

            <div style="margin: 16px 0; padding: 16px; background: var(--nx-bg-surface-subtle, #F4EDE1); border-radius: var(--nx-radius-md, 12px); border: 1px solid var(--nx-border-subtle);">
                <div style="font-size: 28px; font-weight: 800; color: var(--nx-palm-900); font-family: 'IBM Plex Mono', monospace;">
                    ${{ number_format($plan->price, 2) }}
                    <span style="font-size: 13px; font-weight: 600; color: var(--nx-text-muted); font-family: var(--nx-font-family);">/mo</span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--nx-text-secondary); font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--nx-text-muted);">group</span>
                        <span>{{ __('Max Seats (Users)') }}:</span>
                    </span>
                    <strong style="color: var(--nx-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $plan->seat_limit === 0 ? __('Unlimited') : $plan->seat_limit }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--nx-text-secondary); font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--nx-text-muted);">domain</span>
                        <span>{{ __('Max Offices / Branches') }}:</span>
                    </span>
                    <strong style="color: var(--nx-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $plan->max_offices === 0 ? __('Unlimited') : $plan->max_offices }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--nx-text-secondary); font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--nx-text-muted);">meeting_room</span>
                        <span>{{ __('Max Rooms') }}:</span>
                    </span>
                    <strong style="color: var(--nx-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $plan->room_limit === 0 ? __('Unlimited') : $plan->room_limit }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--nx-text-secondary); font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--nx-text-muted);">hard_drive</span>
                        <span>{{ __('Storage Limit (GB)') }}:</span>
                    </span>
                    <strong style="color: var(--nx-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $plan->storage_limit_gb === 0 ? __('Unlimited') : $plan->storage_limit_gb . ' GB' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--nx-text-secondary); font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--nx-text-muted);">apartment</span>
                        <span>{{ __('Active Companies') }}:</span>
                    </span>
                    <strong style="color: var(--nx-palm-900); font-family: 'IBM Plex Mono', monospace;">{{ $plan->organizations_count }}</strong>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 8px; border-top: 1px solid var(--nx-border-subtle); padding-top: 16px;">
            <button
                onclick='openEditPlanModal(@json($plan))'
                class="tactile-btn btn-secondary"
                style="flex: 1; justify-content: center; padding: 8px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;"
            >
                <span class="material-symbols-rounded" style="font-size: 15px;">edit</span>
                <span>{{ __('Edit Plan') }}</span>
            </button>
            @if($plan->organizations_count === 0 && !in_array($plan->slug, ['free', 'starter', 'business', 'enterprise']))
            <form method="POST" action="{{ route('superadmin.plans.delete', $plan) }}" onsubmit="return confirm('Are you sure you want to delete this plan?');" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="tactile-btn" style="color: var(--nx-status-attention); padding: 8px 12px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center;" title="{{ __('Delete Plan') }}">
                    <span class="material-symbols-rounded" style="font-size: 16px;">delete</span>
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

<!-- Plan Modal (Create & Edit) -->
<div id="planModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: var(--nx-radius-xl, 20px); padding: 28px; max-width: 540px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--nx-accent); font-size: 22px;">diamond</span>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--nx-text-primary); margin: 0;" id="planModalTitle">{{ __('Create New Plan') }}</h3>
            </div>
            <button onclick="closePlanModal()" style="background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--nx-text-primary);">
                <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
            </button>
        </div>

        <form id="planForm" method="POST" action="{{ route('superadmin.plans.store') }}">
            @csrf
            <div id="planMethodContainer"></div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Plan Name') }}</label>
                    <input type="text" id="inputPlanName" name="name" required style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--nx-text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Monthly Price ($)') }}</label>
                    <input type="number" step="0.01" id="inputPlanPrice" name="price" required style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--nx-text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Seats') }}</label>
                    <input type="number" id="inputPlanSeats" name="seat_limit" required placeholder="0 = ∞" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: 10px; padding: 10px 10px; color: var(--nx-text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Offices') }}</label>
                    <input type="number" id="inputPlanOffices" name="max_offices" required placeholder="0 = ∞" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: 10px; padding: 10px 10px; color: var(--nx-text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Rooms') }}</label>
                    <input type="number" id="inputPlanRooms" name="room_limit" required placeholder="0 = ∞" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: 10px; padding: 10px 10px; color: var(--nx-text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('GB Storage') }}</label>
                    <input type="number" id="inputPlanStorage" name="storage_limit_gb" required placeholder="0 = ∞" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: 10px; padding: 10px 10px; color: var(--nx-text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--nx-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Features (Comma-separated)') }}</label>
                <input type="text" id="inputPlanFeatures" name="features" placeholder="basic_chat, basic_audio, video, screen_share, analytics" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--nx-text-primary); outline: none; font-size: 13px; font-weight: 500;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; cursor: pointer; color: var(--nx-text-primary);">
                    <input type="checkbox" id="inputPlanActive" name="is_active" value="1" checked style="accent-color: var(--nx-palm-900); width: 16px; height: 16px;">
                    <span>{{ __('Plan is active and available for registration') }}</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closePlanModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                    <span>{{ __('Save Plan') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    function openCreatePlanModal() {
        document.getElementById('planModalTitle').textContent = 'Create New Subscription Plan';
        document.getElementById('planForm').action = '{{ route("superadmin.plans.store") }}';
        document.getElementById('planMethodContainer').innerHTML = '';
        document.getElementById('inputPlanName').value = '';
        document.getElementById('inputPlanPrice').value = '19.99';
        document.getElementById('inputPlanSeats').value = '15';
        document.getElementById('inputPlanOffices').value = '3';
        document.getElementById('inputPlanRooms').value = '10';
        document.getElementById('inputPlanStorage').value = '5';
        document.getElementById('inputPlanFeatures').value = 'basic_chat, basic_audio, video, screen_share';
        document.getElementById('inputPlanActive').checked = true;
        document.getElementById('planModal').style.display = 'flex';
    }

    function openEditPlanModal(plan) {
        document.getElementById('planModalTitle').textContent = `Edit Plan — ${plan.name}`;
        document.getElementById('planForm').action = `/superadmin/plans/${plan.id}`;
        document.getElementById('planMethodContainer').innerHTML = '@method("PUT")';
        document.getElementById('inputPlanName').value = plan.name;
        document.getElementById('inputPlanPrice').value = plan.price;
        document.getElementById('inputPlanSeats').value = plan.seat_limit;
        document.getElementById('inputPlanOffices').value = plan.max_offices ?? 1;
        document.getElementById('inputPlanRooms').value = plan.room_limit;
        document.getElementById('inputPlanStorage').value = plan.storage_limit_gb;
        document.getElementById('inputPlanFeatures').value = (plan.features || []).join(', ');
        document.getElementById('inputPlanActive').checked = !!plan.is_active;
        document.getElementById('planModal').style.display = 'flex';
    }

    function closePlanModal() {
        document.getElementById('planModal').style.display = 'none';
    }
</script>
@endsection
