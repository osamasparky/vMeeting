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
    $titleSize = $density === 'compact' ? 'text-[14px]' : 'text-[15px]';

    $classes = "group relative flex flex-col items-start justify-between rounded-[16px] border border-[var(--ula-border-subtle)] bg-[var(--ula-surface-page)] {$padClass} transition-all duration-200 hover:-translate-y-0.5 hover:border-[var(--ula-border-strong)] hover:shadow-[var(--ula-shadow-sm)] hover:bg-[var(--ula-surface-hover)] focus:outline-none focus-visible:shadow-[var(--ula-focus-ring)] select-none text-start";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <div class="flex items-center justify-between w-full">
            <span class="material-symbols-rounded {{ $iconSize }} text-[var(--ula-highlight-default)] transition-transform duration-200 group-hover:scale-110">
                {{ $icon }}
            </span>
            @if($badge)
                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-[var(--ula-gold-200)] text-[var(--ula-gold-600)]">
                    {{ $badge }}
                </span>
            @endif
        </div>
        <div class="flex flex-col mt-2">
            <span class="{{ $titleSize }} font-medium text-[var(--ula-text-primary)] group-hover:text-[var(--ula-palm-900)]">
                {{ $title }}
            </span>
            @if($subtitle)
                <span class="text-[11px] text-[var(--ula-text-muted)] font-normal">
                    {{ $subtitle }}
                </span>
            @endif
        </div>
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }}>
        <div class="flex items-center justify-between w-full">
            <span class="material-symbols-rounded {{ $iconSize }} text-[var(--ula-highlight-default)] transition-transform duration-200 group-hover:scale-110">
                {{ $icon }}
            </span>
            @if($badge)
                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-[var(--ula-gold-200)] text-[var(--ula-gold-600)]">
                    {{ $badge }}
                </span>
            @endif
        </div>
        <div class="flex flex-col mt-2">
            <span class="{{ $titleSize }} font-medium text-[var(--ula-text-primary)]">
                {{ $title }}
            </span>
            @if($subtitle)
                <span class="text-[11px] text-[var(--ula-text-muted)] font-normal">
                    {{ $subtitle }}
                </span>
            @endif
        </div>
    </button>
@endif
