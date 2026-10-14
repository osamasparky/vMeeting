@extends('superadmin.layout')

@section('title', __('Website Content'))
@section('page_title', __('Website Content'))

@section('styles')
<style>
    .cms-pages-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
    .cms-pages-head h2 { margin: 0; font-size: 20px; font-weight: var(--ula-weight-semibold); color: var(--ula-text-primary); }
    .cms-pages-head p { margin: 4px 0 0; font-size: 13px; color: var(--ula-text-muted); }
    .cms-pages-head a { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; }
    .cms-pages { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    .cms-page-card { display: flex; flex-direction: column; gap: 16px; padding: 22px; border-radius: var(--ula-radius-xl); background: var(--ula-surface-card); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs); text-decoration: none; color: inherit; }
    .cms-page-card:hover { border-color: var(--ula-accent-default); box-shadow: var(--ula-shadow-sm); text-decoration: none; }
    .cms-page-card:focus-visible { outline: none; box-shadow: var(--ula-focus-ring); }
    .cms-page-icon { width: 48px; height: 48px; border-radius: var(--ula-radius-md); background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center; }
    .cms-page-card strong { display: block; font-size: 17px; font-weight: var(--ula-weight-semibold); color: var(--ula-text-primary); }
    .cms-page-card .cms-en { font-family: var(--ula-font-en); font-size: 13px; color: var(--ula-text-secondary); }
    .cms-page-meta { display: flex; flex-wrap: wrap; gap: 6px; }
    .cms-page-meta span { padding: 3px 10px; border-radius: var(--ula-radius-pill); background: var(--ula-surface-page-alt); font-size: 12px; color: var(--ula-text-secondary); }
    .cms-num { font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; }
    [dir="rtl"] .cms-page-go .material-symbols-rounded { transform: scaleX(-1); }
    .cms-page-go { margin-top: auto; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: var(--ula-weight-semibold); color: var(--ula-accent-default); }
</style>
@endsection

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <div class="cms-pages-head">
        <div>
            <h2>{{ __('Website Content') }}</h2>
            <p>{{ __('Every text, link and image on the public website, the header and footer, and the log-in and sign-up pages.') }}</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('landing.home') }}" target="_blank" rel="noopener" class="tactile-btn btn-outline">
                <span class="material-symbols-rounded" style="font-size: 18px;">open_in_new</span>
                <span>{{ __('View website') }}</span>
            </a>
            <a href="{{ route('superadmin.cms.assets') }}" class="tactile-btn btn-secondary">
                <span class="material-symbols-rounded" style="font-size: 18px;">photo_library</span>
                <span>{{ __('Image Library') }}</span>
            </a>
        </div>
    </div>

    <div class="cms-pages">
        @forelse($pages as $p)
            @php $def = \App\Domains\CMS\Support\CmsSchema::page($p->slug); @endphp
            <a href="{{ route('superadmin.cms.pages.edit', $p) }}" class="cms-page-card">
                <span class="cms-page-icon"><span class="material-symbols-rounded" style="font-size: 26px;">{{ $def['icon'] }}</span></span>
                <div>
                    <strong>{{ $def['title_ar'] }}</strong>
                    <span class="cms-en">{{ $def['title_en'] }}</span>
                </div>
                <div class="cms-page-meta">
                    <span><span class="cms-num">{{ $p->sections_count }}</span> {{ __('sections') }}</span>
                    @if($p->active_sections_count < $p->sections_count)
                        <span><span class="cms-num">{{ $p->sections_count - $p->active_sections_count }}</span> {{ __('hidden') }}</span>
                    @endif
                    <span>{{ __('Updated') }} {{ $p->updated_at?->diffForHumans() ?? '—' }}</span>
                </div>
                <span class="cms-page-go">{{ __('Edit content') }} <span class="material-symbols-rounded" style="font-size: 18px;">arrow_forward</span></span>
            </a>
        @empty
            <p style="color: var(--ula-text-muted);">{{ __('No pages yet — run php artisan migrate to create them.') }}</p>
        @endforelse
    </div>
</div>
@endsection
