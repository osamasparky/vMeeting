@props([
    'variant' => 'default', // live, scheduled, attention, cancelled, stone, default, accent, filter
    'size' => 'md',         // sm, md, lg
    'dot' => false,
    'icon' => null,
])

@php
    $isChip = $variant === 'filter';
    $normalizedVariant = match($variant) {
        'live', 'active', 'success' => 'live',
        'scheduled', 'pending', 'warning', 'high' => 'scheduled',
        'attention', 'urgent', 'danger', 'suspended', 'error' => 'attention',
        'cancelled', 'stone', 'low' => 'cancelled',
        'accent' => 'accent',
        'filter' => 'filter',
        default => 'default',
    };
    $normalizedSize = match($size) {
        'sm', 'small' => 'sm',
        'lg', 'large', 'chip' => 'lg',
        default => 'md',
    };
@endphp

<span {{ $attributes->merge(['class' => "ula-badge ula-badge--{$normalizedVariant} ula-badge--{$normalizedSize}" . ($isChip ? ' ula-badge--chip' : '')]) }}>
    @if($dot)
        <span class="ula-badge__dot"></span>
    @endif
    @if($icon)
        <span class="material-symbols-rounded ula-badge__icon">{{ $icon }}</span>
    @endif
    <span>{{ $slot }}</span>
</span>

