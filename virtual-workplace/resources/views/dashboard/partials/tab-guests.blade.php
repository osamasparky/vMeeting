<div id="tab-guests" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-6); flex-wrap: wrap; gap: var(--nx-spacing-4);">
        <div>
            <h1 class="page-title" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-1); display: flex; align-items: center; gap: var(--nx-spacing-2);">
                <span class="material-symbols-rounded" style="font-size: 28px; color: var(--nx-primary-500);">link</span>
                <span>{{ __('Guest Meeting Links') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: var(--nx-font-size-sm); color: var(--nx-text-secondary);">{{ __('Generate instant join links for clients, interviewees, and external partners.') }}</p>
        </div>
        <div style="display: flex; gap: var(--nx-spacing-3); align-items: center;">
            @if($guestInvitations->count() > 0)
                <form method="POST" action="{{ route('guest_invitations.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to delete all guest meeting links?') }}');" style="display: inline; margin: 0;">
                    @csrf
                    <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.12); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.25); padding: 10px 16px; font-size: var(--nx-font-size-sm); display: inline-flex; align-items: center; gap: var(--nx-spacing-2); border-radius: var(--nx-radius-lg);">
                        <span class="material-symbols-rounded" style="font-size: 18px;">delete_sweep</span>
                        <span>{{ __('Clear All Links') }}</span>
                    </button>
                </form>
            @endif
            <button onclick="openInviteModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: var(--nx-font-size-sm); display: inline-flex; align-items: center; gap: var(--nx-spacing-2);">
                <span class="material-symbols-rounded" style="font-size: 18px;">bolt</span>
                <span>{{ __('Create Guest Link') }}</span>
            </button>
        </div>
    </div>

    <div class="card" style="border-radius: var(--nx-radius-xl); overflow: hidden; padding: 0; border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card); background: var(--nx-bg-surface);">
        <div style="padding: var(--nx-spacing-5) var(--nx-spacing-6); border-bottom: 1px solid var(--nx-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--nx-bg-surface);">
            <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); display: flex; align-items: center; gap: var(--nx-spacing-2); margin: 0;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--nx-primary-500);">share</span>
                <span>{{ __('Active & Recent Guest Invitations') }}</span>
                <span class="nav-badge-pill" style="font-family: var(--nx-font-mono);">{{ $guestInvitations->count() }}</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Guest Name / Label') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Target Room') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Expires At') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Join URL') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guestInvitations as $inv)
                        <tr style="border-bottom: 1px solid var(--nx-border-subtle);">
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: var(--nx-spacing-2);">
                                    <span class="material-symbols-rounded" style="font-size: 18px; color: var(--nx-primary-500);">person</span>
                                    <strong style="color: var(--nx-text-primary); font-size: var(--nx-font-size-sm); font-weight: var(--nx-font-weight-bold);">{{ $inv->guest_name }}</strong>
                                </div>
                            </td>
                            <td style="padding: 14px 16px; font-size: var(--nx-font-size-sm); color: var(--nx-text-primary);">
                                <div style="display: flex; align-items: center; gap: var(--nx-spacing-2);">
                                    <span class="material-symbols-rounded" style="font-size: 16px; color: var(--nx-text-muted);">meeting_room</span>
                                    <span>{{ $inv->room->name ?? __('Main Conference') }}</span>
                                </div>
                            </td>
                            <td style="padding: 14px 16px; font-size: var(--nx-font-size-xs); color: var(--nx-text-muted); font-family: var(--nx-font-mono);">
                                {{ $inv->expires_at ? $inv->expires_at->diffForHumans() : __('Never') }}
                            </td>
                            <td style="padding: 14px 16px;">
                                <code style="background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); padding: 4px 10px; border-radius: var(--nx-radius-sm); font-size: 11px; color: var(--nx-primary-500); font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-inset-3d);">
                                    /guest/join/{{ substr($inv->token, 0, 16) }}...
                                </code>
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; gap: var(--nx-spacing-2); align-items: center;">
                                    <button type="button" onclick="copyTableGuestLink('{{ url('/guest/join/' . $inv->token) }}', this)" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: var(--nx-font-size-xs); display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">content_copy</span>
                                        <span>{{ __('Copy Link') }}</span>
                                    </button>
                                    <a href="{{ url('/guest/join/' . $inv->token) }}" target="_blank" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: var(--nx-font-size-xs); display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">visibility</span>
                                        <span>{{ __('Open') }}</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--nx-text-muted); padding: var(--nx-spacing-10);">
                            <div style="font-size: 32px; margin-bottom: var(--nx-spacing-2); color: var(--nx-text-muted); display: flex; justify-content: center;">
                                <span class="material-symbols-rounded" style="font-size: 40px;">link_off</span>
                            </div>
                            <p style="margin: 0; font-size: var(--nx-font-size-sm);">{{ __('No guest invitations generated yet.') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
