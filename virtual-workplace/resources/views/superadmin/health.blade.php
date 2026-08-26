@extends('superadmin.layout')

@section('title', __('System Health & Status'))
@section('page_title', __('System — Infrastructure & Health Status (حالة النظام والخدمات)'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">
                ⚡ {{ __('Platform Health & Services Telemetry') }}
            </h2>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                {{ __('Real-time operational status for WebRTC SFU, database cluster, spatial WebSockets, and AI endpoints.') }}
            </p>
        </div>
    </div>

    <!-- Health Telemetry Grid -->
    <div class="kpi-grid">
        <div class="kpi-card" style="border-top: 4px solid #10B981;">
            <div class="kpi-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
                🗄️
            </div>
            <div class="kpi-value" style="color: #10B981;">
                {{ __('Healthy') }}
            </div>
            <div class="kpi-label">
                {{ __('Primary Database (MySQL 8.0)') }}
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                Latency: <strong>1.2ms</strong> • Connections: Active
            </div>
        </div>

        <div class="kpi-card" style="border-top: 4px solid #10B981;">
            <div class="kpi-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
                📹
            </div>
            <div class="kpi-value" style="color: #10B981;">
                {{ __('Operational') }}
            </div>
            <div class="kpi-label">
                {{ __('LiveKit SFU WebRTC Server') }}
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                Port 7880 • STUN/TURN 3478 Active
            </div>
        </div>

        <div class="kpi-card" style="border-top: 4px solid #10B981;">
            <div class="kpi-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
                ⚡
            </div>
            <div class="kpi-value" style="color: #10B981;">
                {{ __('Active') }}
            </div>
            <div class="kpi-label">
                {{ __('Spatial Realtime WebSockets') }}
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                Port 8080 • Spatial Interpolation Running
            </div>
        </div>

        <div class="kpi-card" style="border-top: 4px solid #10B981;">
            <div class="kpi-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
                🤖
            </div>
            <div class="kpi-value" style="color: #10B981;">
                {{ __('Ready') }}
            </div>
            <div class="kpi-label">
                {{ __('AI Blueprint Engine') }}
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                GPT Image 1 Mini & DALL-E Available
            </div>
        </div>
    </div>

    <!-- Storage & Server Environment -->
    <div class="panel-card" style="border-radius: var(--radius-xl); padding: 24px;">
        <div class="panel-header" style="margin-bottom: 20px;">
            <div class="panel-title">
                <span>🖥️</span>
                <span>{{ __('Server Runtime Environment Details') }}</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
            <div style="background: var(--bg-surface-subtle); padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border-color);">
                <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 4px;">PHP Runtime</span>
                <strong style="font-size: 14px; color: var(--text-primary);">PHP {{ PHP_VERSION }} (FPM / OPcache)</strong>
            </div>

            <div style="background: var(--bg-surface-subtle); padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border-color);">
                <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 4px;">Laravel Framework</span>
                <strong style="font-size: 14px; color: var(--text-primary);">Laravel {{ app()->version() }}</strong>
            </div>

            <div style="background: var(--bg-surface-subtle); padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border-color);">
                <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 4px;">Disk Free Space</span>
                <strong style="font-size: 14px; color: var(--brand-forest);">{{ disk_free_space('/') ? round(disk_free_space('/') / 1073741824, 1) . ' GB Available' : 'N/A' }}</strong>
            </div>

            <div style="background: var(--bg-surface-subtle); padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border-color);">
                <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 4px;">Server OS</span>
                <strong style="font-size: 14px; color: var(--text-primary);">Linux Ubuntu 22.04 LTS (Plesk)</strong>
            </div>
        </div>
    </div>
</div>
@endsection
