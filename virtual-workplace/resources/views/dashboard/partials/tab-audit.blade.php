<div id="tab-audit" class="tab-view">
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
        <div style="display: flex; flex-direction: column; gap: 2px;">
            <h2 class="ula-headline-ar" style="font-size: var(--ula-size-h1); margin: 0;">{{ __('page.audit') }}</h2>
            @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-h4);">Audit Logs</span>@endif
        </div>
        @if($auditLogs->count() > 0)
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <form method="POST" action="{{ route('audit_logs.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to purge all audit logs?') }}');" style="display: inline; margin: 0;">
                @csrf
                <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.12); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.25); padding: 10px 16px; font-size: var(--ula-size-sm); display: inline-flex; align-items: center; gap: var(--ula-space-3); border-radius: var(--ula-radius-lg);">
                    <span class="material-symbols-rounded" style="font-size: 18px;">delete_sweep</span>
                    <span>{{ __('Clear All Logs') }}</span>
                </button>
            </form>
        </div>
        @endif
    </div>

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
                    @php
                        $auditUserNames = $members->mapWithKeys(fn ($m) => [$m->user_id => $m->user->name ?? null])->filter();
                    @endphp
                    @forelse($auditLogs as $log)
                        @php
                            // Tone by verb: removals warn, creations confirm, edits notice (design-reference 20).
                            $auditVerb = \Illuminate\Support\Str::afterLast($log->action, '.');
                            $auditTone = match (true) {
                                in_array($auditVerb, ['deleted', 'leave', 'company_status_toggled']) => 'terracotta',
                                in_array($auditVerb, ['created', 'enter']) => 'palm',
                                in_array($auditVerb, ['updated', 'company_plan_updated']) => 'gold',
                                default => 'stone',
                            };
                            $auditActionKey = 'audit.' . $log->action;
                            $auditActionLabel = __($auditActionKey) === $auditActionKey ? \Illuminate\Support\Str::headline($log->action) : __($auditActionKey);
                            $auditUserName = $auditUserNames[$log->user_id] ?? ($log->user_id ? __('Member') : __('audit.system'));
                        @endphp
                        <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                            <td style="padding: 14px 16px;">
                                <span class="ula-hub-tag ula-hub-tag--{{ $auditTone }}">{{ $auditActionLabel }}</span>
                            </td>
                            @php
                                $auditType = $log->auditable_type ? class_basename($log->auditable_type) : null;
                                $auditTypeKey = 'audit.type.' . $auditType;
                            @endphp
                            <td style="padding: 14px 16px; font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); font-size: var(--ula-size-sm);">{{ $auditType ? (__($auditTypeKey) === $auditTypeKey ? \Illuminate\Support\Str::headline($auditType) : __($auditTypeKey)) : '—' }}</td>
                            <td style="padding: 14px 16px; font-size: var(--ula-size-sm); color: var(--ula-text-primary);">
                                <span style="display: inline-flex; align-items: center; gap: var(--ula-space-3);">
                                    <span class="ula-hub-avatar" aria-hidden="true">{{ mb_substr($auditUserName, 0, 1) }}</span>{{ $auditUserName }}
                                </span>
                            </td>
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
