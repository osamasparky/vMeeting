@props([
    'title' => '',
    'subtitle' => null,
    'value' => '0',
    'caption' => null,
    'metric' => null,
    'icon' => null,
    'iconColor' => 'emerald', // emerald, gold, sage, terracotta, muted
    'donut' => null,          // percentage e.g. 78 (optional)
    'donutSize' => 'default', // default (112px), lg (126px), sm (80px)
    'trend' => null,          // e.g. "+12%" or "-5%"
    'trendDirection' => 'up', // up, down, neutral
    'density' => 'compact',   // kept for compatibility; every KPI uses the Screen 48 card size
])

{{--
    KPI card — design-reference "Screen 48 - Super Admin Dashboard": 20px padding, 40px tonal icon
    tile, 30px IBM Plex Mono figure (end-aligned), then a row of tone chips.
    Chips: pass <x-kpi-chip> elements in the slot. A plain `caption` renders as one neutral chip.
--}}
@php
    $iconStyles = match($iconColor) {
        'gold' => 'bg-[var(--ula-tone-gold-bg)] text-[var(--ula-tone-gold-fg)]',
        'terracotta' => 'bg-[var(--ula-tone-terracotta-bg)] text-[var(--ula-tone-terracotta-fg)]',
        'muted' => 'bg-[var(--ula-tone-stone-bg)] text-[var(--ula-tone-stone-fg)]',
        default => 'bg-[var(--ula-tone-palm-bg)] text-[var(--ula-tone-palm-fg)]', // emerald, sage
    };
    $chips = trim((string) $slot);
    $captionText = $caption ?? $metric;
@endphp

<div {{ $attributes->merge(['class' => "flex flex-col gap-[14px] rounded-[var(--ula-radius-lg)] border border-[var(--ula-border-subtle)] bg-[var(--ula-surface-card)] shadow-[var(--ula-shadow-xs)] transition-[border-color,box-shadow] duration-[var(--ula-duration-base)] ease-[var(--ula-ease-out)] hover:border-[var(--ula-border-strong)] hover:shadow-[var(--ula-shadow-sm)]", 'style' => 'padding: var(--ula-space-6);']) }}>

    <div class="flex items-center justify-between gap-3">
        <div class="flex min-w-0 flex-col">
            <span class="text-[15px] font-medium leading-snug text-[var(--ula-text-secondary)]">{{ $title }}</span>
            @if($subtitle)
                <span class="mt-0.5 text-[12px] text-[var(--ula-text-muted)]">{{ $subtitle }}</span>
            @endif
        </div>
        @if($icon)
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[var(--ula-radius-sm)] {{ $iconStyles }}">
                <span class="material-symbols-rounded text-[22px]" aria-hidden="true">{{ $icon }}</span>
            </span>
        @endif
    </div>

    <div class="flex items-center justify-between gap-3">
        {{-- data-kpi-value: hook for scripts that update the figure live (target it through the card's id). --}}
        <span data-kpi-value style="font-family: var(--ula-font-mono);" class="flex-1 text-end text-[30px] font-normal leading-[1.1] text-[var(--ula-text-primary)] [direction:ltr] [unicode-bidi:isolate]">{{ $value }}</span>
        @if($donut !== null)
            <x-donut-chart :percent="$donut" :size="$donutSize" />
        @endif
    </div>

    @if($chips !== '' || $captionText)
        <div class="flex flex-wrap gap-1.5">
            @if($chips !== '')
                {{ $slot }}
            @else
                <x-kpi-chip tone="stone">{{ $captionText }}</x-kpi-chip>
            @endif
        </div>
    @endif

    @if($trend)
        <div class="flex items-center gap-1.5 border-t border-[var(--ula-border-subtle)] text-[12px]" style="padding-top: 10px;">
            <span class="inline-flex items-center gap-0.5 font-semibold {{ $trendDirection === 'down' ? 'text-[var(--ula-status-danger)]' : ($trendDirection === 'up' ? 'text-[var(--ula-status-success)]' : 'text-[var(--ula-text-muted)]') }}">
                @if($trendDirection !== 'neutral')
                    <span class="material-symbols-rounded text-[15px]" aria-hidden="true">{{ $trendDirection === 'down' ? 'trending_down' : 'trending_up' }}</span>
                @endif
                <span class="[direction:ltr] [unicode-bidi:isolate]">{{ $trend }}</span>
            </span>
            <span class="text-[var(--ula-text-muted)]">{{ __('vs last week') }}</span>
        </div>
    @endif
</div>
