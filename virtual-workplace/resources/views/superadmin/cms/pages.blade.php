@extends('superadmin.layout')

@section('title', __('CMS Pages & Website Content'))
@section('page_title', __('Website CMS — Pages & Sections (إدارة صفحات وأقسام الموقع)'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">
                🌐 {{ __('Public Website Pages (صفحات الموقع العام)') }}
            </h2>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                {{ __('Manage dynamic content, 3D scenes, sections, and bilingual SEO for the NextSpace public website.') }}
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('landing.home') }}" target="_blank" class="tactile-btn btn-outline">
                <span>👁️</span> {{ __('Preview Live Website') }}
            </a>
            <a href="{{ route('superadmin.cms.theme') }}" class="tactile-btn btn-secondary">
                <span>🎨</span> {{ __('Theme & Branding Studio') }}
            </a>
            <a href="{{ route('superadmin.cms.assets') }}" class="tactile-btn btn-primary">
                <span>📁</span> {{ __('3D & Media Assets') }}
            </a>
        </div>
    </div>

    <!-- Pages Data Table -->
    <div class="panel-card" style="border-radius: var(--radius-xl); padding: 24px;">
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Page Title & URL Slug') }}</th>
                        <th>{{ __('Arabic Title') }}</th>
                        <th>{{ __('Total Sections') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Last Updated') }}</th>
                        <th style="text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $p)
                        <tr>
                            <td>
                                <strong style="font-size: 14px; color: var(--text-primary); display: block;">
                                    📄 {{ $p->title_en }}
                                </strong>
                                <span style="font-size: 11px; font-family: monospace; color: var(--brand-forest); font-weight: 700;">
                                    /{{ $p->slug === 'home' ? '' : $p->slug }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 13px; font-weight: 700; color: var(--text-primary);">
                                    {{ $p->title_ar }}
                                </span>
                            </td>
                            <td>
                                <span class="nav-badge-pill" style="font-size: 12px;">
                                    🧩 {{ $p->sections_count }} {{ __('Sections') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-status {{ $p->status === 'published' ? 'badge-active' : 'badge-suspended' }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: var(--text-muted);">
                                    {{ $p->updated_at ? $p->updated_at->diffForHumans() : '—' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('superadmin.cms.pages.edit', $p) }}" class="tactile-btn btn-primary" style="padding: 6px 16px; font-size: 12px;">
                                    <span>✏️</span> {{ __('Edit Sections & Content') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                {{ __('No CMS pages found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
