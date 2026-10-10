{{--
    Shared app top bar — design-reference 06/14/35: search · invite · bell · theme · language.

    $shellMode  'tabs'  → dashboard: search filters the current tab, invite opens the
                          invite modal.
                'links' → other pages: search calls $shellSearch (a JS function name
                          defined by that page), invite goes to the dashboard.
    The bell (partials/notification-bell) is the same live dropdown on every page.
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
    @else
        <a href="{{ route('dashboard') }}#members" class="ula-shell-invite">
            <span class="material-symbols-rounded">person_add</span>{{ __('nav.invite') }}
        </a>
    @endif

    @include('partials.notification-bell')

    <button type="button" id="theme-toggle-btn" class="ula-shell-iconbtn" onclick="toggleThemeMode()" aria-label="{{ __('nav.theme') }}" title="{{ __('nav.theme') }}">
        <span class="material-symbols-rounded">dark_mode</span>
    </button>

    <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="ula-shell-lang" aria-label="{{ __('nav.language') }}" title="{{ __('nav.language') }}">
        {{ app()->getLocale() === 'ar' ? 'EN' : 'ع' }}
    </a>
</header>
