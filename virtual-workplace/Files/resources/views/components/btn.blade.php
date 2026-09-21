@props([
    'variant' => 'primary', // primary, secondary, ghost, danger, outline, nav-cta
    'size' => 'md',        // sm (36px), md (44px), lg (52px)
    'icon' => null,
    'iconPosition' => 'start', // start, end
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
    'pill' => false,       // Figma buttons use radius 10/14/18; set true for a pill
])

@php
    /* Figma: Button Primary/Secondary/Ghost/Danger — Small 36h pad-x 16 r10,
       Medium 44h pad-x 22 r14, Large 52h pad-x 26 r18, gap 8, text Button/* */
    $baseClasses = 'inline-flex items-center justify-center select-none cursor-pointer border transition-[background-color,border-color,box-shadow,transform] focus:outline-none disabled:pointer-events-none disabled:cursor-not-allowed';
    $baseClasses .= ' duration-[var(--ula-duration-fast)] ease-[var(--ula-ease-out)]';

    $isGhost = in_array($variant, ['ghost', 'outline']);

    $sizeClasses = match($size) {
        'sm' => ($isGhost ? 'px-3 ' : 'px-4 ') . 'h-[36px] text-[13px] gap-2 font-semibold',
        'lg' => ($isGhost ? 'px-5 ' : 'px-[26px] ') . 'h-[52px] text-[17px] gap-2 font-semibold',
        default => ($isGhost ? 'px-[18px] ' : 'px-[22px] ') . 'h-[44px] text-[15px] gap-2 font-semibold',
    };

    $radiusClass = $pill ? 'rounded-[var(--ula-radius-pill)]' : match($size) {
        'sm' => 'rounded-[var(--ula-radius-sm)]',
        'lg' => 'rounded-[18px]',
        default => 'rounded-[var(--ula-radius-md)]',
    };

    $disabledClasses = 'disabled:bg-[var(--ula-surface-sunken)] disabled:border-[var(--ula-border-subtle)] disabled:text-[var(--ula-text-muted)] disabled:shadow-none';

    $variantClasses = match($variant) {
        'secondary' => 'bg-[var(--ula-surface-card)] border-[var(--ula-border-strong)] text-[var(--ula-text-strong)] hover:bg-[var(--ula-surface-hover)] hover:border-[var(--ula-border-hover)] active:bg-[var(--ula-surface-pressed)]',
        'outline' => 'bg-transparent border-[var(--ula-border-default)] text-[var(--ula-text-strong)] hover:bg-[var(--ula-surface-hover)] hover:border-[var(--ula-border-hover)] active:bg-[var(--ula-surface-pressed)]',
        'ghost' => 'bg-transparent border-transparent text-[var(--ula-text-link)] hover:bg-[var(--ula-surface-accent-soft)] active:bg-[var(--ula-surface-pressed)]',
        'danger' => 'bg-[var(--ula-status-danger)] border-[var(--ula-status-danger)] text-[var(--ula-text-on-accent)] hover:bg-[var(--ula-status-danger-hover)] hover:border-[var(--ula-status-danger-hover)]',
        'nav-cta' => 'bg-[var(--ula-control-cta-on-dark)] border-transparent text-[var(--ula-text-on-cta)] font-medium hover:bg-[var(--ula-control-cta-on-dark-hover)] shadow-[var(--ula-shadow-xs)]',
        default => 'bg-[var(--ula-accent-default)] border-[var(--ula-accent-default)] text-[var(--ula-accent-fg)] hover:bg-[var(--ula-accent-hover)] hover:border-[var(--ula-accent-hover)] active:bg-[var(--ula-accent-press)] shadow-[var(--ula-shadow-xs)]',
    };

    $focusClass = 'focus-visible:shadow-[var(--ula-focus-ring)]';
    $classes = "{$baseClasses} {$sizeClasses} {$radiusClass} {$variantClasses} {$disabledClasses} {$focusClass}";
    $iconClass = 'material-symbols-rounded text-[1.25em] leading-none';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <span class="w-[18px] h-[18px] rounded-full border-2 border-current border-t-transparent animate-spin shrink-0"></span>
        @elseif($icon && $iconPosition === 'start')
            <span class="{{ $iconClass }}">{{ $icon }}</span>
        @endif
        <span>{{ $slot }}</span>
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
        <span>{{ $slot }}</span>
        @if(!$loading && $icon && $iconPosition === 'end')
            <span class="{{ $iconClass }} ula-mirror-rtl">{{ $icon }}</span>
        @endif
    </button>
@endif
