@props([
    'variant' => 'live', // live, scheduled, attention, cancelled, stone, filter, accent
    'size' => 'md',      // sm, md
    'dot' => false,
    'icon' => null,
])

@php
    $variantClass = match($variant) {
        'live' => 'bg-[var(--nx-status-live-bg)] text-[var(--nx-status-live)] border-[rgba(60,107,76,0.2)]',
        'scheduled' => 'bg-[var(--nx-status-scheduled-bg)] text-[var(--nx-gold-600)] border-[rgba(211,165,83,0.3)]',
        'attention' => 'bg-[var(--nx-status-attention-bg)] text-[var(--nx-status-attention)] border-[rgba(154,88,39,0.25)]',
        'cancelled' => 'bg-[var(--nx-status-cancelled-bg)] text-[var(--nx-status-cancelled)] border-[rgba(142,135,124,0.25)]',
        'accent' => 'bg-[var(--nx-gold-200)] text-[var(--nx-palm-900)] border-[rgba(211,165,83,0.3)]',
        'filter' => 'bg-[var(--nx-bg-surface)] text-[var(--nx-text-primary)] border-[var(--nx-border-default)] hover:border-[var(--nx-border-strong)]',
        default => 'bg-[var(--nx-sand-200)] text-[var(--nx-text-secondary)] border-[var(--nx-border-subtle)]',
    };

    $dotColor = match($variant) {
        'live' => 'bg-[var(--nx-status-live)]',
        'scheduled' => 'bg-[var(--nx-status-scheduled)]',
        'attention' => 'bg-[var(--nx-status-attention)]',
        'cancelled' => 'bg-[var(--nx-status-cancelled)]',
        default => 'bg-[var(--nx-accent)]',
    };

    $sizeClass = $size === 'sm' ? 'px-2 py-0.5 text-[11px] gap-1' : 'px-2.5 py-1 text-[12px] gap-1.5';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center justify-center font-semibold rounded-full border {$sizeClass} {$variantClass} select-none leading-none"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} shrink-0"></span>
    @endif
    @if($icon)
        <span class="material-symbols-rounded text-[14px] leading-none">{{ $icon }}</span>
    @endif
    <span>{{ $slot }}</span>
</span>
