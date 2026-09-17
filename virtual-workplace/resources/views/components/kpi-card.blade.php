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
    'density' => 'compact',   // compact (18px pad, 34px num) or default (24px pad, 40px num)
])

@php
    $numValue = is_numeric($value) ? (float)$value : null;
    $isZero = $numValue === 0.0 || $value === '0' || $value === '0%';
    
    $padClass = $density === 'compact' ? 'p-[18px]' : 'p-5';
    $numClass = $density === 'compact' ? 'text-[34px]' : 'text-[40px]';

    $iconStyles = match($iconColor) {
        'gold' => 'bg-[var(--ula-tone-gold-bg)] text-[var(--ula-tone-gold-fg)]',
        'sage' => 'bg-[var(--ula-tone-palm-bg)] text-[var(--ula-tone-palm-fg)]',
        'terracotta' => 'bg-[var(--ula-tone-terracotta-bg)] text-[var(--ula-tone-terracotta-fg)]',
        'muted' => 'bg-[var(--ula-tone-stone-bg)] text-[var(--ula-tone-stone-fg)]',
        default => 'bg-[var(--ula-tone-palm-bg)] text-[var(--ula-tone-palm-fg)]',
    };
@endphp

<div {{ $attributes->merge(['class' => "relative flex flex-col justify-between rounded-[var(--ula-radius-lg)] border border-[var(--ula-border-subtle)] bg-[var(--ula-surface-card)] {$padClass} shadow-[var(--ula-shadow-xs)] transition-[border-color,box-shadow,transform] duration-[var(--ula-duration-base)] ease-[var(--ula-ease-out)] hover:border-[var(--ula-border-strong)] hover:shadow-[var(--ula-shadow-sm)]"]) }}>
    
    <!-- Top Row: Title & Role-colored Icon -->
    <div class="flex items-start justify-between gap-3 w-full">
        <div class="flex flex-col">
            <span class="text-[15px] font-medium text-[var(--ula-text-secondary)] leading-snug">
                {{ $title }}
            </span>
            @if($subtitle)
                <span class="text-[11px] text-[var(--ula-text-muted)] font-normal mt-0.5">
                    {{ $subtitle }}
                </span>
            @endif
        </div>

        @if($icon)
            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $isZero ? 'bg-[var(--ula-surface-sunken)] text-[var(--ula-text-muted)] opacity-60' : $iconStyles }}">
                <span class="material-symbols-rounded text-[20px]">{{ $icon }}</span>
            </div>
        @endif
    </div>

    <!-- Main Value & Optional Donut -->
    <div class="flex items-baseline justify-between mt-3 gap-3">
        <div class="flex flex-col">
            <!-- Hero Figure: Weight 300, 34px compact / 40px default, faded if zero -->
            <div class="flex items-baseline gap-1.5">
                <span class="{{ $numClass }} font-light font-[family-name:var(--ula-font-ar)] tracking-tight leading-[var(--ula-lh-metric)] {{ $isZero ? 'text-[var(--ula-text-muted)] opacity-60' : 'text-[var(--ula-text-primary)]' }}">
                    {{ $value }}
                </span>
            </div>
            
            @if($caption || $metric)
                <span class="text-[12px] text-[var(--ula-text-muted)] font-normal mt-1.5">
                    {{ $caption ?? $metric }}
                </span>
            @endif
        </div>

        @if($donut !== null)
            <div class="shrink-0 self-center">
                <x-donut-chart :percent="$donut" :size="$donutSize" />
            </div>
        @endif
    </div>

    <!-- Footer: Trend (if present) -->
    @if($trend)
        <div class="flex items-center gap-1.5 mt-3 pt-2.5 border-t border-[var(--ula-border-subtle)] text-[11px]">
            @if($trendDirection === 'up')
                <span class="inline-flex items-center text-[var(--ula-status-success)] font-semibold gap-0.5">
                    <span class="material-symbols-rounded text-[15px]">trending_up</span>
                    {{ $trend }}
                </span>
            @elseif($trendDirection === 'down')
                <span class="inline-flex items-center text-[var(--ula-status-danger)] font-semibold gap-0.5">
                    <span class="material-symbols-rounded text-[15px]">trending_down</span>
                    {{ $trend }}
                </span>
            @else
                <span class="inline-flex items-center text-[var(--ula-text-muted)] font-medium">
                    {{ $trend }}
                </span>
            @endif
            <span class="text-[var(--ula-text-muted)] font-normal">{{ __('vs last week') }}</span>
        </div>
    @endif
</div>
