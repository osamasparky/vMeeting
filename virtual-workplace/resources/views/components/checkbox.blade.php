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

<label for="{{ $inputId }}" class="inline-flex items-center gap-2.5 cursor-pointer select-none group text-[14px] text-[var(--nx-text-primary)] {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
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
        <!-- Custom Box -->
        <div class="w-5 h-5 rounded-[var(--nx-radius-xs)] border-[1.5px] border-[var(--nx-border-strong)] bg-[var(--nx-bg-surface)] transition-all duration-150 peer-checked:bg-[var(--nx-accent)] peer-checked:border-[var(--nx-accent)] peer-focus-visible:ring-2 peer-focus-visible:ring-[var(--nx-accent)] peer-focus-visible:ring-offset-2 flex items-center justify-center text-white">
            <span class="material-symbols-rounded text-[15px] opacity-0 peer-checked:opacity-100 font-bold transition-opacity duration-150">
                check
            </span>
        </div>
    </div>
    @if($label || $slot->isNotEmpty())
        <span>{{ $label ?? $slot }}</span>
    @endif
</label>
