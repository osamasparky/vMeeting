@props([
    'variant' => 'primary', // 'primary', 'secondary', 'danger'
    'type' => 'button',
    'icon' => null,
    'href' => null,
    'onclick' => null
])

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "tactile-btn btn-{$variant}"]) }} @if($onclick) onclick="{{ $onclick }}" @endif>
        @if($icon)<span>{{ $icon }}</span>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "tactile-btn btn-{$variant}"]) }} @if($onclick) onclick="{{ $onclick }}" @endif>
        @if($icon)<span>{{ $icon }}</span>@endif
        {{ $slot }}
    </button>
@endif
