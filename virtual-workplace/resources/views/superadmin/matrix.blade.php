@extends('superadmin.layout')

@section('title', __('Permission Matrix'))
@section('page_title', __('Role & Permission Matrix'))

@section('content')
<form method="POST" action="{{ route('superadmin.matrix.sync') }}">
    @csrf

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 800; color: var(--brand-navy);">🔐 {{ __('Role & Permission Matrix') }}</h2>
            <p style="font-size: 13px; color: var(--text-secondary);">
                {{ __('Define system-wide access controls and capabilities for each role') }}
            </p>
        </div>
        <button type="submit" class="btn-action" style="background: var(--brand-teal); border-color: var(--brand-teal); color: white; padding: 10px 20px; font-size: 13px;">
            💾 {{ __('Save Permission Matrix') }}
        </button>
    </div>

    @foreach($permissions as $group => $groupPerms)
    <div class="panel-card" style="margin-bottom: 24px;">
        <div class="panel-header" style="margin-bottom: 12px; padding-bottom: 10px;">
            <div class="panel-title" style="font-size: 15px; color: var(--brand-teal);">
                <span>📁</span>
                <span>{{ $group }} {{ __('Permissions') }}</span>
            </div>
        </div>

        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 320px;">{{ __('Permission') }}</th>
                        @foreach($roles as $role)
                        <th style="text-align: center; min-width: 130px;">
                            <div style="font-weight: 800; color: var(--brand-navy);">{{ $role->name }}</div>
                            <div style="font-size: 10px; color: var(--text-muted); font-weight: 600;">{{ $role->slug }}</div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupPerms as $perm)
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: var(--brand-navy); font-size: 13px;">{{ $perm->key }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $perm->description }}</div>
                        </td>
                        @foreach($roles as $role)
                        @php
                            $hasPerm = $role->permissions->contains('id', $perm->id);
                            $isSuperRole = $role->slug === 'super_admin';
                        @endphp
                        <td style="text-align: center;">
                            <input
                                type="checkbox"
                                name="matrix[{{ $role->id }}][]"
                                value="{{ $perm->id }}"
                                {{ $hasPerm || $isSuperRole ? 'checked' : '' }}
                                style="width: 18px; height: 18px; accent-color: var(--brand-teal); cursor: pointer;"
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
        <button type="submit" class="btn-action" style="background: var(--brand-teal); border-color: var(--brand-teal); color: white; padding: 12px 24px; font-size: 14px;">
            💾 {{ __('Save Permission Matrix') }}
        </button>
    </div>
</form>
@endsection
