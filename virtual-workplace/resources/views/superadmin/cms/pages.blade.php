@extends('superadmin.layout')

@section('title', __('CMS Pages & Website Content'))
@section('page_title', __('Website CMS — Pages & Sections'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); display: flex; align-items: center; justify-content: center; color: var(--ula-palm-700);">
                    <span class="material-symbols-rounded" style="font-size: 22px;">language</span>
                </div>
                <h2 style="font-size: 20px; font-weight: 900; color: var(--ula-text-primary); margin: 0;">
                    {{ __('Public Website Pages') }}
                </h2>
            </div>
            <p style="font-size: 13px; color: var(--ula-text-muted); margin: 0;">
                {{ __('Manage dynamic website content, section blocks, floorplan screenshots, and bilingual texts for UlaSpace.') }}
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('landing.home') }}" target="_blank" class="tactile-btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">visibility</span>
                <span>{{ __('Preview Live Website') }}</span>
            </a>
            <a href="{{ route('superadmin.cms.theme') }}" class="tactile-btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">palette</span>
                <span>{{ __('Theme & Branding') }}</span>
            </a>
            <a href="{{ route('superadmin.cms.assets') }}" class="tactile-btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">photo_library</span>
                <span>{{ __('Media Library') }}</span>
            </a>
        </div>
    </div>

    <!-- Pages Data Table -->
    <div class="panel-card" style="border-radius: var(--ula-radius-xl); padding: 24px; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card);">
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Page Title & URL Slug') }}</th>
                        <th>{{ __('Arabic Title') }}</th>
                        <th>{{ __('Active Sections') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Last Updated') }}</th>
                        <th style="text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $p)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); display: flex; align-items: center; justify-content: center; color: var(--ula-palm-900); flex-shrink: 0;">
                                        <span class="material-symbols-rounded" style="font-size: 18px;">description</span>
                                    </div>
                                    <div>
                                        <strong style="font-size: 14px; color: var(--ula-text-primary); display: block;">
                                            {{ $p->title_en }}
                                        </strong>
                                        <span style="font-size: 11px; font-family: var(--ula-font-mono); color: var(--ula-palm-700); font-weight: 700; direction: ltr; unicode-bidi: isolate;">
                                            /{{ $p->slug === 'home' ? '' : $p->slug }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 13.5px; font-weight: 700; color: var(--ula-text-primary); font-family: 'Cairo', sans-serif;">
                                    {{ $p->title_ar }}
                                </span>
                            </td>
                            <td>
                                <span class="nav-badge-pill" style="font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                                    <span class="material-symbols-rounded" style="font-size: 15px; color: var(--ula-palm-700);">view_quilt</span>
                                    <span>{{ $p->sections_count }} {{ __('Sections') }}</span>
                                </span>
                            </td>
                            <td>
                                <span class="badge-status {{ $p->status === 'published' ? 'badge-active' : 'badge-suspended' }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: var(--ula-text-muted);">
                                    {{ $p->updated_at ? $p->updated_at->diffForHumans() : '—' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('superadmin.cms.pages.edit', $p) }}" class="tactile-btn btn-primary" style="padding: 7px 18px; font-size: 12.5px; display: inline-flex; align-items: center; gap: 6px;">
                                    <span class="material-symbols-rounded" style="font-size: 16px;">edit_document</span>
                                    <span>{{ __('Edit Sections & Content') }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 36px; color: var(--ula-text-muted);">
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
