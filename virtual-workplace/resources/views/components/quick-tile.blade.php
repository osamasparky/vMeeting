@props([
    'icon' => '',
    'title' => '',
    'subtitle' => null,
    'href' => null,
    'density' => 'default', // default, compact
    'badge' => null,
])

@php
    $padClass = $density === 'compact' ? 'p-3.5 gap-2.5' : 'p-4 gap-3';
    $iconSize = $density === 'compact' ? 'text-[24px]' : 'text-[26px]';
    $titleSize = $density === 'compact' ? 'text-[13px]' : 'text-[14px]';

    $classes = "group relative flex flex-col items-start justify-between rounded-[var(--nx-radius-lg)] border border-[var(--nx-border-subtle)] bg-[var(--nx-bg-surface)] {$padClass} transition-all duration-200 hover:-translate-y-0.5 hover:border-[var(--nx-border-strong)] hover:shadow-[var(--nx-shadow-md)] hover:bg-[var(--nx-bg-surface-hover)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--nx-accent)] select-none text-start";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <div class="flex items-center justify-between w-full">
            <span class="material-symbols-rounded {{ $iconSize }} text-[var(--nx-accent)] transition-transform duration-200 group-hover:scale-110">
                {{ $icon }}
            </span>
            @if($badge)
                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-[var(--nx-gold-200)] text-[var(--nx-gold-600)]">
                    {{ $badge }}
                </span>
            @endif
        </div>
        <div class="flex flex-col mt-2">
            <span class="{{ $titleSize }} font-medium text-[var(--nx-text-primary)] group-hover:text-[var(--nx-palm-900)]">
                {{ $title }}
            </span>
            @if($subtitle)
                <span class="text-[11px] text-[var(--nx-text-muted)] font-normal">
                    {{ $subtitle }}
                </span>
            @endif
        </div>
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }}>
        <div class="flex items-center justify-between w-full">
            <span class="material-symbols-rounded {{ $iconSize }} text-[var(--nx-accent)] transition-transform duration-200 group-hover:scale-110">
                {{ $icon }}
            </span>
            @if($badge)
                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-[var(--nx-gold-200)] text-[var(--nx-gold-600)]">
                    {{ $badge }}
                </span>
            @endif
        </div>
        <div class="flex flex-col mt-2">
            <span class="{{ $titleSize }} font-medium text-[var(--nx-text-primary)]">
                {{ $title }}
            </span>
            @if($subtitle)
                <span class="text-[11px] text-[var(--nx-text-muted)] font-normal">
                    {{ $subtitle }}
                </span>
            @endif
        </div>
    </button>
@endif
