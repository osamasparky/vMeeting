@props([
    'title' => '',
    'value' => '',
    'icon' => '📊',
    'subtitle' => null,
    'subvalue' => null,
    'progress' => null,
    'delta' => null,
    'deltaPositive' => true,
    'color' => null
])

<div class="kpi-card">
    <div class="kpi-header">
        <span class="kpi-title">{{ $title }}</span>
        <div class="kpi-icon-box">{{ $icon }}</div>
    </div>
    <div class="kpi-value" @if($color) style="color: {{ $color }};" @endif>
        {{ $value }}
    </div>

    @if(!is_null($progress))
        <div style="width: 100%; background: var(--bg-surface-subtle); height: 7px; border-radius: 9999px; overflow: hidden; margin-bottom: 6px; border: 1px solid var(--border-color-subtle);">
            <div style="width: {{ min(100, max(0, $progress)) }}%; height: 100%; background: linear-gradient(90deg, #42774C 0%, #245C3A 100%); border-radius: 9999px; transition: width 0.3s ease;"></div>
        </div>
    @endif

    @if($subtitle || $delta)
        <div style="font-size: 11px; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
            @if($subtitle)
                <span>{{ $subtitle }} @if($subvalue)<strong>{{ $subvalue }}</strong>@endif</span>
            @endif
            @if($delta)
                <span class="badge-pill {{ $deltaPositive ? 'badge-green' : 'badge-danger' }}" style="font-size: 10px;">
                    {{ $delta }}
                </span>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
