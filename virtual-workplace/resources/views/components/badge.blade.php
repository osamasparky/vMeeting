@props([
    'variant' => 'live', // live, scheduled, attention, cancelled, stone, filter, accent
    'size' => 'md',      // sm, md
    'dot' => false,
    'icon' => null,
])

@php
    /* Figma: Tag — pill, 29h, pad-x 12, gap 6, dot 7px, tonal fill, no border.
       Chip (variant "filter") — pill, 36h, pad-x 16, surface/card + border. */
    $isChip = $variant === 'filter';

    $variantClass = match($variant) {
        'live' => 'bg-[var(--ula-tone-palm-bg)] text-[var(--ula-tone-palm-fg)]',
        'scheduled' => 'bg-[var(--ula-tone-gold-bg)] text-[var(--ula-tone-gold-fg)]',
        'attention' => 'bg-[var(--ula-tone-terracotta-bg)] text-[var(--ula-tone-terracotta-fg)]',
        'cancelled' => 'bg-[var(--ula-tone-stone-bg)] text-[var(--ula-tone-stone-fg)]',
        'accent' => 'bg-[var(--ula-highlight-default)] text-[var(--ula-text-on-gold)]',
        'filter' => 'bg-[var(--ula-surface-card)] text-[var(--ula-text-body)] border border-[var(--ula-border-default)] hover:bg-[var(--ula-surface-hover)] hover:border-[var(--ula-border-hover)]',
        default => 'bg-[var(--ula-tone-sand-bg)] text-[var(--ula-tone-sand-fg)]',
    };

    $dotColor = match($variant) {
        'live' => 'bg-[var(--ula-tone-palm-dot)]',
        'scheduled' => 'bg-[var(--ula-tone-gold-dot)]',
        'attention' => 'bg-[var(--ula-tone-terracotta-dot)]',
        'cancelled' => 'bg-[var(--ula-tone-stone-dot)]',
        default => 'bg-[var(--ula-highlight-default)]',
    };

    $sizeClass = $isChip
        ? 'h-[36px] px-4 text-[15px] gap-2'
        : ($size === 'sm' ? 'h-[24px] px-2.5 text-[12px] gap-1.5' : 'h-[29px] px-3 text-[13px] gap-1.5');
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center justify-center whitespace-nowrap font-medium rounded-[var(--ula-radius-pill)] leading-none select-none transition-[background-color,border-color] duration-[var(--ula-duration-fast)] ease-[var(--ula-ease-out)] {$sizeClass} {$variantClass}"]) }}>
    @if($dot)
        <span class="w-[7px] h-[7px] rounded-full {{ $dotColor }} shrink-0"></span>
    @endif
    @if($icon)
        <span class="material-symbols-rounded text-[16px] leading-none">{{ $icon }}</span>
    @endif
    <span>{{ $slot }}</span>
</span>
