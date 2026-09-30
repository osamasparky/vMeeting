@props([
    'icon' => null,
    'label' => '',
    'value' => '0',
])

{{-- Compact secondary stat — design-reference "Screen 48": sunken card, 36px icon tile, label, 17px mono value. --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-3 rounded-[var(--ula-radius-md)] bg-[var(--ula-surface-page-alt)]', 'style' => 'padding: 14px var(--ula-space-5);']) }}>
    @if($icon)
        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--ula-radius-sm)] bg-[var(--ula-surface-page)] text-[var(--ula-icon-accent)]">
            <span class="material-symbols-rounded text-[20px]" aria-hidden="true">{{ $icon }}</span>
        </span>
    @endif
    <span class="min-w-0 flex-1 text-[14px] text-[var(--ula-text-body)]">{{ $label }}</span>
    <span data-kpi-value style="font-family: var(--ula-font-mono);" class="text-[17px] font-medium text-[var(--ula-text-primary)] [direction:ltr] [unicode-bidi:isolate]">{{ $value }}</span>
</div>
