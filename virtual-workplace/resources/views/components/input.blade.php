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

<div class="flex flex-col gap-[7px] w-full text-start">
    @if($label)
        <label for="{{ $inputId }}" class="text-[15px] font-medium text-[var(--ula-text-primary)] flex items-center justify-between">
            <span>
                {{ $label }}
                @if($required)
                    <span class="text-[var(--ula-text-danger)]">*</span>
                @endif
            </span>
        </label>
    @endif

    <div class="relative flex items-center w-full">
        @if($icon && $iconPosition === 'start')
            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none text-[var(--ula-text-muted)]">
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
            {{ $attributes->merge(['class' => 'h-[46px] w-full rounded-[var(--ula-radius-md)] border ' . ($hasError ? 'border-[1.5px] border-[var(--ula-border-danger)]' : 'border-[var(--ula-border-default)]') . ' bg-[var(--ula-surface-page)] px-4 text-[15px] text-[var(--ula-text-primary)] placeholder-[var(--ula-text-muted)] transition-[border-color,box-shadow] duration-[var(--ula-duration-fast)] ease-[var(--ula-ease-out)] focus:border-[var(--ula-border-focus)] focus:outline-none focus-visible:shadow-[var(--ula-focus-ring)] ' . ($icon && $iconPosition === 'start' ? 'ps-10' : '') . ' ' . ($shortcut || ($icon && $iconPosition === 'end') ? 'pe-12' : '')]) }}
        >

        @if($shortcut)
            <div class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
                <span class="px-1.5 py-0.5 text-[11px] font-[family-name:var(--ula-font-mono)] rounded-[var(--ula-radius-xs)] bg-[var(--ula-surface-sunken)] text-[var(--ula-text-muted)] border border-[var(--ula-border-subtle)]">
                    {{ $shortcut }}
                </span>
            </div>
        @elseif($icon && $iconPosition === 'end')
            <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 pointer-events-none text-[var(--ula-text-muted)]">
                <span class="material-symbols-rounded text-[19px]">{{ $icon }}</span>
            </div>
        @endif
    </div>

    @if($error)
        <span class="text-[13px] text-[var(--ula-text-danger)] font-medium">
            {{ $error }}
        </span>
    @elseif($helper)
        <span class="text-[13px] text-[var(--ula-text-secondary)]">
            {{ $helper }}
        </span>
    @endif
</div>
