@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'icon' => null,
    'iconPosition' => 'start',
    'helper' => null,
    'error' => null,
    'id' => null,
    'shortcut' => null, // e.g. "⌘K"
])

@php
    $inputId = $id ?? ($name ?? 'input_' . uniqid());
    $hasError = !empty($error);
@endphp

<div class="flex flex-col gap-1.5 w-full text-start">
    @if($label)
        <label for="{{ $inputId }}" class="text-[13px] font-medium text-[var(--nx-text-primary)] flex items-center justify-between">
            <span>
                {{ $label }}
                @if($required)
                    <span class="text-[var(--nx-terracotta-500)]">*</span>
                @endif
            </span>
        </label>
    @endif

    <div class="relative flex items-center w-full">
        @if($icon && $iconPosition === 'start')
            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none text-[var(--nx-text-muted)]">
                <span class="material-symbols-rounded text-[19px]">{{ $icon }}</span>
            </div>
        @endif

        <input 
            type="{{ $type }}" 
            id="{{ $inputId }}" 
            name="{{ $name }}" 
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'h-[44px] w-full rounded-[var(--nx-radius-md)] border ' . ($hasError ? 'border-[var(--nx-terracotta-500)] ring-1 ring-[var(--nx-terracotta-500)]' : 'border-[var(--nx-border-default)]') . ' bg-[var(--nx-bg-surface)] px-3.5 text-[14px] text-[var(--nx-text-primary)] placeholder-[var(--nx-text-placeholder)] transition-all duration-150 focus:border-[var(--nx-accent)] focus:outline-none focus:ring-2 focus:ring-[var(--nx-accent)] focus:ring-offset-1 ' . ($icon && $iconPosition === 'start' ? 'ps-10' : '') . ' ' . ($shortcut || ($icon && $iconPosition === 'end') ? 'pe-12' : '')]) }}
        >

        @if($shortcut)
            <div class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
                <span class="px-1.5 py-0.5 text-[11px] font-mono rounded bg-[var(--nx-sand-200)] text-[var(--nx-text-muted)] border border-[var(--nx-border-subtle)]">
                    {{ $shortcut }}
                </span>
            </div>
        @elseif($icon && $iconPosition === 'end')
            <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 pointer-events-none text-[var(--nx-text-muted)]">
                <span class="material-symbols-rounded text-[19px]">{{ $icon }}</span>
            </div>
        @endif
    </div>

    @if($error)
        <span class="text-[12px] text-[var(--nx-terracotta-500)] font-medium">
            {{ $error }}
        </span>
    @elseif($helper)
        <span class="text-[12px] text-[var(--nx-text-muted)]">
            {{ $helper }}
        </span>
    @endif
</div>
