@extends('superadmin.layout')

@section('title', __('Permission Matrix'))
@section('page_title', __('Role & Permission Matrix'))

@section('content')
<form method="POST" action="{{ route('superadmin.matrix.sync') }}">
    @csrf

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">🔐 {{ __('Role & Permission Matrix') }}</h2>
            <p style="font-size: 13px; color: var(--text-secondary);">
                {{ __('Define system-wide access controls, feature gates, and operational capabilities for each role') }}
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <input type="text" id="matrix-search-input" onkeyup="filterMatrixRows()" placeholder="🔍 {{ __('Filter permissions...') }}" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 14px; font-size: 12px; color: var(--text-primary); width: 220px; outline: none; box-shadow: var(--shadow-inset-3d);">
            <button type="submit" class="tactile-btn btn-primary" style="padding: 10px 22px; font-size: 13px;">
                💾 {{ __('Save Permission Matrix') }}
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(79, 155, 95, 0.15); border: 1px solid rgba(79, 155, 95, 0.35); color: #4F9B5F; padding: 14px 18px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    @php
        $groupIcons = [
            'Organization' => '🏢',
            'Members' => '👥',
            'People' => '🏛️',
            'Workspace' => '🗺️',
            'Guests' => '🔗',
            'Projects' => '📁',
            'Tasks' => '📑',
            'Time' => '⏱️',
            'Timesheets' => '📅',
            'Analytics' => '📊',
            'Reports' => '📈',
            'Administration' => '🛡️',
            'Billing' => '💎',
        ];
    @endphp

    @foreach($permissions as $group => $groupPerms)
    <div class="panel-card matrix-group-card" style="margin-bottom: 24px; border-radius: var(--radius-xl); padding: 22px;">
        <div class="panel-header" style="margin-bottom: 14px; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
            <div class="panel-title" style="font-size: 16px; color: var(--brand-forest); display: flex; align-items: center; gap: 8px; font-weight: 900;">
                <span>{{ $groupIcons[$group] ?? '📁' }}</span>
                <span>{{ $group }} {{ __('Permissions') }}</span>
                <span class="nav-badge-pill" style="font-size: 11px; margin-inline-start: 6px;">{{ $groupPerms->count() }}</span>
            </div>
            <button type="button" onclick="toggleGroupAll('{{ Str::slug($group) }}')" class="tactile-btn btn-secondary" style="padding: 4px 10px; font-size: 11px;">
                ⚡ {{ __('Toggle All') }}
            </button>
        </div>

        <div class="data-table-container" style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 340px;">{{ __('Permission & Key') }}</th>
                        @foreach($roles as $role)
                        <th style="text-align: center; min-width: 120px;">
                            <div style="font-weight: 800; color: var(--text-primary); font-size: 13px;">{{ $role->name }}</div>
                            <div style="font-size: 10px; color: var(--text-muted); font-weight: 700; font-family: monospace;">{{ $role->slug }}</div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupPerms as $perm)
                    <tr class="matrix-perm-row" data-perm-key="{{ strtolower($perm->key) }}" data-perm-desc="{{ strtolower($perm->description) }}">
                        <td>
                            <div style="font-weight: 800; color: var(--brand-forest); font-size: 13px; font-family: monospace;">{{ $perm->key }}</div>
                            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">{{ $perm->description }}</div>
                        </td>
                        @foreach($roles as $role)
                        @php
                            $hasPerm = $role->permissions->contains('id', $perm->id);
                            $isSuperRole = $role->slug === 'super_admin';
                        @endphp
                        <td style="text-align: center;">
                            <input
                                type="checkbox"
                                class="perm-chk-{{ Str::slug($group) }} perm-chk-role-{{ $role->id }}"
                                name="matrix[{{ $role->id }}][]"
                                value="{{ $perm->id }}"
                                {{ $hasPerm || $isSuperRole ? 'checked' : '' }}
                                style="width: 18px; height: 18px; accent-color: var(--brand-forest); cursor: pointer;"
                            >
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <div style="display: flex; justify-content: flex-end; margin-top: 16px; margin-bottom: 40px;">
        <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 28px; font-size: 14px;">
            💾 {{ __('Save Permission Matrix') }}
        </button>
    </div>
</form>

<script nonce="{{ $cspNonce ?? '' }}">
function filterMatrixRows() {
    const q = (document.getElementById('matrix-search-input').value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.matrix-perm-row');
    rows.forEach(r => {
        const key = r.getAttribute('data-perm-key') || '';
        const desc = r.getAttribute('data-perm-desc') || '';
        if (!q || key.includes(q) || desc.includes(q)) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function toggleGroupAll(groupSlug) {
    const chks = document.querySelectorAll('.perm-chk-' + groupSlug);
    if (!chks.length) return;
    const anyUnchecked = Array.from(chks).some(c => !c.checked);
    chks.forEach(c => c.checked = anyUnchecked);
}
</script>
@endsection
