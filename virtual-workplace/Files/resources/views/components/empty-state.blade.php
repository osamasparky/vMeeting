@props([
    'icon' => 'inbox',
    'title' => 'لا توجد بيانات حالياً',
    'subtitle' => 'No records found',
    'actionLabel' => null,
    'actionHref' => null,
    'actionIcon' => null,
])

<div class="flex flex-col items-center justify-center p-12 text-center select-none w-full rounded-[var(--ula-radius-lg)] border border-dashed border-[var(--ula-border-default)] bg-[var(--ula-surface-card)]">
    <div class="w-16 h-16 rounded-full bg-[var(--ula-surface-accent-soft)] flex items-center justify-center text-[var(--ula-icon-accent)] mb-4">
        <span class="material-symbols-rounded text-[32px]">{{ $icon }}</span>
    </div>

    <h4 class="text-[17px] font-semibold text-[var(--ula-text-primary)] leading-tight">
        {{ $title }}
    </h4>
    
    @if($subtitle)
        <p class="text-[13px] text-[var(--ula-text-secondary)] mt-1 max-w-sm">
            {{ $subtitle }}
        </p>
    @endif

    @if($actionLabel)
        <div class="mt-6">
            <x-btn :href="$actionHref" :icon="$actionIcon" size="md" variant="primary">
                {{ $actionLabel }}
            </x-btn>
        </div>
    @endif

    {{ $slot }}
</div>
