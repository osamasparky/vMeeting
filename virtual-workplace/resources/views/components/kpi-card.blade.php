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
    
    $padClass = $density === 'compact' ? 'p-[18px]' : 'p-6';
    $numClass = $density === 'compact' ? 'text-[34px]' : 'text-[40px]';

    $iconStyles = match($iconColor) {
        'gold' => 'bg-[var(--nx-gold-200)] text-[var(--nx-gold-600)] dark:bg-[var(--nx-gold-600)]/20 dark:text-[var(--nx-gold-400)]',
        'sage' => 'bg-[var(--nx-palm-100)] text-[var(--nx-palm-700)] dark:bg-[var(--nx-palm-700)]/20 dark:text-[var(--nx-palm-300)]',
        'terracotta' => 'bg-[#faebe8] text-[var(--nx-status-attention)] dark:bg-[#5a2a22]/30 dark:text-[var(--nx-terracotta-400)]',
        'muted' => 'bg-[var(--nx-sand-200)] text-[var(--nx-text-muted)] dark:bg-white/10 dark:text-[var(--nx-text-muted)]',
        default => 'bg-[var(--nx-sand-200)] text-[var(--nx-status-live)] dark:bg-[var(--nx-palm-700)]/25 dark:text-[var(--nx-status-live)]',
    };
@endphp

<div {{ $attributes->merge(['class' => "relative flex flex-col justify-between rounded-[var(--nx-radius-lg)] border border-[var(--nx-border-subtle)] bg-[var(--nx-bg-surface)] {$padClass} shadow-[var(--nx-shadow-sm)] transition-all duration-200 hover:border-[var(--nx-border-strong)] hover:shadow-[var(--nx-shadow-md)]"]) }}>
    
    <!-- Top Row: Title & Role-colored Icon -->
    <div class="flex items-start justify-between gap-3 w-full">
        <div class="flex flex-col">
            <span class="text-[14px] font-medium text-[var(--nx-text-secondary)] leading-snug">
                {{ $title }}
            </span>
            @if($subtitle)
                <span class="text-[11px] text-[var(--nx-text-muted)] font-normal mt-0.5">
                    {{ $subtitle }}
                </span>
            @endif
        </div>

        @if($icon)
            <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 {{ $isZero ? 'bg-[var(--nx-sand-200)] text-[var(--nx-text-muted)] opacity-60' : $iconStyles }}">
                <span class="material-symbols-rounded text-[20px]">{{ $icon }}</span>
            </div>
        @endif
    </div>

    <!-- Main Value & Optional Donut -->
    <div class="flex items-baseline justify-between mt-3 gap-3">
        <div class="flex flex-col">
            <!-- Hero Figure: Weight 300, 34px compact / 40px default, faded if zero -->
            <div class="flex items-baseline gap-1.5">
                <span class="{{ $numClass }} font-light font-['IBM_Plex_Sans_Arabic',sans-serif] tracking-tight leading-none {{ $isZero ? 'text-[var(--nx-text-muted)] opacity-60' : 'text-[var(--nx-text-primary)]' }}">
                    {{ $value }}
                </span>
            </div>
            
            @if($caption || $metric)
                <span class="text-[12px] text-[var(--nx-text-muted)] font-normal mt-1.5">
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
        <div class="flex items-center gap-1.5 mt-3 pt-2.5 border-t border-[var(--nx-border-subtle)] text-[11px]">
            @if($trendDirection === 'up')
                <span class="inline-flex items-center text-[var(--nx-status-live)] font-semibold gap-0.5">
                    <span class="material-symbols-rounded text-[15px]">trending_up</span>
                    {{ $trend }}
                </span>
            @elseif($trendDirection === 'down')
                <span class="inline-flex items-center text-[var(--nx-status-attention)] font-semibold gap-0.5">
                    <span class="material-symbols-rounded text-[15px]">trending_down</span>
                    {{ $trend }}
                </span>
            @else
                <span class="inline-flex items-center text-[var(--nx-text-muted)] font-medium">
                    {{ $trend }}
                </span>
            @endif
            <span class="text-[var(--nx-text-muted)] font-normal">{{ __('vs last week') }}</span>
        </div>
    @endif
</div>
