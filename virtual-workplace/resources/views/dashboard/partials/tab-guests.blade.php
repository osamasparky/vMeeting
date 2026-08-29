                    <div id="tab-guests" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">🔗 {{ __('Guest Meeting Links') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Generate instant join links for clients, interviewees, and external partners.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    @if($guestInvitations->count() > 0)
                        <form method="POST" action="{{ route('guest_invitations.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to delete all guest meeting links?') }}');" style="display: inline;">
                            @csrf
                            <button type="submit" class="tactile-btn" style="background: #D96B5F; color: white; padding: 10px 16px; font-size: 13px;">
                                <span>🗑️</span> {{ __('Clear All Links') }}
                            </button>
                        </form>
                    @endif
                    <button onclick="openInviteModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>⚡</span> {{ __('Create Guest Link') }}
                    </button>
                </div>
            </div>

            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">🔗 {{ __('Active & Recent Guest Invitations') }} ({{ $guestInvitations->count() }})</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Guest Name / Label') }}</th>
                                <th>{{ __('Target Room') }}</th>
                                <th>{{ __('Expires At') }}</th>
                                <th>{{ __('Join URL') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guestInvitations as $inv)
                                <tr>
                                    <td>
                                        <strong style="color: var(--brand-forest); font-size: 13px;">👤 {{ $inv->guest_name }}</strong>
                                    </td>
                                    <td>🏢 {{ $inv->room->name ?? 'Main Conference' }}</td>
                                    <td style="font-size: 12px; color: var(--text-muted);">{{ $inv->expires_at ? $inv->expires_at->diffForHumans() : __('Never') }}</td>
                                    <td>
                                        <code style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); padding: 4px 10px; border-radius: 6px; font-size: 11px; color: var(--brand-forest); box-shadow: var(--shadow-inset-3d);">
                                            /guest/join/{{ substr($inv->token, 0, 16) }}...
                                        </code>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <button type="button" onclick="copyTableGuestLink('{{ url('/guest/join/' . $inv->token) }}', this)" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: 11px; cursor: pointer;">
                                                📋 {{ __('Copy Link') }}
                                            </button>
                                            <a href="{{ url('/guest/join/' . $inv->token) }}" target="_blank" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px; text-decoration: none;">
                                                👁️ {{ __('Open') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                    {{ __('No guest invitations generated yet.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 5. DEPARTMENTS & TEAMS TAB -->
