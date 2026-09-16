@props([
    'label' => null,
    'name' => null,
    'checked' => false,
    'value' => '1',
    'id' => null,
    'disabled' => false,
])

@php
    $switchId = $id ?? ($name ?? 'switch_' . uniqid());
@endphp

{{-- Figma: Switch — track 44×26 pill, pad 3, off control/track-off, on accent/default, knob 20, gap 16 --}}
<label for="{{ $switchId }}" class="inline-flex items-center justify-between gap-4 cursor-pointer select-none group w-full max-w-[320px] {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
    @if($label || $slot->isNotEmpty())
        <span class="text-[15px] font-medium text-[var(--ula-text-primary)]">
            {{ $label ?? $slot }}
        </span>
    @endif

    <div class="relative flex items-center shrink-0">
        <input
            type="checkbox"
            id="{{ $switchId }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ $checked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'peer sr-only']) }}
        >
        <div class="w-[44px] h-[26px] rounded-[var(--ula-radius-pill)] bg-[var(--ula-control-track-off)] p-[3px] transition-colors duration-[var(--ula-duration-fast)] ease-[var(--ula-ease-out)] peer-checked:bg-[var(--ula-accent-default)] peer-focus-visible:shadow-[var(--ula-focus-ring)] flex items-center">
            <div class="w-[20px] h-[20px] rounded-full bg-[var(--ula-white)] shadow-[var(--ula-shadow-xs)] transform transition-transform duration-[var(--ula-duration-fast)] ease-[var(--ula-ease-out)] peer-checked:translate-x-[18px] rtl:peer-checked:-translate-x-[18px]"></div>
        </div>
    </div>
</label>
