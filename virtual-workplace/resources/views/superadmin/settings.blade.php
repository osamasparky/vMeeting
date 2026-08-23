@extends('superadmin.layout')

@section('title', __('System Settings'))
@section('page_title', __('System Settings'))

@section('content')
<form method="POST" action="{{ route('superadmin.settings.update') }}">
    @csrf

    <div class="panel-card" style="border-radius: var(--radius-xl); padding: 28px;">
        <div class="panel-header" style="margin-bottom: 24px;">
            <div class="panel-title">
                <span>🌐</span>
                <span>{{ __('Global SaaS Configuration') }}</span>
            </div>
            <p class="panel-subtitle">{{ __('Configure core platform parameters, default registration tier, and real-time connectivity.') }}</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
            <div>
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                    🏢 {{ __('Platform Name') }}
                </label>
                <input
                    type="text"
                    name="platform_name"
                    value="Virtual Workplace SaaS"
                    style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);"
                >
            </div>

            <div>
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                    💎 {{ __('Default Registration Plan') }}
                </label>
                <select name="default_plan_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}" {{ $p->slug === 'free' ? 'selected' : '' }}>
                            💎 {{ $p->name }} ({{ $p->seat_limit === 0 ? __('Unlimited') : $p->seat_limit }} Users)
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                    ⚡ {{ __('Realtime WebSocket URL') }}
                </label>
                <input
                    type="text"
                    name="ws_url"
                    value="ws://127.0.0.1:8080"
                    style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 14px; color: var(--brand-forest); outline: none; font-size: 13px; font-family: monospace; font-weight: 700; box-shadow: var(--shadow-inset-3d);"
                >
            </div>

            <div>
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                    📡 {{ __('STUN / TURN Server') }}
                </label>
                <input
                    type="text"
                    name="stun_server"
                    value="stun:173.212.248.192:3478"
                    style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-family: monospace; box-shadow: var(--shadow-inset-3d);"
                >
            </div>
        </div>

        <div style="margin-top: 28px; display: flex; justify-content: flex-end;">
            <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 28px; font-size: 13px;">
                💾 {{ __('Save Changes') }}
            </button>
        </div>
    </div>
</form>

<!-- Global System Default Office Blueprint & Room Design (Super Admin Level) -->
<form method="POST" action="{{ route('superadmin.settings.blueprint') }}" enctype="multipart/form-data" style="margin-top: 24px;">
    @csrf
    <div class="panel-card" style="border-radius: var(--radius-xl); padding: 28px;">
        <div class="panel-header" style="margin-bottom: 20px;">
            <div class="panel-title">
                <span>📐</span>
                <span>{{ __('Global System Default Office Blueprint (المخطط الافتراضي للنظام)') }}</span>
            </div>
            <p class="panel-subtitle">
                {{ __('Upload the platform-wide default 3D isometric architectural floorplan. All newly registered organizations and default workspaces will automatically inherit this blueprint design. Company admins can then customize and edit their specific rooms.') }}
            </p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px;">
            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                <div style="width: 140px; height: 100px; border-radius: 10px; overflow: hidden; border: 2px solid var(--border-color); background: #0C1711; display: flex; align-items: center; justify-content: center;">
                    <img src="/images/office_floorplan.jpg" alt="Default Blueprint" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <strong style="font-size: 14px; color: var(--text-primary); display: block; margin-bottom: 4px;">
                        {{ __('Active Default Floorplan Blueprint') }}
                    </strong>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">
                        {{ __('Supported formats: JPG, PNG, WebP (High Resolution Recommended)') }}
                    </div>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/jpg" required style="font-size: 12px; color: var(--text-primary);">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
                <button type="submit" class="tactile-btn btn-primary" style="padding: 10px 22px; font-size: 12px; background: linear-gradient(135deg, #10b981, #059669);">
                    🚀 {{ __('Update Global Default Blueprint (تحديث المخطط الافتراضي العام)') }}
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
