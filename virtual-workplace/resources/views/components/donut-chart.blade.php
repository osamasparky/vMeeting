@props([
    'percent' => 0,       // 0 to 100
    'size' => 'default',  // default (112px), lg (126px), sm (80px)
    'label' => null,      // e.g. "78%"
    'caption' => null,    // sub-label under percentage
    'accentColor' => 'var(--nx-accent)',
    'trackColor' => 'var(--nx-stone-track)',
])

@php
    $dim = match($size) {
        'lg' => 126,
        'sm' => 80,
        default => 112,
    };

    $stroke = match($size) {
        'lg' => 16,
        'sm' => 10,
        default => 15,
    };

    $radius = ($dim - $stroke) / 2;
    $circumference = 2 * pi() * $radius;
    $offset = $circumference - (($percent / 100) * $circumference);
    
    $textSize = match($size) {
        'lg' => 'text-[26px]',
        'sm' => 'text-[17px]',
        default => 'text-[23px]',
    };

    $displayLabel = $label ?? ($percent . '%');
@endphp

<div class="relative inline-flex items-center justify-center select-none" style="width: {{ $dim }}px; height: {{ $dim }}px;">
    <svg width="{{ $dim }}" height="{{ $dim }}" viewBox="0 0 {{ $dim }} {{ $dim }}" class="rotate-[-90deg] transform">
        <!-- Track circle -->
        <circle 
            cx="{{ $dim / 2 }}" 
            cy="{{ $dim / 2 }}" 
            r="{{ $radius }}" 
            fill="transparent" 
            stroke="{{ $trackColor }}" 
            stroke-width="{{ $stroke }}"
        />
        <!-- Value Arc -->
        <circle 
            cx="{{ $dim / 2 }}" 
            cy="{{ $dim / 2 }}" 
            r="{{ $radius }}" 
            fill="transparent" 
            stroke="{{ $accentColor }}" 
            stroke-width="{{ $stroke }}"
            stroke-dasharray="{{ $circumference }}"
            stroke-dashoffset="{{ $offset }}"
            stroke-linecap="round"
            class="transition-all duration-700 ease-out"
        />
    </svg>

    <!-- Center content -->
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
        <span class="font-semibold text-[var(--nx-text-primary)] {{ $textSize }} font-['IBM_Plex_Sans_Arabic',sans-serif] leading-none">
            {{ $displayLabel }}
        </span>
        @if($caption)
            <span class="text-[10px] text-[var(--nx-text-secondary)] font-normal mt-1 leading-tight max-w-[80%]">
                {{ $caption }}
            </span>
        @endif
    </div>
</div>
