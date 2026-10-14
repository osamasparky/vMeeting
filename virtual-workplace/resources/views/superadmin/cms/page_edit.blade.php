@extends('superadmin.layout')

@section('title', __('Edit :page', ['page' => $pageDef['title_en']]))
@section('page_title', __('Website Content'))

@section('styles')
<style>
    .cms-wrap { display: flex; flex-direction: column; gap: 20px; }
    .cms-topbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
    .cms-topbar h2 { margin: 0; font-size: 21px; font-weight: var(--ula-weight-semibold); color: var(--ula-text-primary); }
    .cms-topbar p { margin: 4px 0 0; font-size: 13px; color: var(--ula-text-muted); }
    .cms-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .cms-actions a { display: inline-flex; align-items: center; gap: 6px; }

    .cms-alert { display: flex; align-items: flex-start; gap: 10px; padding: 14px 18px; border-radius: var(--ula-radius-sm); font-size: 13.5px; font-weight: var(--ula-weight-medium); }
    .cms-alert--ok { background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); }
    .cms-alert--err { background: var(--ula-tone-terracotta-bg); color: var(--ula-tone-terracotta-fg); }
    .cms-alert ul { margin: 0; padding-inline-start: 18px; }

    .cms-nav { position: sticky; top: 70px; z-index: 30; display: flex; gap: 8px; overflow-x: auto; padding: 8px; border-radius: var(--ula-radius-lg); background: var(--ula-surface-page); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); scrollbar-width: thin; }
    .cms-nav a { display: inline-flex; align-items: center; gap: 6px; min-height: 36px; padding: 0 14px; border-radius: var(--ula-radius-pill); background: var(--ula-surface-card); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); font-size: 12.5px; font-weight: var(--ula-weight-medium); color: var(--ula-text-secondary); text-decoration: none; white-space: nowrap; }
    .cms-nav a:hover { color: var(--ula-text-primary); border-color: var(--ula-accent-default); }
    .cms-nav a.is-hidden { opacity: 0.55; text-decoration: line-through; }
    .cms-nav .material-symbols-rounded { font-size: 16px; color: var(--ula-icon-accent); }

    .cms-card { background: var(--ula-surface-card); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 24px; box-shadow: var(--ula-shadow-xs); scroll-margin-top: 140px; }
    .cms-card.is-hidden { opacity: 0.7; }
    .cms-card-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; padding-bottom: 16px; margin-bottom: 20px; border-bottom: var(--ula-border-width-hairline) solid var(--ula-border-subtle); }
    .cms-card-title { display: flex; align-items: center; gap: 14px; }
    .cms-card-icon { width: 44px; height: 44px; flex-shrink: 0; border-radius: var(--ula-radius-sm); background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center; }
    .cms-card-title strong { display: block; font-size: 17px; font-weight: var(--ula-weight-semibold); color: var(--ula-text-primary); }
    .cms-card-title span.cms-sub { font-size: 12px; color: var(--ula-text-muted); }
    .cms-note { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; font-size: 12.5px; color: var(--ula-text-secondary); }

    .cms-pill { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 16px; border-radius: var(--ula-radius-pill); border: var(--ula-border-width-hairline) solid var(--ula-border-default); background: var(--ula-surface-card); font-family: inherit; font-size: 12.5px; font-weight: var(--ula-weight-medium); cursor: pointer; }
    .cms-pill--on { background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); border-color: transparent; }
    .cms-pill--off { background: var(--ula-tone-terracotta-bg); color: var(--ula-tone-terracotta-fg); border-color: transparent; }
    .cms-pill--fixed { cursor: default; color: var(--ula-text-muted); }
    .cms-pill .material-symbols-rounded { font-size: 17px; }

    .cms-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
    .cms-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
    .cms-field--wide, .cms-block { grid-column: 1 / -1; }
    .cms-label { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: var(--ula-weight-semibold); color: var(--ula-text-secondary); }
    .cms-lang { padding: 1px 6px; border-radius: var(--ula-radius-xs); background: var(--ula-surface-page-alt); color: var(--ula-text-muted); font-family: var(--ula-font-mono); font-size: 10px; direction: ltr; unicode-bidi: isolate; }
    .cms-input { width: 100%; min-height: 44px; font-size: 13.5px; }
    textarea.cms-input { min-height: 80px; resize: vertical; line-height: 1.6; }
    .cms-hint { font-size: 11.5px; color: var(--ula-text-muted); }
    .cms-hint a { color: var(--ula-accent-default); }
    .cms-icon-input { display: flex; align-items: center; gap: 8px; }
    .cms-icon-preview { width: 44px; height: 44px; flex-shrink: 0; border-radius: var(--ula-radius-sm); background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center; font-size: 22px; }

    .cms-block { border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); background: var(--ula-surface-page-alt); padding: 18px; display: flex; flex-direction: column; gap: 14px; }
    .cms-block-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .cms-block-head strong { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: var(--ula-weight-semibold); color: var(--ula-text-primary); }
    .cms-block-head .material-symbols-rounded { font-size: 18px; color: var(--ula-icon-accent); }

    .cms-row { border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); border-radius: var(--ula-radius-md); background: var(--ula-surface-card); padding: 14px; display: flex; flex-direction: column; gap: 12px; }
    .cms-row-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .cms-row-head span { font-size: 12px; font-weight: var(--ula-weight-semibold); color: var(--ula-text-muted); }
    .cms-row-tools { display: flex; gap: 4px; }
    .cms-icon-btn { width: 44px; height: 44px; border-radius: var(--ula-radius-sm); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-card); color: var(--ula-text-secondary); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
    .cms-icon-btn:hover { color: var(--ula-text-primary); border-color: var(--ula-border-default); }
    .cms-icon-btn--danger:hover { color: var(--ula-status-danger); }
    .cms-icon-btn .material-symbols-rounded { font-size: 18px; }
    .cms-add { align-self: flex-start; display: inline-flex; align-items: center; gap: 6px; }

    .cms-image { display: grid; grid-template-columns: 200px minmax(0, 1fr); gap: 20px; align-items: start; }
    .cms-image-frame { width: 200px; aspect-ratio: 4 / 3; border-radius: var(--ula-radius-md); overflow: hidden; border: var(--ula-border-width-hairline) solid var(--ula-border-default); background: repeating-linear-gradient(135deg, var(--ula-media-stripe-a) 0 12px, var(--ula-media-stripe-b) 12px 24px); display: flex; align-items: center; justify-content: center; }
    .cms-image-frame img { width: 100%; height: 100%; object-fit: cover; }
    .cms-image-frame span { padding: 6px 8px; background: var(--ula-media-label-bg); color: var(--ula-media-label-fg); font-size: 11px; }
    .cms-image-controls { display: flex; flex-direction: column; gap: 12px; }
    .cms-check { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; font-size: 12.5px; color: var(--ula-text-secondary); cursor: pointer; }
    .cms-check input { width: 18px; height: 18px; accent-color: var(--ula-accent-default); }

    .cms-save { display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-top: 20px; padding-top: 16px; border-top: var(--ula-border-width-hairline) solid var(--ula-border-subtle); }
    .cms-save button { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding-inline: 24px; }

    @media (max-width: 720px) {
        .cms-image { grid-template-columns: minmax(0, 1fr); }
        .cms-image-frame { width: 100%; }
    }
</style>
@endsection

@section('content')
@php
    $savedKey = session('cms_section');
    $oldFields = old('fields');
    // After a failed save, refill only the section that was being edited.
    $valueFor = function ($sec, $key) use ($savedKey, $oldFields) {
        if ($oldFields !== null && $savedKey === $sec->section_key) {
            return $oldFields[$key] ?? null;
        }
        return $sec->field($key);
    };
@endphp

<div class="cms-wrap">

    <div class="cms-topbar">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ route('superadmin.cms.pages') }}" class="tactile-btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px; min-height: 44px;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">arrow_back</span>
                    <span>{{ __('All pages') }}</span>
                </a>
                <h2>{{ $pageDef['title_ar'] }} <span style="font-weight: var(--ula-weight-regular); color: var(--ula-text-muted); font-size: 15px;">· {{ $pageDef['title_en'] }}</span></h2>
            </div>
            <p>{{ __('Everything this page shows is edited here. Each section saves on its own.') }}</p>
        </div>
        <div class="cms-actions">
            <a href="{{ url($pageDef['url']) }}" target="_blank" rel="noopener" class="tactile-btn btn-outline">
                <span class="material-symbols-rounded" style="font-size: 18px;">open_in_new</span>
                <span>{{ __('View page') }}</span>
            </a>
            <a href="{{ route('superadmin.cms.assets') }}" class="tactile-btn btn-secondary">
                <span class="material-symbols-rounded" style="font-size: 18px;">photo_library</span>
                <span>{{ __('Image Library') }}</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="cms-alert cms-alert--ok" role="status">
            <span class="material-symbols-rounded" style="font-size: 20px;">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="cms-alert cms-alert--err" role="alert">
            <span class="material-symbols-rounded" style="font-size: 20px;">error</span>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if(count($sections) > 1)
        <nav class="cms-nav" aria-label="{{ __('Sections') }}">
            @if(!empty($pageDef['seo']))
                <a href="#sec_seo"><span class="material-symbols-rounded">travel_explore</span><span>{{ __('Page title & description') }}</span></a>
            @endif
            @foreach($sections as $sec)
                @php $def = $pageDef['sections'][$sec->section_key]; @endphp
                <a href="#sec_{{ $sec->section_key }}" class="{{ $sec->is_active ? '' : 'is-hidden' }}">
                    <span class="material-symbols-rounded">{{ $def['icon'] }}</span>
                    <span>{{ $def['name_ar'] }}</span>
                </a>
            @endforeach
        </nav>
    @endif

    @if(!empty($pageDef['seo']))
        <div class="cms-card" id="sec_seo">
            <div class="cms-card-head">
                <div class="cms-card-title">
                    <span class="cms-card-icon"><span class="material-symbols-rounded">travel_explore</span></span>
                    <div>
                        <strong>عنوان الصفحة ووصفها</strong>
                        <span class="cms-sub">{{ __('Browser tab title and search-engine description') }}</span>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('superadmin.cms.pages.update', $page) }}">
                @csrf
                @method('PUT')
                <div class="cms-grid">
                    <div class="cms-field cms-field--wide">
                        <label class="cms-label" for="meta_title_ar"><span>{{ __('Page title') }}</span><span class="cms-lang">AR</span></label>
                        <input id="meta_title_ar" type="text" name="meta_title_ar" value="{{ old('meta_title_ar', $page->meta_title_ar) }}" class="form-input cms-input" dir="rtl" maxlength="255">
                    </div>
                    <div class="cms-field cms-field--wide">
                        <label class="cms-label" for="meta_desc_ar"><span>{{ __('Description') }}</span><span class="cms-lang">AR</span></label>
                        <textarea id="meta_desc_ar" name="meta_desc_ar" rows="2" class="form-input cms-input" dir="rtl" maxlength="500">{{ old('meta_desc_ar', $page->meta_desc_ar) }}</textarea>
                    </div>
                </div>
                <div class="cms-save">
                    <button type="submit" class="tactile-btn btn-primary"><span class="material-symbols-rounded" style="font-size: 18px;">save</span><span>{{ __('Save') }}</span></button>
                </div>
            </form>
        </div>
    @endif

    @foreach($sections as $sec)
        @php
            $def = $pageDef['sections'][$sec->section_key];
            $required = !empty($def['required']);
            $repeaters = array_filter($def['fields'], fn ($f) => $f['type'] === 'repeater');
            $simple = array_filter($def['fields'], fn ($f) => $f['type'] !== 'repeater');
        @endphp
        <div class="cms-card {{ $sec->is_active ? '' : 'is-hidden' }}" id="sec_{{ $sec->section_key }}">
            <div class="cms-card-head">
                <div class="cms-card-title">
                    <span class="cms-card-icon"><span class="material-symbols-rounded">{{ $def['icon'] }}</span></span>
                    <div>
                        <strong>{{ $def['name_ar'] }}</strong>
                        <span class="cms-sub">{{ __($def['name_en']) }}</span>
                    </div>
                </div>
                @if($required)
                    <span class="cms-pill cms-pill--fixed"><span class="material-symbols-rounded">lock</span>{{ __('Always shown') }}</span>
                @else
                    <form method="POST" action="{{ route('superadmin.cms.sections.toggle', $sec) }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="cms-pill {{ $sec->is_active ? 'cms-pill--on' : 'cms-pill--off' }}" title="{{ __('Click to switch') }}">
                            <span class="material-symbols-rounded">{{ $sec->is_active ? 'visibility' : 'visibility_off' }}</span>
                            <span>{{ $sec->is_active ? __('Shown on website') : __('Hidden from website') }}</span>
                        </button>
                    </form>
                @endif
            </div>

            @if(!empty($def['note']))
                <div class="cms-note"><span class="material-symbols-rounded" style="font-size: 18px;">info</span><span>{{ __($def['note']) }}</span></div>
            @endif

            <form method="POST" action="{{ route('superadmin.cms.sections.update', $sec) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @unless($required)
                    <input type="hidden" name="is_active" value="{{ $sec->is_active ? 1 : 0 }}">
                @endunless

                <div class="cms-grid">
                    @if(!empty($def['image']))
                        <div class="cms-block">
                            <div class="cms-block-head">
                                <strong><span class="material-symbols-rounded">image</span>{{ __('Image') }}</strong>
                                <span class="cms-hint">{{ __($def['image']['hint']) }}</span>
                            </div>
                            <div class="cms-image">
                                <div class="cms-image-frame">
                                    @if($sec->image_url)
                                        <img src="{{ $sec->image_url }}" alt="">
                                    @else
                                        <span>{{ __('No image — placeholder shown') }}</span>
                                    @endif
                                </div>
                                <div class="cms-image-controls">
                                    <div class="cms-field">
                                        <label class="cms-label" for="img_{{ $sec->id }}">{{ __('Upload a new image') }}</label>
                                        <input id="img_{{ $sec->id }}" type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/avif,image/gif" class="form-input cms-input">
                                    </div>
                                    <div class="cms-field">
                                        <label class="cms-label" for="asset_{{ $sec->id }}">{{ __('Or choose from the image library') }}</label>
                                        <select id="asset_{{ $sec->id }}" name="media_asset_id" class="form-input cms-input">
                                            <option value="">— {{ __('No image') }} —</option>
                                            @foreach($assets as $asset)
                                                <option value="{{ $asset->id }}" @selected($sec->media_asset_id == $asset->id)>{{ $asset->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if($sec->media_asset_id)
                                        <label class="cms-check"><input type="checkbox" name="remove_image" value="1"> {{ __('Remove the image (show the placeholder)') }}</label>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @foreach($simple as $key => $fieldDef)
                        @include('superadmin.cms._field', ['name' => "fields[$key]", 'value' => $valueFor($sec, $key), 'def' => $fieldDef])
                    @endforeach

                    @foreach($repeaters as $key => $rep)
                        @php $rows = $valueFor($sec, $key); $rows = is_array($rows) ? array_values($rows) : []; @endphp
                        <div class="cms-block" data-repeater="{{ $sec->section_key }}_{{ $key }}">
                            <div class="cms-block-head">
                                <strong><span class="material-symbols-rounded">list</span>{{ __($rep['label']) }}</strong>
                                <span class="cms-hint">{{ __('Add, remove or reorder. Empty items are dropped on save.') }}</span>
                            </div>
                            <div class="cms-rows" data-rows>
                                @foreach($rows as $i => $row)
                                    <div class="cms-row" data-row>
                                        <div class="cms-row-head">
                                            <span>{{ __($rep['item_label']) }} <span data-row-number>{{ $i + 1 }}</span></span>
                                            <div class="cms-row-tools">
                                                <button type="button" class="cms-icon-btn" data-move="-1" aria-label="{{ __('Move up') }}"><span class="material-symbols-rounded">arrow_upward</span></button>
                                                <button type="button" class="cms-icon-btn" data-move="1" aria-label="{{ __('Move down') }}"><span class="material-symbols-rounded">arrow_downward</span></button>
                                                <button type="button" class="cms-icon-btn cms-icon-btn--danger" data-remove aria-label="{{ __('Remove') }}"><span class="material-symbols-rounded">delete</span></button>
                                            </div>
                                        </div>
                                        <div class="cms-grid">
                                            @foreach($rep['fields'] as $subKey => $subDef)
                                                @include('superadmin.cms._field', ['name' => "fields[$key][$i][$subKey]", 'value' => $row[$subKey] ?? ($subDef['default'] ?? null), 'def' => $subDef])
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <template data-template>
                                <div class="cms-row" data-row>
                                    <div class="cms-row-head">
                                        <span>{{ __($rep['item_label']) }} <span data-row-number></span></span>
                                        <div class="cms-row-tools">
                                            <button type="button" class="cms-icon-btn" data-move="-1" aria-label="{{ __('Move up') }}"><span class="material-symbols-rounded">arrow_upward</span></button>
                                            <button type="button" class="cms-icon-btn" data-move="1" aria-label="{{ __('Move down') }}"><span class="material-symbols-rounded">arrow_downward</span></button>
                                            <button type="button" class="cms-icon-btn cms-icon-btn--danger" data-remove aria-label="{{ __('Remove') }}"><span class="material-symbols-rounded">delete</span></button>
                                        </div>
                                    </div>
                                    <div class="cms-grid">
                                        @foreach($rep['fields'] as $subKey => $subDef)
                                            @include('superadmin.cms._field', ['name' => "fields[$key][__ROW__][$subKey]", 'value' => $subDef['default'] ?? null, 'def' => $subDef])
                                        @endforeach
                                    </div>
                                </div>
                            </template>
                            <button type="button" class="tactile-btn btn-outline cms-add" data-add>
                                <span class="material-symbols-rounded" style="font-size: 18px;">add</span>
                                <span>{{ __('Add :item', ['item' => __($rep['item_label'])]) }}</span>
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="cms-save">
                    <button type="submit" class="tactile-btn btn-primary">
                        <span class="material-symbols-rounded" style="font-size: 18px;">save</span>
                        <span>{{ __('Save :section', ['section' => $def['name_ar']]) }}</span>
                    </button>
                </div>
            </form>
        </div>
    @endforeach
</div>
@endsection

@section('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
(function () {
    // Repeaters: add / remove / reorder rows. Row indexes only need to be unique;
    // the server re-numbers rows in the order they are submitted.
    let uid = Date.now();
    const renumber = (rows) => rows.querySelectorAll(':scope > [data-row]').forEach((row, i) => {
        const n = row.querySelector('[data-row-number]');
        if (n) n.textContent = i + 1;
    });

    document.querySelectorAll('[data-repeater]').forEach((block) => {
        const rows = block.querySelector('[data-rows]');
        const tpl = block.querySelector('[data-template]');

        block.querySelector('[data-add]').addEventListener('click', () => {
            const html = tpl.innerHTML.replaceAll('__ROW__', 'n' + (uid++));
            rows.insertAdjacentHTML('beforeend', html);
            renumber(rows);
            rows.lastElementChild.querySelector('input, textarea, select')?.focus();
        });

        rows.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            const row = btn.closest('[data-row]');
            if (btn.hasAttribute('data-remove')) {
                row.remove();
            } else if (btn.dataset.move === '-1' && row.previousElementSibling) {
                row.parentNode.insertBefore(row, row.previousElementSibling);
            } else if (btn.dataset.move === '1' && row.nextElementSibling) {
                row.parentNode.insertBefore(row.nextElementSibling, row);
            }
            renumber(rows);
        });
    });

    // Live icon preview.
    document.addEventListener('input', (e) => {
        if (!e.target.matches('[data-icon-input]')) return;
        const preview = e.target.parentNode.querySelector('.cms-icon-preview');
        if (preview) preview.textContent = e.target.value.trim();
    });

    // Return to the section that was just saved.
    const saved = @json(session('cms_section'));
    if (saved) document.getElementById('sec_' + saved)?.scrollIntoView({ block: 'start' });
})();
</script>
@endsection
