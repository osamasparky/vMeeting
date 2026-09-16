@props([
    'padding' => 'default', // none, sm, default, lg
    'hover' => false,
    'header' => null,
    'footer' => null,
    'title' => null,
    'subtitle' => null,
    'action' => null,
    'variant' => 'default', // default, elevated, glass, dark
])

@php
    $padClass = match($padding) {
        'none' => 'p-0',
        'sm' => 'p-3.5 sm:p-4',
        'lg' => 'p-6 sm:p-8',
        default => 'p-5 sm:p-6',
    };

    $variantClass = match($variant) {
        'elevated' => 'bg-[var(--ula-surface-raised)] border border-[var(--ula-border-subtle)] shadow-[var(--ula-shadow-md)]',
        'glass' => 'bg-[var(--ula-surface-capsule)] backdrop-blur-[var(--ula-backdrop-blur)] border border-[var(--ula-border-subtle)] shadow-[var(--ula-shadow-sm)]',
        'dark' => 'bg-[var(--ula-surface-dark)] text-[var(--ula-text-on-dark)] border border-[var(--ula-border-on-dark)] shadow-[var(--ula-shadow-lg)]',
        default => 'bg-[var(--ula-surface-card)] border border-[var(--ula-border-subtle)] shadow-[var(--ula-shadow-xs)]',
    };

    $hoverClass = $hover ? 'transition-all duration-200 hover:border-[var(--ula-border-strong)] hover:shadow-[var(--ula-shadow-md)] hover:-translate-y-0.5' : 'transition-colors duration-200';
@endphp

<div {{ $attributes->merge(['class' => "rounded-[var(--ula-radius-lg)] {$variantClass} {$hoverClass} overflow-hidden"]) }}>
    @if($header || $title)
        <div class="flex items-center justify-between px-5 py-4 sm:px-6 border-b border-[var(--ula-border-subtle)]">
            @if($title)
                <div class="flex flex-col">
                    <h3 class="text-[18px] font-semibold text-[var(--ula-text-primary)] leading-[var(--ula-lh-heading)]">
                        {{ $title }}
                    </h3>
                    @if($subtitle)
                        <span class="text-[13px] text-[var(--ula-text-secondary)] mt-0.5">
                            {{ $subtitle }}
                        </span>
                    @endif
                </div>
            @else
                {{ $header }}
            @endif

            @if($action)
                <div class="shrink-0">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padClass }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-5 py-3.5 sm:px-6 bg-[var(--ula-surface-page-alt)] border-t border-[var(--ula-border-subtle)]">
            {{ $footer }}
        </div>
    @endif
</div>
