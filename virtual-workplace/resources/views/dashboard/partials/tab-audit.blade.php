                    <div id="tab-audit" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">📋 {{ __('Audit Logs') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Track administrative actions and security events across the workplace.') }}</p>
                </div>
                <div>
                    @if($auditLogs->count() > 0)
                        <form method="POST" action="{{ route('audit_logs.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to purge all audit logs?') }}');" style="display: inline;">
                            @csrf
                            <button type="submit" class="tactile-btn" style="background: #D96B5F; color: white; padding: 10px 16px; font-size: 13px;">
                                <span>🗑️</span> {{ __('Clear All Logs') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">🛡️ {{ __('Security Activity Trail') }} ({{ $auditLogs->count() }})</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Action') }}</th>
                                <th>{{ __('Target') }}</th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('IP Address') }}</th>
                                <th>{{ __('Timestamp') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditLogs as $log)
                                <tr>
                                    <td><span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">{{ $log->action }}</span></td>
                                    <td style="font-weight: 700; color: var(--text-primary);">{{ class_basename($log->auditable_type) }}</td>
                                    <td style="font-family: monospace; font-size: 12px;">{{ substr($log->user_id ?? 'System', 0, 8) }}</td>
                                    <td style="font-family: monospace; font-size: 12px; color: var(--text-muted);">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                    <td style="font-size: 12px; color: var(--text-muted); font-family: monospace;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                        {{ __('Audit trail is clean and recorded.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 7. SETTINGS TAB -->
