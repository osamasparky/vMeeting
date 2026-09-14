@props([
    'variant' => 'primary', // primary, secondary, ghost, danger, outline, nav-cta
    'size' => 'md',        // sm (36px), md (44px), lg (52px)
    'icon' => null,
    'iconPosition' => 'start', // start, end
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'pill' => true,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 select-none cursor-pointer focus:outline-none disabled:opacity-45 disabled:pointer-events-none disabled:cursor-not-allowed';
    
    // Sizing
    $sizeClasses = match($size) {
        'sm' => 'h-[36px] px-3.5 text-[13px] gap-1.5 font-semibold',
        'lg' => 'h-[52px] px-6 text-[17px] gap-2.5 font-semibold',
        default => 'h-[44px] px-5 text-[15px] gap-2 font-semibold',
    };

    // Radii
    $radiusClass = $pill ? 'rounded-full' : 'rounded-[var(--nx-radius-md)]';

    // Variants
    $variantClasses = match($variant) {
        'secondary' => 'bg-[var(--nx-sand-200)] text-[var(--nx-palm-900)] hover:bg-[var(--nx-sand-300)] active:scale-[0.98] border border-[var(--nx-border-subtle)]',
        'outline' => 'bg-transparent text-[var(--nx-palm-900)] border border-[var(--nx-border-default)] hover:border-[var(--nx-border-strong)] hover:bg-[var(--nx-bg-surface-hover)] active:scale-[0.98]',
        'ghost' => 'bg-transparent text-[var(--nx-text-primary)] hover:bg-[var(--nx-bg-surface-hover)] active:scale-[0.98]',
        'danger' => 'bg-[var(--nx-terracotta-500)] text-white hover:brightness-110 active:scale-[0.98] shadow-[var(--nx-shadow-sm)]',
        'nav-cta' => 'bg-[#ede6d9] text-[#142b24] hover:bg-[#f9f6ef] active:scale-[0.98] font-medium shadow-[var(--nx-shadow-sm)]',
        default => 'bg-[var(--nx-palm-900)] text-[#ffffff] hover:bg-[var(--nx-palm-700)] active:scale-[0.98] shadow-[var(--nx-shadow-sm)] hover:shadow-[var(--nx-shadow-md)] hover:-translate-y-[1px]',
    };

    $focusClass = 'focus-visible:ring-2 focus-visible:ring-[var(--nx-accent)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--nx-bg-surface)]';
    $classes = "{$baseClasses} {$sizeClasses} {$radiusClass} {$variantClasses} {$focusClass}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'start')
            <span class="material-symbols-rounded text-[1.2em] leading-none">{{ $icon }}</span>
        @endif
        <span>{{ $slot }}</span>
        @if($icon && $iconPosition === 'end')
            <span class="material-symbols-rounded text-[1.2em] leading-none nx-mirror-rtl">{{ $icon }}</span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'start')
            <span class="material-symbols-rounded text-[1.2em] leading-none">{{ $icon }}</span>
        @endif
        <span>{{ $slot }}</span>
        @if($icon && $iconPosition === 'end')
            <span class="material-symbols-rounded text-[1.2em] leading-none nx-mirror-rtl">{{ $icon }}</span>
        @endif
    </button>
@endif
