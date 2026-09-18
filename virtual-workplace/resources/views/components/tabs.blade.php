@props([
    'tabs' => [], // array of ['id' => '...', 'label' => '...', 'icon' => '...', 'badge' => '...']
    'active' => null,
    'density' => 'default', // default, sm
    'variant' => 'pill',    // pill, underline, segment
])

@php
    $activeTab = $active ?? ($tabs[0]['id'] ?? '');
    
    $containerClass = match($variant) {
        'segment' => 'inline-flex items-center p-1 rounded-[var(--ula-radius-pill)] bg-[var(--ula-surface-sunken)] border border-[var(--ula-border-subtle)] gap-1',
        'underline' => 'flex items-center border-b border-[var(--ula-border-subtle)] gap-6 w-full',
        default => 'inline-flex flex-wrap items-center gap-2 p-1.5 rounded-[var(--ula-radius-xl)] bg-[var(--ula-surface-page-alt)] border border-[var(--ula-border-subtle)]',
    };
@endphp

<div class="{{ $containerClass }}" role="tablist">
    @foreach($tabs as $tab)
        @php
            $tabId = $tab['id'] ?? '';
            $tabLabel = $tab['label'] ?? '';
            $tabIcon = $tab['icon'] ?? null;
            $tabBadge = $tab['badge'] ?? null;
            $isActive = ($tabId === $activeTab);

            $tabBtnClass = match($variant) {
                'segment' => ($isActive 
                    ? 'bg-[var(--ula-surface-card)] text-[var(--ula-text-primary)] font-semibold shadow-[var(--ula-shadow-sm)]' 
                    : 'text-[var(--ula-text-secondary)] hover:text-[var(--ula-text-primary)] font-medium') . ' px-3.5 py-1.5 text-[13px] rounded-full transition-all duration-150 inline-flex items-center gap-1.5',
                'underline' => ($isActive 
                    ? 'border-b-2 border-[var(--ula-accent-default)] text-[var(--ula-text-primary)] font-semibold' 
                    : 'border-b-2 border-transparent text-[var(--ula-text-secondary)] hover:text-[var(--ula-text-primary)] font-medium') . ' pb-3 px-1 text-[14px] transition-all duration-150 inline-flex items-center gap-2',
                default => ($isActive 
                    ? 'bg-[var(--ula-accent-default)] text-[var(--ula-accent-fg)] shadow-[var(--ula-shadow-xs)] font-semibold' 
                    : 'bg-transparent text-[var(--ula-text-secondary)] hover:bg-[var(--ula-surface-hover)] hover:text-[var(--ula-text-primary)] font-medium') . ' px-4 py-2 text-[13px] rounded-full transition-all duration-150 inline-flex items-center gap-2 select-none',
            };
        @endphp

        <button 
            type="button" 
            role="tab" 
            id="tab-btn-{{ $tabId }}"
            data-tab-target="{{ $tabId }}"
            aria-selected="{{ $isActive ? 'true' : 'false' }}"
            class="{{ $tabBtnClass }}"
            {{ $attributes }}
        >
            @if($tabIcon)
                <span class="material-symbols-rounded text-[18px] leading-none">{{ $tabIcon }}</span>
            @endif
            <span>{{ $tabLabel }}</span>
            @if($tabBadge)
                <span class="px-1.5 py-0.5 text-[10px] font-[family-name:var(--ula-font-mono)] rounded-full [direction:ltr] [unicode-bidi:isolate] {{ $isActive ? 'bg-[var(--ula-alpha-ivory-18)] text-[var(--ula-accent-fg)]' : 'bg-[var(--ula-surface-sunken)] text-[var(--ula-text-secondary)]' }}">
                    {{ $tabBadge }}
                </span>
            @endif
        </button>
    @endforeach
</div>
