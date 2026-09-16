@extends('superadmin.layout')

@section('title', __('Permission Matrix'))
@section('page_title', __('Role & Permission Matrix'))

@section('content')
<form method="POST" action="{{ route('superadmin.matrix.sync') }}">
    @csrf

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 800; color: var(--nx-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--nx-accent); font-size: 24px;">lock_person</span>
                <span>{{ __('Role & Permission Matrix') }}</span>
            </h2>
            <p style="font-size: 13px; color: var(--nx-text-secondary); margin: 0;">
                {{ __('Define system-wide access controls, feature gates, and operational capabilities for each role') }}
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <div style="position: relative; display: flex; align-items: center;">
                <span class="material-symbols-rounded" style="position: absolute; inset-inline-start: 12px; font-size: 16px; color: var(--nx-text-muted); pointer-events: none;">search</span>
                <input type="text" id="matrix-search-input" onkeyup="filterMatrixRows()" placeholder="{{ __('Filter permissions...') }}" style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-full, 9999px); padding: 8px 14px; padding-inline-start: 36px; font-size: 12px; color: var(--nx-text-primary); width: 220px; outline: none;">
            </div>
            <button type="submit" class="tactile-btn btn-primary" style="padding: 9px 20px; font-size: 13px; border-radius: var(--nx-radius-full, 9999px); display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                <span>{{ __('Save Permission Matrix') }}</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(60, 107, 76, 0.12); border: 1px solid rgba(60, 107, 76, 0.35); color: var(--nx-status-live); padding: 14px 18px; border-radius: var(--nx-radius-md, 12px); margin-bottom: 20px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="font-size: 18px;">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @php
        $groupIcons = [
            'Organization' => 'corporate_fare',
            'Members' => 'group',
            'People' => 'account_box',
            'Workspace' => 'apartment',
            'Guests' => 'link',
            'Projects' => 'folder',
            'Tasks' => 'task_alt',
            'Time' => 'timer',
            'Timesheets' => 'calendar_month',
            'Analytics' => 'insights',
            'Reports' => 'analytics',
            'Administration' => 'shield',
            'Billing' => 'credit_card',
        ];
    @endphp

    @foreach($permissions as $group => $groupPerms)
    <div class="panel-card matrix-group-card" style="margin-bottom: 24px; border-radius: var(--nx-radius-xl, 20px); padding: 22px; background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-sm);">
        <div class="panel-header" style="margin-bottom: 14px; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--nx-border-subtle);">
            <div class="panel-title" style="font-size: 15px; color: var(--nx-palm-900); display: flex; align-items: center; gap: 8px; font-weight: 800;">
                <span class="material-symbols-rounded" style="color: var(--nx-accent); font-size: 20px;">{{ $groupIcons[$group] ?? 'folder' }}</span>
                <span>{{ $group }} {{ __('Permissions') }}</span>
                <span class="nav-badge-pill" style="font-size: 11px; padding: 2px 8px; background: rgba(20,43,36,0.06); color: var(--nx-palm-900); font-family: 'IBM Plex Mono', monospace;">{{ $groupPerms->count() }}</span>
            </div>
            <button type="button" onclick="toggleGroupAll('{{ Str::slug($group) }}')" class="tactile-btn btn-secondary" style="padding: 4px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 13px;">check_box</span>
                <span>{{ __('Toggle All') }}</span>
            </button>
        </div>

        <div class="data-table-container" style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: start;">
                <thead>
                    <tr style="background: var(--nx-bg-surface-subtle, #F4EDE1); border-bottom: 1px solid var(--nx-border-subtle);">
                        <th style="padding: 10px 16px; font-size: 11px; font-weight: 700; color: var(--nx-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start; width: 340px;">{{ __('Permission & Key') }}</th>
                        @foreach($roles as $role)
                        <th style="padding: 10px 16px; font-size: 11px; font-weight: 700; color: var(--nx-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: center; min-width: 120px;">
                            <div style="font-weight: 800; color: var(--nx-text-primary); font-size: 12px;">{{ $role->name }}</div>
                            <div style="font-size: 10px; color: var(--nx-text-muted); font-family: 'IBM Plex Mono', monospace;">{{ $role->slug }}</div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupPerms as $perm)
                    <tr class="matrix-perm-row" data-perm-key="{{ strtolower($perm->key) }}" data-perm-desc="{{ strtolower($perm->description) }}" style="border-bottom: 1px solid var(--nx-border-subtle); transition: background 0.15s ease;">
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 700; color: var(--nx-palm-900); font-size: 12px; font-family: 'IBM Plex Mono', monospace;">{{ $perm->key }}</div>
                            <div style="font-size: 11px; color: var(--nx-text-secondary); margin-top: 2px;">{{ $perm->description }}</div>
                        </td>
                        @foreach($roles as $role)
                        @php
                            $hasPerm = $role->permissions->contains('id', $perm->id);
                            $isSuperRole = $role->slug === 'super_admin';
                        @endphp
                        <td style="padding: 12px 16px; text-align: center;">
                            <input
                                type="checkbox"
                                class="perm-chk-{{ Str::slug($group) }} perm-chk-role-{{ $role->id }}"
                                name="matrix[{{ $role->id }}][]"
                                value="{{ $perm->id }}"
                                {{ $hasPerm || $isSuperRole ? 'checked' : '' }}
                                style="width: 17px; height: 17px; accent-color: var(--nx-palm-900); cursor: pointer;"
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
        <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 28px; font-size: 13px; border-radius: var(--nx-radius-full, 9999px); display: inline-flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="font-size: 18px;">save</span>
            <span>{{ __('Save Permission Matrix') }}</span>
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
