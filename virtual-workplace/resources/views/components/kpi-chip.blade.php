@props([
    'tone' => 'stone', // palm, gold, terracotta, stone
    'n' => null,       // optional leading figure, rendered in mono and isolated LTR
])

{{-- Tone chip under a KPI figure — design-reference "Screen 48": 24px pill, 12px/500 label. --}}
@php
    $toneClass = match($tone) {
        'palm' => 'bg-[var(--ula-tone-palm-bg)] text-[var(--ula-tone-palm-fg)]',
        'gold' => 'bg-[var(--ula-tone-gold-bg)] text-[var(--ula-tone-gold-fg)]',
        'terracotta' => 'bg-[var(--ula-tone-terracotta-bg)] text-[var(--ula-tone-terracotta-fg)]',
        default => 'bg-[var(--ula-tone-stone-bg)] text-[var(--ula-tone-stone-fg)]',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex h-6 items-center gap-1 whitespace-nowrap rounded-[var(--ula-radius-pill)] text-[12px] font-medium {$toneClass}", 'style' => 'padding-inline: 10px;']) }}>
    @if($n !== null)
        <span style="font-family: var(--ula-font-mono);" class="[direction:ltr] [unicode-bidi:isolate]">{{ $n }}</span>
    @endif
    {{ $slot }}
</span>
