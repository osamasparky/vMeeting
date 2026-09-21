<div id="tab-audit" class="tab-view">
    @if($auditLogs->count() > 0)
    <div style="display: flex; justify-content: flex-end; margin-bottom: var(--ula-space-6);">
        <form method="POST" action="{{ route('audit_logs.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to purge all audit logs?') }}');" style="display: inline; margin: 0;">
            @csrf
            <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.12); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.25); padding: 10px 16px; font-size: var(--ula-size-sm); display: inline-flex; align-items: center; gap: var(--ula-space-3); border-radius: var(--ula-radius-lg);">
                <span class="material-symbols-rounded" style="font-size: 18px;">delete_sweep</span>
                <span>{{ __('Clear All Logs') }}</span>
            </button>
        </form>
    </div>
    @endif

    <div class="card" style="border-radius: var(--ula-radius-xl); overflow: hidden; padding: 0; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs); background: var(--ula-surface-card);">
        <div style="padding: var(--ula-space-6) var(--ula-space-7); border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--ula-surface-card);">
            <h3 style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); display: flex; align-items: center; gap: var(--ula-space-3); margin: 0;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-accent-default);">security</span>
                <span>{{ __('Security Activity Trail') }}</span>
                <span class="nav-badge-pill" style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">{{ $auditLogs->count() }}</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Action') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Target') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('User') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('IP Address') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Timestamp') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                            <td style="padding: 14px 16px;">
                                <span class="nav-badge-pill" style="background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); font-weight: var(--ula-weight-bold); font-size: 11px;">{{ $log->action }}</span>
                            </td>
                            <td style="padding: 14px 16px; font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); font-size: var(--ula-size-sm);">{{ class_basename($log->auditable_type) }}</td>
                            <td style="padding: 14px 16px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; font-size: var(--ula-size-xs); color: var(--ula-text-primary);">{{ substr($log->user_id ?? 'System', 0, 8) }}</td>
                            <td style="padding: 14px 16px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; font-size: var(--ula-size-xs); color: var(--ula-text-muted);">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            <td style="padding: 14px 16px; font-size: var(--ula-size-xs); color: var(--ula-text-muted); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--ula-text-muted); padding: var(--ula-space-9);">
                                <div style="font-size: 32px; margin-bottom: var(--ula-space-3); color: var(--ula-text-muted); display: flex; justify-content: center;">
                                    <span class="material-symbols-rounded" style="font-size: 40px;">verified_user</span>
                                </div>
                                <p style="margin: 0; font-size: var(--ula-size-sm);">{{ __('Audit trail is clean and recorded.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
