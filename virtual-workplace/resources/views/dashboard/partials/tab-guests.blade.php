<div id="tab-guests" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--ula-space-7); flex-wrap: wrap; gap: var(--ula-space-5);">
        <div>
            <p class="page-subtitle" style="font-size: var(--ula-size-sm); color: var(--ula-text-secondary);">{{ __('Generate instant join links for clients, interviewees, and external partners.') }}</p>
        </div>
        <div style="display: flex; gap: var(--ula-space-4); align-items: center;">
            @if($guestInvitations->count() > 0)
                <form method="POST" action="{{ route('guest_invitations.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to delete all guest meeting links?') }}');" style="display: inline; margin: 0;">
                    @csrf
                    <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.12); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.25); padding: 10px 16px; font-size: var(--ula-size-sm); display: inline-flex; align-items: center; gap: var(--ula-space-3); border-radius: var(--ula-radius-lg);">
                        <span class="material-symbols-rounded" style="font-size: 18px;">delete_sweep</span>
                        <span>{{ __('Clear All Links') }}</span>
                    </button>
                </form>
            @endif
            <button onclick="openInviteModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: var(--ula-size-sm); display: inline-flex; align-items: center; gap: var(--ula-space-3);">
                <span class="material-symbols-rounded" style="font-size: 18px;">bolt</span>
                <span>{{ __('Create Guest Link') }}</span>
            </button>
        </div>
    </div>

    <div class="card" style="border-radius: var(--ula-radius-xl); overflow: hidden; padding: 0; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs); background: var(--ula-surface-card);">
        <div style="padding: var(--ula-space-6) var(--ula-space-7); border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--ula-surface-card);">
            <h3 style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); display: flex; align-items: center; gap: var(--ula-space-3); margin: 0;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-accent-default);">share</span>
                <span>{{ __('Active & Recent Guest Invitations') }}</span>
                <span class="nav-badge-pill" style="font-family: var(--ula-font-mono);">{{ $guestInvitations->count() }}</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Guest Name / Label') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Target Room') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Expires At') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Join URL') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guestInvitations as $inv)
                        <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: var(--ula-space-3);">
                                    <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-accent-default);">person</span>
                                    <strong style="color: var(--ula-text-primary); font-size: var(--ula-size-sm); font-weight: var(--ula-weight-bold);">{{ $inv->guest_name }}</strong>
                                </div>
                            </td>
                            <td style="padding: 14px 16px; font-size: var(--ula-size-sm); color: var(--ula-text-primary);">
                                <div style="display: flex; align-items: center; gap: var(--ula-space-3);">
                                    <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">meeting_room</span>
                                    <span>{{ $inv->room->name ?? __('Main Conference') }}</span>
                                </div>
                            </td>
                            <td style="padding: 14px 16px; font-size: var(--ula-size-xs); color: var(--ula-text-muted); font-family: var(--ula-font-mono);">
                                {{ $inv->expires_at ? $inv->expires_at->diffForHumans() : __('Never') }}
                            </td>
                            <td style="padding: 14px 16px;">
                                <code style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); padding: 4px 10px; border-radius: var(--ula-radius-sm); font-size: 11px; color: var(--ula-accent-default); font-family: var(--ula-font-mono); box-shadow: var(--ula-shadow-xs);">
                                    /guest/join/{{ substr($inv->token, 0, 16) }}...
                                </code>
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; gap: var(--ula-space-3); align-items: center;">
                                    <button type="button" onclick="copyTableGuestLink('{{ url('/guest/join/' . $inv->token) }}', this)" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: var(--ula-size-xs); display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">content_copy</span>
                                        <span>{{ __('Copy Link') }}</span>
                                    </button>
                                    <a href="{{ url('/guest/join/' . $inv->token) }}" target="_blank" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: var(--ula-size-xs); display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">visibility</span>
                                        <span>{{ __('Open') }}</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--ula-text-muted); padding: var(--ula-space-9);">
                            <div style="font-size: 32px; margin-bottom: var(--ula-space-3); color: var(--ula-text-muted); display: flex; justify-content: center;">
                                <span class="material-symbols-rounded" style="font-size: 40px;">link_off</span>
                            </div>
                            <p style="margin: 0; font-size: var(--ula-size-sm);">{{ __('No guest invitations generated yet.') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
