@props([
    'variant' => 'subtle', // subtle (Outline), primary (Accent), dark-toolbar, danger, ghost
    'size' => 'md',        // sm (32px), md (40px), lg (48px)
    'icon' => null,
    'href' => null,
    'type' => 'button',
    'rounded' => 'md',     // md (Figma: 12px), lg, full
    'active' => false,
    'dot' => false,        // Figma: 8px notification dot, 1.5px surface ring
])

@php
    /* Figma: Icon Button 40×40, radius 12, icon 20 */
    $sizeClass = match($size) {
        'sm' => 'w-[32px] h-[32px] text-[18px]',
        'lg' => 'w-[48px] h-[48px] text-[24px]',
        default => 'w-[40px] h-[40px] text-[20px]',
    };

    $radiusClass = match($rounded) {
        'lg' => 'rounded-[var(--ula-radius-md)]',
        'full' => 'rounded-[var(--ula-radius-pill)]',
        default => 'rounded-[12px]',
    };

    $variantClass = match($variant) {
        'primary' => 'bg-[var(--ula-accent-default)] border border-[var(--ula-accent-default)] text-[var(--ula-icon-on-accent)] hover:bg-[var(--ula-accent-hover)] hover:border-[var(--ula-accent-hover)] shadow-[var(--ula-shadow-xs)]',
        'dark-toolbar' => 'bg-[var(--ula-control-dark-fill)] border border-[var(--ula-control-dark-border)] text-[var(--ula-icon-on-dark-subtle)] hover:bg-[var(--ula-control-dark-fill-hover)] hover:text-[var(--ula-icon-on-dark)]',
        'danger' => 'bg-[var(--ula-surface-danger-soft)] border border-transparent text-[var(--ula-icon-danger)] hover:bg-[var(--ula-status-danger)] hover:text-[var(--ula-text-on-accent)]',
        'ghost' => 'bg-transparent border border-transparent text-[var(--ula-icon-secondary)] hover:bg-[var(--ula-surface-hover)] hover:text-[var(--ula-icon-primary)]',
        default => 'bg-[var(--ula-surface-card)] border border-[var(--ula-border-default)] text-[var(--ula-icon-secondary)] hover:bg-[var(--ula-surface-hover)] hover:border-[var(--ula-border-hover)] hover:text-[var(--ula-icon-primary)]',
    };

    if ($active) {
        $variantClass = 'bg-[var(--ula-surface-accent-soft)] border border-[var(--ula-border-focus)] text-[var(--ula-icon-accent)]';
    }

    $classes = "relative inline-flex items-center justify-center transition-[background-color,border-color,color,box-shadow] duration-[var(--ula-duration-fast)] ease-[var(--ula-ease-out)] focus:outline-none focus-visible:shadow-[var(--ula-focus-ring)] {$sizeClass} {$radiusClass} {$variantClass}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <span class="material-symbols-rounded">{{ $icon ?? $slot }}</span>
        @if($dot)
            <span class="absolute top-0 end-0 w-[8px] h-[8px] rounded-full bg-[var(--ula-tone-terracotta-dot)] ring-[1.5px] ring-[var(--ula-surface-card)]"></span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        <span class="material-symbols-rounded">{{ $icon ?? $slot }}</span>
        @if($dot)
            <span class="absolute top-0 end-0 w-[8px] h-[8px] rounded-full bg-[var(--ula-tone-terracotta-dot)] ring-[1.5px] ring-[var(--ula-surface-card)]"></span>
        @endif
    </button>
@endif
