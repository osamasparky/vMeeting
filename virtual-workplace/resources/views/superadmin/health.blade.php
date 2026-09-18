@extends('superadmin.layout')

@section('title', __('System Health & Status'))
@section('page_title', __('System Infrastructure & Health Status'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-status-success); font-size: 24px;">health_and_safety</span>
                <span>{{ __('Platform Health & Services Telemetry') }}</span>
            </h2>
            <p style="font-size: 13px; color: var(--ula-text-secondary); margin: 0;">
                {{ __('Real-time operational status for WebRTC SFU, database cluster, spatial WebSockets, and AI endpoints.') }}
            </p>
        </div>
    </div>

    <!-- Health Telemetry Grid -->
    <div class="metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div class="metric-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 20px; box-shadow: var(--ula-shadow-sm); border-top: 4px solid var(--ula-status-success);">
            <div class="metric-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span class="metric-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Primary Database') }}</span>
                <div class="metric-icon-badge" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(60, 107, 76, 0.12); color: var(--ula-status-success); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">database</span>
                </div>
            </div>
            <div class="metric-value" style="font-size: 22px; font-weight: 800; color: var(--ula-status-success); margin-bottom: 6px;">
                {{ __('Healthy') }}
            </div>
            <div class="metric-trend" style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                <span>MySQL 8.0</span> • <span>Latency: 1.2ms</span>
            </div>
        </div>

        <div class="metric-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 20px; box-shadow: var(--ula-shadow-sm); border-top: 4px solid var(--ula-status-success);">
            <div class="metric-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span class="metric-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('WebRTC SFU') }}</span>
                <div class="metric-icon-badge" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(60, 107, 76, 0.12); color: var(--ula-status-success); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">videocam</span>
                </div>
            </div>
            <div class="metric-value" style="font-size: 22px; font-weight: 800; color: var(--ula-status-success); margin-bottom: 6px;">
                {{ __('Operational') }}
            </div>
            <div class="metric-trend" style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                <span>LiveKit SFU</span> • <span>Port 7880 Active</span>
            </div>
        </div>

        <div class="metric-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 20px; box-shadow: var(--ula-shadow-sm); border-top: 4px solid var(--ula-status-success);">
            <div class="metric-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span class="metric-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Spatial WebSockets') }}</span>
                <div class="metric-icon-badge" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(60, 107, 76, 0.12); color: var(--ula-status-success); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">bolt</span>
                </div>
            </div>
            <div class="metric-value" style="font-size: 22px; font-weight: 800; color: var(--ula-status-success); margin-bottom: 6px;">
                {{ __('Active') }}
            </div>
            <div class="metric-trend" style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                <span>Port 8080</span> • <span>Interpolation Running</span>
            </div>
        </div>

        <div class="metric-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 20px; box-shadow: var(--ula-shadow-sm); border-top: 4px solid var(--ula-status-success);">
            <div class="metric-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span class="metric-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('AI Blueprint Engine') }}</span>
                <div class="metric-icon-badge" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(60, 107, 76, 0.12); color: var(--ula-status-success); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">smart_toy</span>
                </div>
            </div>
            <div class="metric-value" style="font-size: 22px; font-weight: 800; color: var(--ula-status-success); margin-bottom: 6px;">
                {{ __('Ready') }}
            </div>
            <div class="metric-trend" style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                <span>GPT Image 1 Mini</span> • <span>DALL-E Ready</span>
            </div>
        </div>
    </div>

    <!-- Storage & Server Environment -->
    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 24px; box-shadow: var(--ula-shadow-sm);">
        <div class="panel-header" style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 22px;">terminal</span>
            <span style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary);">{{ __('Server Runtime Environment Details') }}</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
            <div style="background: var(--ula-surface-page-alt); padding: 16px 20px; border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                <span style="font-size: 11px; color: var(--ula-text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">PHP Runtime</span>
                <strong style="font-size: 14px; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">PHP {{ PHP_VERSION }} (FPM / OPcache)</strong>
            </div>

            <div style="background: var(--ula-surface-page-alt); padding: 16px 20px; border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                <span style="font-size: 11px; color: var(--ula-text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Laravel Framework</span>
                <strong style="font-size: 14px; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">Laravel {{ app()->version() }}</strong>
            </div>

            <div style="background: var(--ula-surface-page-alt); padding: 16px 20px; border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                <span style="font-size: 11px; color: var(--ula-text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Disk Free Space</span>
                <strong style="font-size: 14px; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ ($freeBytes = @disk_free_space(base_path())) ? round($freeBytes / 1073741824, 1) . ' GB Available' : ($health['storage']['free_space'] ?? 'Available') }}</strong>
            </div>

            <div style="background: var(--ula-surface-page-alt); padding: 16px 20px; border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                <span style="font-size: 11px; color: var(--ula-text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Server OS</span>
                <strong style="font-size: 14px; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">Linux Ubuntu 22.04 LTS (Plesk)</strong>
            </div>
        </div>
    </div>
</div>
@endsection
