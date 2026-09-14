@props([
    'title' => '',
    'subtitle' => null,
    'value' => '',
    'metric' => null,
    'donut' => null,       // percentage e.g. 78
    'donutSize' => 'lg',   // lg, default, sm
    'trend' => null,       // e.g. "+12%" or "-5%"
    'trendDirection' => 'up', // up, down, neutral
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'relative flex flex-col justify-between rounded-[var(--nx-radius-lg)] border border-[var(--nx-border-subtle)] bg-[var(--nx-bg-surface)] p-6 shadow-[var(--nx-shadow-sm)] transition-all duration-200 hover:border-[var(--nx-border-strong)] hover:shadow-[var(--nx-shadow-md)]']) }}>
    
    <!-- Top Header -->
    <div class="flex items-start justify-between gap-3 w-full">
        <div class="flex flex-col">
            <span class="text-[15px] font-semibold text-[var(--nx-text-primary)] leading-tight">
                {{ $title }}
            </span>
            @if($subtitle)
                <span class="text-[12px] text-[var(--nx-text-muted)] font-normal mt-0.5">
                    {{ $subtitle }}
                </span>
            @endif
        </div>

        @if($icon)
            <div class="w-9 h-9 rounded-full bg-[var(--nx-sand-200)] flex items-center justify-center text-[var(--nx-accent)] shrink-0">
                <span class="material-symbols-rounded text-[20px]">{{ $icon }}</span>
            </div>
        @endif
    </div>

    <!-- Middle Body: Numbers + Donut -->
    <div class="flex items-center justify-between mt-4 gap-4">
        <div class="flex flex-col">
            <span class="text-[40px] font-light text-[var(--nx-text-primary)] font-['IBM_Plex_Sans_Arabic',sans-serif] leading-none tracking-tight">
                {{ $value }}
            </span>
            @if($metric)
                <span class="text-[13px] text-[var(--nx-text-secondary)] font-normal mt-2">
                    {{ $metric }}
                </span>
            @endif
        </div>

        @if($donut !== null)
            <div class="shrink-0">
                <x-donut-chart :percent="$donut" :size="$donutSize" />
            </div>
        @endif
    </div>

    <!-- Bottom Footer: Trend -->
    @if($trend)
        <div class="flex items-center gap-2 mt-4 pt-3 border-t border-[var(--nx-border-subtle)] text-[12px]">
            @if($trendDirection === 'up')
                <span class="inline-flex items-center text-[var(--nx-status-live)] font-semibold gap-0.5">
                    <span class="material-symbols-rounded text-[16px]">trending_up</span>
                    {{ $trend }}
                </span>
            @elseif($trendDirection === 'down')
                <span class="inline-flex items-center text-[var(--nx-status-attention)] font-semibold gap-0.5">
                    <span class="material-symbols-rounded text-[16px]">trending_down</span>
                    {{ $trend }}
                </span>
            @else
                <span class="inline-flex items-center text-[var(--nx-text-muted)] font-medium">
                    {{ $trend }}
                </span>
            @endif
            <span class="text-[var(--nx-text-muted)]">مقارنة بالأسبوع السابق</span>
        </div>
    @endif
</div>
