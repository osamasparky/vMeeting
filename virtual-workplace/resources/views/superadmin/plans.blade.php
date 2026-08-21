@extends('superadmin.layout')

@section('title', __('Subscription Plans'))
@section('page_title', __('Subscription Plans'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">💎 {{ __('Plans & Pricing Tiers') }}</h2>
        <p style="font-size: 13px; color: var(--text-secondary);">
            {{ __('Configure seat capacity tiers, room limits, and recurring pricing for companies') }}
        </p>
    </div>
    <button onclick="openCreatePlanModal()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: 13px;">
        ✨ {{ __('Create New Plan') }}
    </button>
</div>

<!-- Plan Cards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 32px;">
    @foreach($plans as $plan)
    <div class="panel-card" style="position: relative; display: flex; flex-direction: column; justify-content: space-between; border-radius: var(--radius-xl); padding: 24px; border-top: 4px solid var(--brand-forest); box-shadow: var(--shadow-card);">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
                <div>
                    <h3 style="font-size: 20px; font-weight: 900; color: var(--text-primary);">💎 {{ $plan->name }}</h3>
                    <span style="font-size: 11px; color: var(--text-muted); font-weight: 700; font-family: monospace;">{{ $plan->slug }}</span>
                </div>
                <span class="badge-status {{ $plan->is_active ? 'badge-active' : 'badge-suspended' }}">
                    {{ $plan->is_active ? __('Active') : __('Draft') }}
                </span>
            </div>

            <div style="margin: 16px 0; padding: 16px; background: var(--bg-surface-subtle); border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                <div style="font-size: 30px; font-weight: 900; color: var(--brand-forest);">
                    ${{ number_format($plan->price, 2) }}
                    <span style="font-size: 13px; font-weight: 700; color: var(--text-muted);">/mo</span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary); font-weight: 700;">👥 {{ __('Max Seats (Users)') }}:</span>
                    <strong style="color: var(--text-primary);">{{ $plan->seat_limit === 0 ? __('Unlimited') : $plan->seat_limit }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary); font-weight: 700;">🚪 {{ __('Max Rooms') }}:</span>
                    <strong style="color: var(--text-primary);">{{ $plan->room_limit === 0 ? __('Unlimited') : $plan->room_limit }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary); font-weight: 700;">💾 {{ __('Storage Limit (GB)') }}:</span>
                    <strong style="color: var(--text-primary);">{{ $plan->storage_limit_gb === 0 ? __('Unlimited') : $plan->storage_limit_gb . ' GB' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary); font-weight: 700;">🏢 {{ __('Active Companies') }}:</span>
                    <strong style="color: var(--brand-forest);">{{ $plan->organizations_count }}</strong>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 16px;">
            <button
                onclick='openEditPlanModal(@json($plan))'
                class="tactile-btn btn-secondary"
                style="flex: 1; justify-content: center; padding: 8px 12px; font-size: 12px;"
            >
                ✏️ {{ __('Edit Plan') }}
            </button>
            @if($plan->organizations_count === 0 && !in_array($plan->slug, ['free', 'starter', 'business', 'enterprise']))
            <form method="POST" action="{{ route('superadmin.plans.delete', $plan) }}" onsubmit="return confirm('Are you sure you want to delete this plan?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="tactile-btn" style="color: #D96B5F; padding: 8px 12px; font-size: 12px;">🗑️</button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

<!-- Plan Modal (Create & Edit) -->
<div id="planModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: 24px; padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);" id="planModalTitle">✨ {{ __('Create New Plan') }}</h3>
            <button onclick="closePlanModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
        </div>

        <form id="planForm" method="POST" action="{{ route('superadmin.plans.store') }}">
            @csrf
            <div id="planMethodContainer"></div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Plan Name') }}</label>
                    <input type="text" id="inputPlanName" name="name" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Monthly Price ($)') }}</label>
                    <input type="number" step="0.01" id="inputPlanPrice" name="price" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Seats (Users)') }}</label>
                    <input type="number" id="inputPlanSeats" name="seat_limit" required placeholder="0 = Unlimited" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Max Rooms') }}</label>
                    <input type="number" id="inputPlanRooms" name="room_limit" required placeholder="0 = Unlimited" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Storage (GB)') }}</label>
                    <input type="number" id="inputPlanStorage" name="storage_limit_gb" required placeholder="0 = Unlimited" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Features (Comma-separated)') }}</label>
                <input type="text" id="inputPlanFeatures" name="features" placeholder="basic_chat, basic_audio, video, screen_share, analytics" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; cursor: pointer; color: var(--text-primary);">
                    <input type="checkbox" id="inputPlanActive" name="is_active" value="1" checked style="accent-color: var(--brand-forest); width: 16px; height: 16px;">
                    <span>{{ __('Plan is active and available for registration') }}</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closePlanModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary">
                    💾 {{ __('Save Plan') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openCreatePlanModal() {
        document.getElementById('planModalTitle').textContent = '✨ Create New Subscription Plan';
        document.getElementById('planForm').action = '{{ route("superadmin.plans.store") }}';
        document.getElementById('planMethodContainer').innerHTML = '';
        document.getElementById('inputPlanName').value = '';
        document.getElementById('inputPlanPrice').value = '19.99';
        document.getElementById('inputPlanSeats').value = '15';
        document.getElementById('inputPlanRooms').value = '5';
        document.getElementById('inputPlanStorage').value = '5';
        document.getElementById('inputPlanFeatures').value = 'basic_chat, basic_audio, video, screen_share';
        document.getElementById('inputPlanActive').checked = true;
        document.getElementById('planModal').style.display = 'flex';
    }

    function openEditPlanModal(plan) {
        document.getElementById('planModalTitle').textContent = `✏️ Edit Plan: ${plan.name}`;
        document.getElementById('planForm').action = `/superadmin/plans/${plan.id}`;
        document.getElementById('planMethodContainer').innerHTML = '@method("PUT")';
        document.getElementById('inputPlanName').value = plan.name;
        document.getElementById('inputPlanPrice').value = plan.price;
        document.getElementById('inputPlanSeats').value = plan.seat_limit;
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
