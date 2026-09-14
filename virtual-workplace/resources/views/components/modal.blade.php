@props([
    'id' => '',
    'title' => '',
    'subtitle' => null,
    'icon' => null,
    'maxWidth' => '560px',
    'onClose' => null,
])

<div id="{{ $id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-[rgba(11,20,16,0.65)] backdrop-blur-sm transition-opacity duration-200" style="display: none;">
    <div class="relative w-full rounded-[var(--nx-radius-xl)] bg-[var(--nx-bg-surface)] border border-[var(--nx-border-subtle)] shadow-[var(--nx-shadow-xl)] overflow-hidden transform transition-all duration-200" style="max-width: {{ $maxWidth }};">
        
        <!-- Header -->
        <div class="flex items-start justify-between p-6 border-b border-[var(--nx-border-subtle)]">
            <div class="flex items-center gap-3">
                @if($icon)
                    <div class="w-10 h-10 rounded-full bg-[var(--nx-sand-200)] flex items-center justify-center text-[var(--nx-accent)] shrink-0">
                        <span class="material-symbols-rounded text-[22px]">{{ $icon }}</span>
                    </div>
                @endif
                <div class="flex flex-col">
                    <h3 class="text-[18px] font-semibold text-[var(--nx-text-primary)] leading-tight">
                        {{ $title }}
                    </h3>
                    @if($subtitle)
                        <span class="text-[13px] text-[var(--nx-text-muted)] mt-0.5">
                            {{ $subtitle }}
                        </span>
                    @endif
                </div>
            </div>

            <button 
                type="button" 
                onclick="{{ $onClose ?: "document.getElementById('{$id}').style.display='none'" }}" 
                class="w-8 h-8 rounded-full flex items-center justify-center text-[var(--nx-text-muted)] hover:text-[var(--nx-text-primary)] hover:bg-[var(--nx-bg-surface-hover)] transition-colors focus:outline-none"
                title="إغلاق"
            >
                <span class="material-symbols-rounded text-[20px]">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 max-h-[75vh] overflow-y-auto">
            {{ $slot }}
        </div>
    </div>
</div>
