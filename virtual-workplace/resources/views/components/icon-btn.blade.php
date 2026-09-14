@props([
    'variant' => 'subtle', // subtle, primary, dark-toolbar, danger, ghost
    'size' => 'md',        // sm (32px), md (40px), lg (48px)
    'icon' => null,
    'href' => null,
    'type' => 'button',
    'rounded' => 'full',   // full, md, lg
    'active' => false,
])

@php
    $sizeClass = match($size) {
        'sm' => 'w-[32px] h-[32px] text-[18px]',
        'lg' => 'w-[48px] h-[48px] text-[24px]',
        default => 'w-[40px] h-[40px] text-[20px]',
    };

    $radiusClass = match($rounded) {
        'md' => 'rounded-[var(--nx-radius-md)]',
        'lg' => 'rounded-[var(--nx-radius-lg)]',
        default => 'rounded-full',
    };

    $variantClass = match($variant) {
        'primary' => 'bg-[var(--nx-palm-900)] text-white hover:bg-[var(--nx-palm-700)] shadow-[var(--nx-shadow-sm)]',
        'dark-toolbar' => 'bg-[rgba(237,230,217,0.08)] text-[var(--nx-sand-300)] border border-[rgba(237,230,217,0.15)] hover:bg-[rgba(237,230,217,0.16)] hover:text-white',
        'danger' => 'bg-[var(--nx-terracotta-100)] text-[var(--nx-terracotta-500)] hover:bg-[var(--nx-terracotta-500)] hover:text-white',
        'ghost' => 'bg-transparent text-[var(--nx-text-secondary)] hover:bg-[var(--nx-bg-surface-hover)] hover:text-[var(--nx-text-primary)]',
        default => 'bg-[var(--nx-bg-surface)] text-[var(--nx-text-secondary)] border border-[var(--nx-border-subtle)] hover:border-[var(--nx-border-strong)] hover:text-[var(--nx-text-primary)] hover:shadow-[var(--nx-shadow-sm)]',
    };

    if ($active) {
        $variantClass .= ' ring-2 ring-[var(--nx-accent)] bg-[var(--nx-sand-200)] text-[var(--nx-palm-900)]';
    }

    $classes = "inline-flex items-center justify-center transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--nx-accent)] {$sizeClass} {$radiusClass} {$variantClass}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <span class="material-symbols-rounded">{{ $icon ?? $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        <span class="material-symbols-rounded">{{ $icon ?? $slot }}</span>
    </button>
@endif
