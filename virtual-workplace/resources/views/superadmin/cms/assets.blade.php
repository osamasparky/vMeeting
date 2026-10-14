@extends('superadmin.layout')

@section('title', __('Image Library'))
@section('page_title', __('Website Content — Image Library'))

@section('styles')
<style>
    .lib-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
    .lib-head h2 { margin: 0; font-size: 20px; font-weight: var(--ula-weight-semibold); color: var(--ula-text-primary); }
    .lib-head p { margin: 4px 0 0; font-size: 13px; color: var(--ula-text-muted); }
    .lib-card { padding: 24px; border-radius: var(--ula-radius-xl); background: var(--ula-surface-card); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); }
    .lib-upload { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr) auto; gap: 16px; align-items: end; }
    .lib-field { display: flex; flex-direction: column; gap: 6px; }
    .lib-field label { font-size: 12px; font-weight: var(--ula-weight-semibold); color: var(--ula-text-secondary); }
    .lib-field .form-input { width: 100%; min-height: 44px; }
    .lib-btn { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; }
    .lib-alert { display: flex; align-items: center; gap: 10px; padding: 14px 18px; border-radius: var(--ula-radius-sm); font-size: 13.5px; }
    .lib-alert--ok { background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); }
    .lib-alert--err { background: var(--ula-tone-terracotta-bg); color: var(--ula-tone-terracotta-fg); }
    .lib-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 18px; }
    .lib-item { display: flex; flex-direction: column; border-radius: var(--ula-radius-lg); overflow: hidden; background: var(--ula-surface-page-alt); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); }
    .lib-thumb { aspect-ratio: 4 / 3; background: repeating-linear-gradient(135deg, var(--ula-media-stripe-a) 0 12px, var(--ula-media-stripe-b) 12px 24px); }
    .lib-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .lib-body { padding: 14px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
    .lib-body strong { font-size: 13px; color: var(--ula-text-primary); word-break: break-word; }
    .lib-used { display: flex; flex-wrap: wrap; gap: 4px; }
    .lib-used span { padding: 2px 8px; border-radius: var(--ula-radius-pill); background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); font-size: 11px; }
    .lib-meta { font-size: 11px; color: var(--ula-text-muted); }
    .lib-actions { margin-top: auto; display: flex; gap: 8px; }
    .lib-actions .tactile-btn { min-height: 44px; display: inline-flex; align-items: center; gap: 4px; font-size: 12px; }
    .lib-del { background: var(--ula-tone-terracotta-bg); color: var(--ula-tone-terracotta-fg); border-color: transparent; }
    @media (max-width: 720px) { .lib-upload { grid-template-columns: minmax(0, 1fr); } }
</style>
@endsection

@section('content')
@php $schemaNames = fn ($key) => \App\Domains\CMS\Support\CmsSchema::section($key)['name_ar'] ?? $key; @endphp
<div style="display: flex; flex-direction: column; gap: 24px;">

    <div class="lib-head">
        <div>
            <h2>{{ __('Image Library') }}</h2>
            <p>{{ __('Images you upload here can be picked for any section with an image. You can also upload straight from a section.') }}</p>
        </div>
        <a href="{{ route('superadmin.cms.pages') }}" class="tactile-btn btn-outline lib-btn">
            <span class="material-symbols-rounded" style="font-size: 18px;">arrow_back</span>
            <span>{{ __('Website Content') }}</span>
        </a>
    </div>

    @if(session('success'))
        <div class="lib-alert lib-alert--ok" role="status"><span class="material-symbols-rounded">check_circle</span>{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="lib-alert lib-alert--err" role="alert"><span class="material-symbols-rounded">error</span>{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('superadmin.cms.assets.upload') }}" enctype="multipart/form-data" class="lib-card">
        @csrf
        <div class="lib-upload">
            <div class="lib-field">
                <label for="lib_name">{{ __('Name') }}</label>
                <input id="lib_name" type="text" name="name" required maxlength="255" placeholder="{{ __('e.g. Hero — AlUla canyon') }}" class="form-input" value="{{ old('name') }}">
            </div>
            <div class="lib-field">
                <label for="lib_file">{{ __('Image') }} <span style="font-weight: var(--ula-weight-regular); color: var(--ula-text-muted);">JPG · PNG · WebP · AVIF · GIF · SVG — max 10 MB</span></label>
                <input id="lib_file" type="file" name="file" required accept="image/*" class="form-input">
            </div>
            <button type="submit" class="tactile-btn btn-primary lib-btn">
                <span class="material-symbols-rounded" style="font-size: 18px;">upload</span>
                <span>{{ __('Upload') }}</span>
            </button>
        </div>
    </form>

    <div class="lib-card">
        <div class="lib-grid">
            @forelse($assets as $ast)
                <div class="lib-item">
                    <div class="lib-thumb"><img src="{{ $ast->url }}" alt="{{ $ast->name }}" loading="lazy"></div>
                    <div class="lib-body">
                        <strong>{{ $ast->name }}</strong>
                        @if($ast->sections->isNotEmpty())
                            <div class="lib-used">
                                @foreach($ast->sections as $usedBy)
                                    <span>{{ $schemaNames($usedBy->section_key) }}</span>
                                @endforeach
                            </div>
                        @endif
                        <span class="lib-meta">{{ $ast->created_at?->format('Y-m-d') }}</span>
                        <div class="lib-actions">
                            <a href="{{ $ast->url }}" target="_blank" rel="noopener" class="tactile-btn btn-secondary">
                                <span class="material-symbols-rounded" style="font-size: 16px;">open_in_new</span>{{ __('Open') }}
                            </a>
                            <form method="POST" action="{{ route('superadmin.cms.assets.delete', $ast) }}" onsubmit="return confirm(@js($ast->sections->isNotEmpty() ? __('This image is used on the website. Delete it? Those sections will show the placeholder.') : __('Delete this image?')))" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tactile-btn lib-del">
                                    <span class="material-symbols-rounded" style="font-size: 16px;">delete</span>{{ __('Delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--ula-text-muted);">{{ __('No images yet.') }}</p>
            @endforelse
        </div>
        <div style="margin-top: 20px;">{{ $assets->links() }}</div>
    </div>
</div>
@endsection
