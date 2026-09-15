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
        'elevated' => 'bg-[var(--nx-bg-surface-elevated)] border border-[var(--nx-border-subtle)] shadow-[var(--nx-shadow-md)]',
        'glass' => 'bg-[var(--nx-bg-capsule)] backdrop-blur-[var(--nx-backdrop-blur)] border border-[var(--nx-border-subtle)] shadow-[var(--nx-shadow-sm)]',
        'dark' => 'bg-[var(--nx-palm-900)] text-white border border-[var(--nx-border-on-dark)] shadow-[var(--nx-shadow-lg)]',
        default => 'bg-[var(--nx-bg-surface)] border border-[var(--nx-border-subtle)] shadow-[var(--nx-shadow-sm)]',
    };

    $hoverClass = $hover ? 'transition-all duration-200 hover:border-[var(--nx-border-strong)] hover:shadow-[var(--nx-shadow-md)] hover:-translate-y-0.5' : 'transition-colors duration-200';
@endphp

<div {{ $attributes->merge(['class' => "rounded-[var(--nx-radius-lg)] {$variantClass} {$hoverClass} overflow-hidden"]) }}>
    @if($header || $title)
        <div class="flex items-center justify-between px-5 py-4 sm:px-6 border-b border-[var(--nx-border-subtle)]">
            @if($title)
                <div class="flex flex-col">
                    <h3 class="text-[16px] font-semibold text-[var(--nx-text-primary)] leading-tight">
                        {{ $title }}
                    </h3>
                    @if($subtitle)
                        <span class="text-[12px] text-[var(--nx-text-muted)] mt-0.5">
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
        <div class="px-5 py-3.5 sm:px-6 bg-[var(--nx-sand-100)] border-t border-[var(--nx-border-subtle)]">
            {{ $footer }}
        </div>
    @endif
</div>
