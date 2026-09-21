@props([
    'label' => null,
    'name' => null,
    'checked' => false,
    'value' => '1',
    'id' => null,
    'disabled' => false,
])

@php
    $inputId = $id ?? ($name ?? 'checkbox_' . uniqid());
@endphp

{{-- Figma: Checkbox — box 20px, radius 6, border 1.5 border/strong, checked accent/default, gap 11 --}}
<label for="{{ $inputId }}" class="inline-flex items-center gap-[11px] cursor-pointer select-none group text-[15px] text-[var(--ula-text-primary)] {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
    <div class="relative flex items-center justify-center">
        <input
            type="checkbox"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ $checked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'peer sr-only']) }}
        >
        <div class="w-5 h-5 rounded-[var(--ula-radius-xs)] border-[1.5px] border-[var(--ula-border-strong)] bg-[var(--ula-surface-page)] transition-[background-color,border-color] duration-[var(--ula-duration-fast)] ease-[var(--ula-ease-out)] peer-checked:bg-[var(--ula-accent-default)] peer-checked:border-[var(--ula-accent-default)] peer-focus-visible:shadow-[var(--ula-focus-ring)] flex items-center justify-center text-[var(--ula-accent-fg)]">
            <span class="material-symbols-rounded text-[15px] opacity-0 peer-checked:opacity-100 font-bold transition-opacity duration-[var(--ula-duration-instant)]">
                check
            </span>
        </div>
    </div>
    @if($label || $slot->isNotEmpty())
        <span>{{ $label ?? $slot }}</span>
    @endif
</label>
