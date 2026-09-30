{{--
    Shared app sidebar — design-reference 06/14/35 (dark chrome, four open sections).
    Used by the dashboard and the project hub so the two can never drift apart.

    $shellActive  tab key of the current page (e.g. 'overview', 'projects')
    $shellMode    'tabs'  → items call switchAdminTab() (dashboard, single page)
                  'links' → items link to /dashboard#tab (every other page)

    Items keep the nav-tab-btn class and nav-btn-{key} ids that switchAdminTab()
    uses to move the active state, and the aside keeps id="dashboardSidebar"
    for the collapse / mobile-drawer toggles in public/js/dashboard/theme-sidebar.js.
--}}
@php
    $shellActive = $shellActive ?? 'overview';
    $shellMode = $shellMode ?? 'links';
    $isCompanyAdmin = $membership->role?->slug === 'company_admin';

    // [key, label key, icon, visible, extra] — extra: 'live' pill or 'external' arrow
    $shellSections = [
        ['nav.workspace', [
            ['overview', 'nav.overview', 'dashboard', true, null],
            ['office', 'nav.office', 'location_on', true, 'external'],
            ['chat', 'nav.chat', 'forum', true, 'live'],
            ['editor', 'nav.editor', 'architecture', $membership->hasPermission('maps.manage'), 'external'],
        ]],
        ['nav.projects_section', [
            ['projects', 'nav.projects', 'folder', true, null],
            ['all-tasks', 'nav.all_tasks', 'assignment', $membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || $isCompanyAdmin, null],
            ['my-tasks', 'nav.my_tasks', 'task_alt', true, null],
            ['timesheets', 'nav.timesheets', 'schedule', true, null],
            ['workload', 'nav.workload', 'stacked_bar_chart', $membership->hasPermission('reports.view') || $isCompanyAdmin, null],
        ]],
        ['nav.admin_section', [
            ['members', 'nav.members', 'group', $membership->hasPermission('members.view') || $membership->hasPermission('members.manage'), null],
            ['offices', 'nav.offices', 'apartment', $membership->hasPermission('maps.manage') || $isCompanyAdmin, null],
            ['rooms', 'nav.rooms', 'meeting_room', $membership->hasPermission('rooms.manage'), null],
            ['meetings', 'nav.meetings', 'calendar_month', true, null],
            ['guests', 'nav.guests', 'link', $membership->hasPermission('guests.invite'), null],
            ['departments', 'nav.departments', 'account_tree', $membership->hasPermission('departments.manage') || $membership->hasPermission('teams.manage'), null],
            ['audit', 'nav.audit', 'history', $membership->hasPermission('audit.view'), null],
        ]],
        ['nav.settings_section', [
            ['profile', 'nav.profile', 'account_circle', true, null],
            ['billing', 'nav.billing', 'credit_card', $membership->hasPermission('billing.manage'), null],
            ['settings', 'nav.settings', 'settings', $membership->hasPermission('organizations.manage'), null],
        ]],
    ];

    // Office and editor are separate pages in both modes.
    $shellRoutes = ['office' => route('office'), 'editor' => route('editor')];
@endphp

<aside class="sidebar ula-shell-sidebar" id="dashboardSidebar" aria-label="{{ __('nav.menu') }}">
    {{-- A company logo (Settings → General) replaces the UlaSpace mark and puts the company name first. --}}
    <div class="ula-shell-brand">
        @if($organization->logo_url)
            <span class="ula-shell-logo"><img id="sidebar-org-logo" src="{{ $organization->logo_url }}" alt="{{ $organization->name }}"></span>
            <div class="ula-shell-brand-text">
                <span class="ula-shell-brand-name ula-shell-brand-name--org">{{ $organization->name }}</span>
                <span class="ula-shell-brand-org">UlaSpace</span>
            </div>
        @else
            <svg role="img" aria-label="UlaSpace" class="ula-shell-mark" viewBox="-1.2 -1.3 60 40" fill="currentColor"><path d="M0 38.734L1.493 30.973L4.179 20.824L6.865 11.869C8.259 7.491 11.94 4.207 17.91 2.018C26.268 -0.569 34.427 -0.669 42.387 1.719C49.153 3.311 54.128 7.292 57.312 13.66L57.312 38.734L26.268 38.734L25.074 27.988C23.482 20.824 21.591 17.242 19.403 17.242C17.214 18.038 15.721 21.819 14.925 28.585L14.328 38.734L0 38.734Z"/></svg>
            <div class="ula-shell-brand-text">
                <span class="ula-shell-brand-name">UlaSpace</span>
                <span class="ula-shell-brand-org">{{ $organization->name }}</span>
            </div>
        @endif
        <button type="button" class="sidebar-toggle-btn ula-shell-toggle" onclick="toggleSidebarCollapse()" aria-label="{{ __('nav.toggle') }}" title="{{ __('nav.toggle') }}">
            <span class="material-symbols-rounded">left_panel_close</span>
        </button>
    </div>

    <nav class="ula-shell-nav">
        @foreach($shellSections as [$sectionLabel, $items])
            @php $visibleItems = array_filter($items, fn ($item) => $item[3]); @endphp
            @if(count($visibleItems))
            <div class="ula-shell-section">
                <span class="ula-shell-section-label">{{ __($sectionLabel) }}</span>
                @foreach($visibleItems as [$key, $label, $icon, $visible, $extra])
                    @php
                        $isActive = $shellActive === $key;
                        $classes = 'nav-tab-btn ula-shell-item' . ($isActive ? ' active' : '');
                    @endphp
                    @if(isset($shellRoutes[$key]))
                        <a href="{{ $shellRoutes[$key] }}" class="{{ $classes }}" data-tooltip="{{ __($label) }}">
                    @elseif($shellMode === 'tabs')
                        <button type="button" id="nav-btn-{{ $key }}" class="{{ $classes }}" onclick="switchAdminTab('{{ $key }}')" data-tooltip="{{ __($label) }}" @if($isActive) aria-current="page" @endif>
                    @else
                        <a href="{{ route('dashboard') }}#{{ $key }}" class="{{ $classes }}" data-tooltip="{{ __($label) }}" @if($isActive) aria-current="page" @endif>
                    @endif
                        <span class="material-symbols-rounded ula-shell-icon">{{ $icon }}</span>
                        <span class="ula-shell-label">{{ __($label) }}</span>
                        @if($extra === 'live')
                            <span class="ula-shell-live">{{ __('nav.live') }}</span>
                        @elseif($extra === 'external')
                            <span class="material-symbols-rounded ula-shell-external">north_west</span>
                        @endif
                    @if(!isset($shellRoutes[$key]) && $shellMode === 'tabs')
                        </button>
                    @else
                        </a>
                    @endif
                @endforeach
            </div>
            @endif
        @endforeach

        @if($user->isSuperAdmin())
            <div class="ula-shell-section">
                <a href="{{ route('superadmin.dashboard') }}" class="nav-tab-btn ula-shell-item" data-tooltip="{{ __('nav.superadmin') }}">
                    <span class="material-symbols-rounded ula-shell-icon">shield_person</span>
                    <span class="ula-shell-label">{{ __('nav.superadmin') }}</span>
                </a>
            </div>
        @endif
    </nav>

    <div class="ula-shell-user">
        @if($user->avatar_url)
            <img id="sidebar-user-avatar" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="ula-shell-avatar">
        @else
            <span class="ula-shell-avatar" aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</span>
        @endif
        <div class="ula-shell-user-text">
            <span class="ula-shell-user-name">{{ $user->name }}</span>
            <span class="ula-shell-user-role">{{ $membership->role->name ?? __('Member') }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="ula-shell-logout" aria-label="{{ __('nav.logout') }}" title="{{ __('nav.logout') }}">
                <span class="material-symbols-rounded">logout</span>
            </button>
        </form>
    </div>
</aside>
