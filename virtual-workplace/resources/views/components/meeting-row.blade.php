@props([
    'time' => '',
    'title' => '',
    'room' => '',
    'status' => 'scheduled', // live, scheduled, attention, cancelled
    'density' => 'default',   // default, compact
    'avatars' => [],
    'href' => null,
])

@php
    $isCancelled = $status === 'cancelled';
    
    $padClass = $density === 'compact' ? 'py-2.5 px-3.5 gap-3' : 'py-3 px-4 gap-3.5';
    $squareSize = $density === 'compact' ? 'w-[34px] h-[34px]' : 'w-[36px] h-[36px]';
    $iconSize = $density === 'compact' ? 'text-[18px]' : 'text-[19px]';

    $statusConfig = match($status) {
        'live' => [
            'bg' => 'bg-[var(--nx-status-live-bg)]',
            'color' => 'text-[var(--nx-status-live)]',
            'icon' => 'videocam',
            'label' => 'مباشر الآن',
        ],
        'attention' => [
            'bg' => 'bg-[var(--nx-status-attention-bg)]',
            'color' => 'text-[var(--nx-status-attention)]',
            'icon' => 'front_hand',
            'label' => 'مطلوب انتباه',
        ],
        'cancelled' => [
            'bg' => 'bg-[var(--nx-status-cancelled-bg)]',
            'color' => 'text-[var(--nx-status-cancelled)]',
            'icon' => 'error',
            'label' => 'ملغي',
        ],
        default => [
            'bg' => 'bg-[var(--nx-status-scheduled-bg)]',
            'color' => 'text-[var(--nx-status-scheduled)]',
            'icon' => 'schedule',
            'label' => 'مجدول',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => "group relative flex items-center justify-between rounded-[var(--nx-radius-md)] border border-[var(--nx-border-subtle)] bg-[var(--nx-bg-surface)] {$padClass} transition-all duration-200 hover:border-[var(--nx-border-strong)] hover:bg-[var(--nx-bg-surface-hover)] " . ($isCancelled ? 'opacity-60' : '')]) }}>
    
    <!-- Start: Time & Details -->
    <div class="flex items-center gap-3.5 min-w-0">
        <!-- Timestamp in Mono -->
        <span class="font-mono text-[12px] font-normal text-[var(--nx-text-muted)] shrink-0 min-w-[64px]">
            {{ $time }}
        </span>

        <!-- Title & Room -->
        <div class="flex flex-col min-w-0">
            <span class="text-[14px] font-medium text-[var(--nx-text-primary)] truncate {{ $isCancelled ? 'line-through' : '' }}">
                {{ $title }}
            </span>
            @if($room)
                <span class="text-[12px] text-[var(--nx-text-secondary)] truncate">
                    {{ $room }}
                </span>
            @endif
        </div>
    </div>

    <!-- End: Avatars & Status Square -->
    <div class="flex items-center gap-3 shrink-0">
        @if(!empty($avatars))
            <div class="hidden sm:flex items-center -space-x-2 rtl:space-x-reverse">
                @foreach(array_slice($avatars, 0, 3) as $avatar)
                    <div class="w-6 h-6 rounded-full border-2 border-[var(--nx-bg-surface)] bg-[var(--nx-sand-300)] overflow-hidden">
                        @if(is_string($avatar) && filter_var($avatar, FILTER_VALIDATE_URL))
                            <img src="{{ $avatar }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-[10px] flex items-center justify-center h-full text-[var(--nx-palm-900)] font-semibold">{{ substr($avatar, 0, 1) }}</span>
                        @endif
                    </div>
                @endforeach
                @if(count($avatars) > 3)
                    <div class="w-6 h-6 rounded-full border-2 border-[var(--nx-bg-surface)] bg-[var(--nx-stone-track)] text-[var(--nx-stone-text)] text-[10px] font-mono flex items-center justify-center">
                        +{{ count($avatars) - 3 }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Status Square Icon -->
        <div class="flex items-center justify-center rounded-[var(--nx-radius-sm)] {{ $squareSize }} {{ $statusConfig['bg'] }} {{ $statusConfig['color'] }} shrink-0" title="{{ $statusConfig['label'] }}">
            <span class="material-symbols-rounded {{ $iconSize }}">{{ $statusConfig['icon'] }}</span>
        </div>
    </div>
</div>
