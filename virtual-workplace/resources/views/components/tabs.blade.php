@props([
    'tabs' => [], // array of ['id' => '...', 'label' => '...', 'icon' => '...', 'badge' => '...']
    'active' => null,
    'density' => 'default', // default, sm
    'variant' => 'pill',    // pill, underline, segment
])

@php
    $activeTab = $active ?? ($tabs[0]['id'] ?? '');
    
    $containerClass = match($variant) {
        'segment' => 'inline-flex items-center p-1 rounded-full bg-[var(--nx-sand-200)] border border-[var(--nx-border-subtle)] gap-1',
        'underline' => 'flex items-center border-b border-[var(--nx-border-subtle)] gap-6 w-full',
        default => 'inline-flex flex-wrap items-center gap-2 p-1.5 rounded-[var(--nx-radius-xl)] bg-[var(--nx-sand-100)] border border-[var(--nx-border-subtle)]',
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
                    ? 'bg-[var(--nx-bg-surface)] text-[var(--nx-text-primary)] font-semibold shadow-[var(--nx-shadow-sm)]' 
                    : 'text-[var(--nx-text-secondary)] hover:text-[var(--nx-text-primary)] font-medium') . ' px-3.5 py-1.5 text-[13px] rounded-full transition-all duration-150 inline-flex items-center gap-1.5',
                'underline' => ($isActive 
                    ? 'border-b-2 border-[var(--nx-accent)] text-[var(--nx-accent)] font-semibold' 
                    : 'border-b-2 border-transparent text-[var(--nx-text-secondary)] hover:text-[var(--nx-text-primary)] font-medium') . ' pb-3 px-1 text-[14px] transition-all duration-150 inline-flex items-center gap-2',
                default => ($isActive 
                    ? 'bg-[var(--nx-palm-900)] text-white shadow-[var(--nx-shadow-sm)] font-semibold' 
                    : 'bg-transparent text-[var(--nx-text-secondary)] hover:bg-[var(--nx-sand-200)] hover:text-[var(--nx-text-primary)] font-medium') . ' px-4 py-2 text-[13px] rounded-full transition-all duration-150 inline-flex items-center gap-2 select-none',
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
                <span class="px-1.5 py-0.5 text-[10px] font-mono rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-[var(--nx-sand-300)] text-[var(--nx-text-secondary)]' }}">
                    {{ $tabBadge }}
                </span>
            @endif
        </button>
    @endforeach
</div>
