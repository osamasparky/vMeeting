@props([
    'variant' => 'subtle', // subtle, primary, dark-toolbar, danger, ghost
    'size' => 'md',        // sm (32px), md (40px), lg (48px)
    'icon' => null,
    'href' => null,
    'type' => 'button',
    'rounded' => 'md',     // md (12px), lg, full (pill)
    'active' => false,
    'dot' => false,
])

@php
    $normalizedVariant = match($variant) {
        'primary', 'accent' => 'primary',
        'danger' => 'danger',
        'ghost' => 'ghost',
        default => 'subtle',
    };
    $normalizedSize = match($size) {
        'sm', 'small' => 'sm',
        'lg', 'large' => 'lg',
        default => 'md',
    };
    $pillClass = $rounded === 'full' ? ' ula-icon-btn--pill' : '';
    $activeClass = $active ? ' active' : '';
    $classes = "ula-icon-btn ula-icon-btn--{$normalizedVariant} ula-icon-btn--{$normalizedSize}{$pillClass}{$activeClass}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <span class="material-symbols-rounded">{{ $icon ?? $slot }}</span>
        @if($dot)
            <span style="position: absolute; top: 2px; right: 2px; width: 8px; height: 8px; border-radius: 9999px; background: var(--ula-tone-terracotta-dot, #b46c34); box-shadow: 0 0 0 2px var(--ula-surface-card, #ffffff);"></span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        <span class="material-symbols-rounded">{{ $icon ?? $slot }}</span>
        @if($dot)
            <span style="position: absolute; top: 2px; right: 2px; width: 8px; height: 8px; border-radius: 9999px; background: var(--ula-tone-terracotta-dot, #b46c34); box-shadow: 0 0 0 2px var(--ula-surface-card, #ffffff);"></span>
        @endif
    </button>
@endif

