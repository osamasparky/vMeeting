@props([
    'variant' => 'primary', // primary, secondary, ghost, danger, outline, nav-cta
    'size' => 'md',        // sm (36px), md (44px), lg (52px)
    'icon' => null,
    'iconPosition' => 'start', // start, end
    'iconOnly' => false,
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
    'pill' => false,       // Set true for rounded pill (9999px)
])

@php
    /* ══════════════════════════════════════════════════════════════════════
       ULASPACE FIGMA SPECIFICATIONS — BUTTONS (الأنواع والمقاسات)
       ══════════════════════════════════════════════════════════════════════ */
    $validVariants = ['primary', 'secondary', 'ghost', 'danger', 'outline', 'nav-cta'];
    $appliedVariant = in_array($variant, $validVariants) ? $variant : 'primary';

    $validSizes = ['sm', 'md', 'lg'];
    $appliedSize = in_array($size, $validSizes) ? $size : 'md';

    $classes = "ula-btn ula-btn--{$appliedVariant} ula-btn--{$appliedSize}";
    if ($pill) {
        $classes .= " ula-btn--pill";
    }
    if ($iconOnly) {
        $classes .= " ula-btn--icon-only";
    }

    $iconClass = "material-symbols-rounded shrink-0";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <span class="w-[18px] h-[18px] rounded-full border-2 border-current border-t-transparent animate-spin shrink-0"></span>
        @elseif($icon && $iconPosition === 'start')
            <span class="{{ $iconClass }}">{{ $icon }}</span>
        @endif
        @if(!$iconOnly)
            <span>{{ $slot }}</span>
        @endif
        @if(!$loading && $icon && $iconPosition === 'end')
            <span class="{{ $iconClass }} ula-mirror-rtl">{{ $icon }}</span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ ($disabled || $loading) ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <span class="w-[18px] h-[18px] rounded-full border-2 border-current border-t-transparent animate-spin shrink-0"></span>
        @elseif($icon && $iconPosition === 'start')
            <span class="{{ $iconClass }}">{{ $icon }}</span>
        @endif
        @if(!$iconOnly)
            <span>{{ $slot }}</span>
        @endif
        @if(!$loading && $icon && $iconPosition === 'end')
            <span class="{{ $iconClass }} ula-mirror-rtl">{{ $icon }}</span>
        @endif
    </button>
@endif
