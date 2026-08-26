@extends('superadmin.layout')

@section('title', __('Global Feature Flags'))
@section('page_title', __('System — Global Feature Flags (إدارة ميزات المنصة)'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">
                🚩 {{ __('Global Platform Feature Flags') }}
            </h2>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                {{ __('Enable or disable platform-wide modules (Spatial audio, AI generator, Whiteboards, Kanban, Time tracking).') }}
            </p>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(79, 155, 95, 0.15); border: 1px solid rgba(79, 155, 95, 0.35); color: #4F9B5F; padding: 14px 18px; border-radius: var(--radius-md); font-size: 13px; font-weight: 800;">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="panel-card" style="border-radius: var(--radius-xl); padding: 24px;">
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Feature Name') }}</th>
                        <th>{{ __('Key & Category') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th style="text-align: center;">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($flags as $flag)
                        <tr>
                            <td>
                                <strong style="font-size: 14px; color: var(--text-primary); display: block;">
                                    {{ $flag->name_en }}
                                </strong>
                                <span style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                                    {{ $flag->name_ar }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 11px; font-family: monospace; font-weight: 700; color: var(--brand-forest); display: block;">
                                    {{ $flag->flag_key }}
                                </span>
                                <span class="nav-badge-pill" style="font-size: 10px;">
                                    {{ ucfirst($flag->category) }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: var(--text-muted); line-height: 1.5; display: block; max-width: 400px;">
                                    {{ $flag->description_en }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-status {{ $flag->is_enabled ? 'badge-active' : 'badge-suspended' }}">
                                    {{ $flag->is_enabled ? __('Enabled') : __('Disabled') }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <form method="POST" action="{{ route('superadmin.features.toggle', $flag) }}" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="tactile-btn" style="padding: 6px 14px; font-size: 12px; {{ $flag->is_enabled ? 'background: rgba(217, 107, 95, 0.15); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);' : 'background: rgba(79, 155, 95, 0.15); color: #4F9B5F; border-color: rgba(79, 155, 95, 0.3);' }}">
                                        {{ $flag->is_enabled ? __('Disable') : __('Enable') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
