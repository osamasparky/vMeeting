{{--
    Shared app top bar — design-reference 06/14/35: search · invite · bell · theme · language.

    $shellMode  'tabs'  → dashboard: search filters the current tab, invite opens the
                          invite modal, and $notificationsSlot holds the bell dropdown.
                'links' → other pages: search calls $shellSearch (a JS function name
                          defined by that page), invite and bell go to the dashboard.
--}}
@php
    $shellMode = $shellMode ?? 'links';
    $shellSearch = $shellSearch ?? null;
@endphp

<header class="ulaspace-appbar ula-shell-topbar">
    <button type="button" class="mobile-menu-btn ula-shell-iconbtn" onclick="toggleDashboardSidebar()" aria-label="{{ __('nav.menu') }}">
        <span class="material-symbols-rounded">menu</span>
    </button>

    <label class="ula-shell-search">
        <span class="material-symbols-rounded">search</span>
        <input type="search" id="globalSearchInput" placeholder="{{ __('nav.search') }}" aria-label="{{ __('nav.search') }}"
            @if($shellMode === 'tabs') onkeyup="handleGlobalSearch(this.value)"
            @elseif($shellSearch) oninput="{{ $shellSearch }}(this.value)"
            @endif>
    </label>

    <div class="ula-shell-spacer"></div>

    @if($shellMode === 'tabs')
        <button type="button" class="ula-shell-invite" onclick="openInviteModal()">
            <span class="material-symbols-rounded">person_add</span>{{ __('nav.invite') }}
        </button>
        <div class="ula-shell-notif" id="notifWrapper">
            <button type="button" id="notifBellBtn" class="ula-shell-iconbtn" onclick="toggleNotificationDropdown()" aria-label="{{ __('nav.notifications') }}" title="{{ __('nav.notifications') }}">
                <span class="material-symbols-rounded">notifications</span>
            </button>
            <span class="notification-badge-pulse" id="notifBadge" style="display: none;">0</span>

            <div class="notification-dropdown-panel" id="notifDropdown">
                <div style="padding: 14px 18px; background: var(--ula-surface-page-alt); border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-icon-highlight);">notifications</span>
                        <strong style="font-size: 13px; color: var(--ula-text-primary);">{{ __('Notifications') }}</strong>
                        <span id="notifHeaderCount" class="badge-status badge-active" style="font-size: 10px; padding: 2px 8px; display: none;">0 new</span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" onclick="markAllNotificationsAsRead()" style="background: none; border: none; font-size: 11px; font-weight: 700; color: var(--ula-text-primary); cursor: pointer;" title="{{ __('Mark all as read') }}">
                            {{ __('Mark read') }}
                        </button>
                        <button type="button" onclick="clearAllNotificationsFromServer()" style="background: none; border: none; color: var(--ula-text-muted); cursor: pointer;" title="{{ __('Clear all') }}">
                            <span class="material-symbols-rounded" style="font-size: 16px;">delete_sweep</span>
                        </button>
                    </div>
                </div>
                <div style="padding: 8px 12px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; gap: 6px; background: var(--ula-surface-card);">
                    <button type="button" class="notif-tab-btn active" onclick="filterNotifTab('all', this)">{{ __('All') }}</button>
                    <button type="button" class="notif-tab-btn" onclick="filterNotifTab('task', this)">{{ __('Tasks') }}</button>
                    <button type="button" class="notif-tab-btn" onclick="filterNotifTab('meeting', this)">{{ __('Meetings') }}</button>
                    <button type="button" class="notif-tab-btn" onclick="filterNotifTab('spatial', this)">{{ __('Office') }}</button>
                </div>
                <div id="notifListContainer" style="max-height: 380px; overflow-y: auto; display: flex; flex-direction: column;">
                    <div id="notifEmptyState" style="padding: 36px 18px; text-align: center; color: var(--ula-text-muted);">
                        <span class="material-symbols-rounded" style="font-size: 32px; color: var(--ula-text-muted); display: block; margin-bottom: 8px;">celebration</span>
                        <strong style="display: block; font-size: 13px; color: var(--ula-text-primary); margin-bottom: 4px;">{{ __('All caught up!') }}</strong>
                        <span style="font-size: 12px;">{{ __('No new notifications right now.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <a href="{{ route('dashboard') }}#members" class="ula-shell-invite">
            <span class="material-symbols-rounded">person_add</span>{{ __('nav.invite') }}
        </a>
        <a href="{{ route('dashboard') }}#overview" class="ula-shell-iconbtn" aria-label="{{ __('nav.notifications') }}" title="{{ __('nav.notifications') }}">
            <span class="material-symbols-rounded">notifications</span>
        </a>
    @endif

    <button type="button" id="theme-toggle-btn" class="ula-shell-iconbtn" onclick="toggleThemeMode()" aria-label="{{ __('nav.theme') }}" title="{{ __('nav.theme') }}">
        <span class="material-symbols-rounded">dark_mode</span>
    </button>

    <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="ula-shell-lang" aria-label="{{ __('nav.language') }}" title="{{ __('nav.language') }}">
        {{ app()->getLocale() === 'ar' ? 'EN' : 'ع' }}
    </a>
</header>
