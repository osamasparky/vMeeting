@props([
    'id' => '',
    'title' => '',
    'icon' => '',
    'maxWidth' => '580px',
    'onClose' => ''
])

<div id="{{ $id }}" class="modal-overlay">
    <div class="modal-card" style="max-width: {{ $maxWidth }};">
        <div class="modal-header">
            <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                @if($icon)<span>{{ $icon }}</span>@endif
                <span>{{ $title }}</span>
            </h3>
            <button type="button" onclick="{{ $onClose ?: "document.getElementById('{$id}').style.display='none'" }}" class="modal-close" title="{{ __('Close') }}">✕</button>
        </div>
        <div class="modal-body">
            {{ $slot }}
        </div>
    </div>
</div>
