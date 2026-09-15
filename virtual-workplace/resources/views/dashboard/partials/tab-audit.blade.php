<div id="tab-audit" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-6); flex-wrap: wrap; gap: var(--nx-spacing-4);">
        <div>
            <h1 class="page-title" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-1); display: flex; align-items: center; gap: var(--nx-spacing-2);">
                <span class="material-symbols-rounded" style="font-size: 28px; color: var(--nx-primary-500);">history</span>
                <span>{{ __('Audit Logs') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: var(--nx-font-size-sm); color: var(--nx-text-secondary);">{{ __('Track administrative actions and security events across the workplace.') }}</p>
        </div>
        <div>
            @if($auditLogs->count() > 0)
                <form method="POST" action="{{ route('audit_logs.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to purge all audit logs?') }}');" style="display: inline; margin: 0;">
                    @csrf
                    <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.12); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.25); padding: 10px 16px; font-size: var(--nx-font-size-sm); display: inline-flex; align-items: center; gap: var(--nx-spacing-2); border-radius: var(--nx-radius-lg);">
                        <span class="material-symbols-rounded" style="font-size: 18px;">delete_sweep</span>
                        <span>{{ __('Clear All Logs') }}</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card" style="border-radius: var(--nx-radius-xl); overflow: hidden; padding: 0; border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card); background: var(--nx-bg-surface);">
        <div style="padding: var(--nx-spacing-5) var(--nx-spacing-6); border-bottom: 1px solid var(--nx-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--nx-bg-surface);">
            <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); display: flex; align-items: center; gap: var(--nx-spacing-2); margin: 0;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--nx-primary-500);">security</span>
                <span>{{ __('Security Activity Trail') }}</span>
                <span class="nav-badge-pill" style="font-family: var(--nx-font-mono);">{{ $auditLogs->count() }}</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Action') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Target') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('User') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('IP Address') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Timestamp') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr style="border-bottom: 1px solid var(--nx-border-subtle);">
                            <td style="padding: 14px 16px;">
                                <span class="nav-badge-pill" style="background: var(--nx-primary-surface); color: var(--nx-primary-500); font-weight: var(--nx-font-weight-bold); font-size: 11px;">{{ $log->action }}</span>
                            </td>
                            <td style="padding: 14px 16px; font-weight: var(--nx-font-weight-bold); color: var(--nx-text-primary); font-size: var(--nx-font-size-sm);">{{ class_basename($log->auditable_type) }}</td>
                            <td style="padding: 14px 16px; font-family: var(--nx-font-mono); font-size: var(--nx-font-size-xs); color: var(--nx-text-primary);">{{ substr($log->user_id ?? 'System', 0, 8) }}</td>
                            <td style="padding: 14px 16px; font-family: var(--nx-font-mono); font-size: var(--nx-font-size-xs); color: var(--nx-text-muted);">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            <td style="padding: 14px 16px; font-size: var(--nx-font-size-xs); color: var(--nx-text-muted); font-family: var(--nx-font-mono);">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--nx-text-muted); padding: var(--nx-spacing-10);">
                                <div style="font-size: 32px; margin-bottom: var(--nx-spacing-2); color: var(--nx-text-muted); display: flex; justify-content: center;">
                                    <span class="material-symbols-rounded" style="font-size: 40px;">verified_user</span>
                                </div>
                                <p style="margin: 0; font-size: var(--nx-font-size-sm);">{{ __('Audit trail is clean and recorded.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
