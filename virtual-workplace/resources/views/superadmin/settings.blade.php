@extends('superadmin.layout')

@section('title', __('System Settings'))
@section('page_title', __('System Settings'))

@section('content')
<form method="POST" action="{{ route('superadmin.settings.update') }}">
    @csrf

    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <span>🌐</span>
                <span>{{ __('Global SaaS Configuration') }}</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">
                    {{ __('Platform Name') }}
                </label>
                <input
                    type="text"
                    name="platform_name"
                    value="Virtual Workplace SaaS"
                    style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;"
                >
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">
                    {{ __('Default Registration Plan') }}
                </label>
                <select name="default_plan_id" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}" {{ $p->slug === 'free' ? 'selected' : '' }}>
                            💎 {{ $p->name }} ({{ $p->seat_limit === 0 ? __('Unlimited') : $p->seat_limit }} Users)
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">
                    {{ __('Realtime WebSocket URL') }}
                </label>
                <input
                    type="text"
                    name="ws_url"
                    value="ws://127.0.0.1:8080"
                    style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; color: var(--text-primary); outline: none; font-size: 13px; font-family: monospace;"
                >
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">
                    {{ __('STUN / TURN Server') }}
                </label>
                <input
                    type="text"
                    name="stun_server"
                    value="stun:stun.l.google.com:19302"
                    style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; color: var(--text-primary); outline: none; font-size: 13px; font-family: monospace;"
                >
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn-action" style="background: var(--brand-teal); border-color: var(--brand-teal); color: white; padding: 10px 22px;">
                💾 {{ __('Save Changes') }}
            </button>
        </div>
    </div>
</form>
@endsection
