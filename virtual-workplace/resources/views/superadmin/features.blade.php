@extends('superadmin.layout')

@section('title', __('Global Feature Flags'))
@section('page_title', __('System Global Feature Flags'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 24px;">flag</span>
                <span>{{ __('Global Platform Feature Flags') }}</span>
            </h2>
            <p style="font-size: 13px; color: var(--ula-text-secondary); margin: 0;">
                {{ __('Enable or disable platform-wide modules (Spatial audio, AI generator, Whiteboards, Kanban, Time tracking).') }}
            </p>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(60, 107, 76, 0.12); border: 1px solid rgba(60, 107, 76, 0.35); color: var(--ula-status-success); padding: 14px 18px; border-radius: var(--ula-radius-md); font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="font-size: 18px;">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 24px; box-shadow: var(--ula-shadow-sm);">
        <div class="data-table-container" style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: start;">
                <thead>
                    <tr style="background: var(--ula-surface-page-alt); border-bottom: 1px solid var(--ula-border-subtle);">
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Feature Name') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Key & Category') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Description') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Status') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($flags as $flag)
                        <tr style="border-bottom: 1px solid var(--ula-border-subtle); transition: background 0.15s ease;">
                            <td style="padding: 14px 18px;">
                                <strong style="font-size: 13px; color: var(--ula-text-primary); display: block;">
                                    {{ $flag->name_en }}
                                </strong>
                                <span style="font-size: 11px; font-weight: 600; color: var(--ula-text-secondary);">
                                    {{ $flag->name_ar }}
                                </span>
                            </td>
                            <td style="padding: 14px 18px;">
                                <span style="font-size: 11px; font-family: 'IBM Plex Mono', monospace; font-weight: 700; color: var(--ula-palm-900); display: block;">
                                    {{ $flag->flag_key }}
                                </span>
                                <span class="nav-badge-pill" style="font-size: 10px; padding: 2px 6px; background: rgba(20,43,36,0.06); color: var(--ula-palm-900);">
                                    {{ ucfirst($flag->category) }}
                                </span>
                            </td>
                            <td style="padding: 14px 18px;">
                                <span style="font-size: 12px; color: var(--ula-text-secondary); line-height: 1.5; display: block; max-width: 400px;">
                                    {{ $flag->description_en }}
                                </span>
                            </td>
                            <td style="padding: 14px 18px;">
                                @if($flag->is_enabled)
                                    <span class="badge-status badge-active" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(60,107,76,0.12); color: var(--ula-status-success); font-weight: 700;">
                                        <span class="material-symbols-rounded" style="font-size: 12px;">check_circle</span>
                                        <span>{{ __('Enabled') }}</span>
                                    </span>
                                @else
                                    <span class="badge-status badge-suspended" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(217,107,95,0.12); color: var(--ula-status-danger); font-weight: 700;">
                                        <span class="material-symbols-rounded" style="font-size: 12px;">cancel</span>
                                        <span>{{ __('Disabled') }}</span>
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; text-align: center;">
                                <form method="POST" action="{{ route('superadmin.features.toggle', $flag) }}" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="tactile-btn" style="padding: 6px 14px; font-size: 11px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 4px; {{ $flag->is_enabled ? 'background: rgba(217, 107, 95, 0.12); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.3);' : 'background: rgba(60, 107, 76, 0.12); color: var(--ula-status-success); border: 1px solid rgba(60, 107, 76, 0.3);' }}">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">{{ $flag->is_enabled ? 'toggle_off' : 'toggle_on' }}</span>
                                        <span>{{ $flag->is_enabled ? __('Disable') : __('Enable') }}</span>
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
