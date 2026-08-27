@props([
    'variant' => 'neutral', // 'green', 'gold', 'danger', 'neutral'
    'icon' => null
])

<span {{ $attributes->merge(['class' => "badge-pill badge-{$variant}"]) }}>
    @if($icon)<span>{{ $icon }}</span>@endif
    {{ $slot }}
</span>
