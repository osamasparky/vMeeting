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

<label for="{{ $switchId }}" class="inline-flex items-center justify-between gap-4 cursor-pointer select-none group w-full max-w-[320px] {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
    @if($label || $slot->isNotEmpty())
        <span class="text-[14px] font-medium text-[var(--nx-text-primary)]">
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
        <!-- Switch Track: 44x26 pill, padding 3px -->
        <div class="w-[44px] h-[26px] rounded-full bg-[var(--nx-stone-track)] p-[3px] transition-colors duration-200 peer-checked:bg-[var(--nx-accent)] peer-focus-visible:ring-2 peer-focus-visible:ring-[var(--nx-accent)] peer-focus-visible:ring-offset-2 flex items-center">
            <!-- Knob: 20px circle -->
            <div class="w-[20px] h-[20px] rounded-full bg-white shadow-[var(--nx-shadow-sm)] transform transition-transform duration-200 peer-checked:translate-x-[18px] rtl:peer-checked:-translate-x-[18px]"></div>
        </div>
    </div>
</label>
