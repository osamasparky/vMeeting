<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $organization->name }} — Workspace Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Clean White & Saudi Brand Theme */
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;

            /* Saudi Brand Palette from color.webp */
            --brand-teal: #00b4b3;
            --brand-pine: #00726c;
            --brand-ocean: #004862;
            --brand-navy: #012c41;
            --brand-green: #006847;
            --brand-lime: #a7c545;
            --brand-gold: #ffd136;
            --brand-orange: #f57b36;
            --brand-coral: #ff3600;
            --brand-crimson: #d20005;

            --accent-primary: #00b4b3;
            --accent-gradient: linear-gradient(135deg, #00b4b3, #00726c, #004862);
            --accent-green: #006847;
            --accent-amber: #f57b36;

            --text-primary: #012c41;
            --text-secondary: #004862;
            --text-muted: #64748b;

            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-card: 0 4px 20px -2px rgba(1, 44, 65, 0.06), 0 2px 6px -1px rgba(1, 44, 65, 0.04);
            --shadow-hover: 0 12px 28px -4px rgba(1, 44, 65, 0.12), 0 4px 10px -2px rgba(1, 44, 65, 0.06);

            --font-family: {{ app()->getLocale() === 'ar' ? "'Cairo', 'Inter', sans-serif" : "'Inter', 'Cairo', sans-serif" }};
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-family); }
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            background: var(--bg-secondary);
            border-inline-end: 1px solid var(--border-color);
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            position: fixed;
            inset-inline-start: 0;
            top: 0;
            height: 100vh;
            z-index: 50;
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.02);
            transition: transform 0.3s ease;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 8px;
            margin-bottom: 28px;
            cursor: pointer;
        }

        .sidebar-logo-icon {
            width: 38px;
            height: 38px;
            background: var(--accent-gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 800;
            color: white;
            box-shadow: 0 4px 14px rgba(0, 180, 179, 0.35);
        }

        .sidebar-logo-text {
            font-size: 15px;
            font-weight: 800;
            color: var(--brand-navy);
            letter-spacing: -0.3px;
        }

        .sidebar-section {
            margin-bottom: 20px;
        }

        .sidebar-section-title {
            font-size: 10px;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 0 10px;
            margin-bottom: 6px;
        }

        .nav-tab-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            background: transparent;
            border: none;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .nav-tab-btn:hover {
            background: #f1f5f9;
            color: var(--brand-navy);
        }

        .nav-tab-btn.active {
            background: linear-gradient(135deg, var(--brand-teal), var(--brand-pine));
            color: white;
            box-shadow: 0 4px 12px rgba(0, 180, 179, 0.25);
        }

        .sidebar-user {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: var(--radius-md);
            background: #f8fafc;
            border: 1px solid var(--border-color);
        }

        .sidebar-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            color: white;
        }

        /* ── Main Content ── */
        .main-content {
            flex: 1;
            margin-inline-start: 260px;
            padding: 32px 40px;
            max-width: 1300px;
            width: 100%;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 14px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 900;
            color: var(--brand-navy);
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 13px;
            margin-top: 4px;
            font-weight: 500;
        }

        .header-btn {
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 4px 14px rgba(0, 180, 179, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 180, 179, 0.45);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--brand-green), #004d34);
            color: white;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(0, 104, 71, 0.25);
        }

        .btn-outline {
            background: #ffffff;
            border: 1px solid var(--border-color);
            color: var(--brand-navy);
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: var(--brand-teal);
        }

        /* ── Stats ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-card);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .stat-val {
            font-size: 28px;
            font-weight: 900;
            color: var(--brand-navy);
            margin: 6px 0 2px;
        }

        .stat-lbl {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Tab Views ── */
        .tab-view {
            display: none;
        }

        .tab-view.active {
            display: block;
        }

        /* ── Tables & Cards ── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-card);
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--brand-navy);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }

        .data-table th {
            padding: 12px 14px;
            background: #f8fafc;
            color: var(--text-secondary);
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table td {
            padding: 14px;
            border-bottom: 1px solid var(--border-color);
            color: var(--brand-navy);
            background: #ffffff;
        }
        .data-table tr:hover td {
            background: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
        }

        .badge-green { background: rgba(0, 104, 71, 0.1); color: var(--brand-green); border: 1px solid rgba(0, 104, 71, 0.25); }
        .badge-purple { background: rgba(0, 180, 179, 0.12); color: var(--brand-pine); border: 1px solid rgba(0, 180, 179, 0.3); }
        .badge-amber { background: rgba(245, 123, 54, 0.12); color: var(--brand-orange); border: 1px solid rgba(245, 123, 54, 0.3); }

        /* ── Modal Overlay ── */
        .modal, .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(1, 44, 65, 0.5);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .modal-box, .modal-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 28px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 60px rgba(1, 44, 65, 0.25);
            color: var(--brand-navy);
            position: relative;
            animation: modalFadeIn 0.2s ease-out;
        }

        @keyframes modalFadeIn {
            from { transform: translateY(12px) scale(0.98); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--brand-navy);
        }

        .modal-close {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            border-radius: 6px;
            transition: color 0.15s;
        }
        .modal-close:hover {
            color: var(--brand-crimson);
        }

        .mobile-menu-btn {
            display: none;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            font-size: 20px;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--brand-navy);
        }

        @media (max-width: 900px) {
            .sidebar {
                transform: translateX({{ app()->getLocale() === 'ar' ? '100%' : '-100%' }});
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-inline-start: 0;
                padding: 20px 16px;
            }
            .mobile-menu-btn {
                display: block;
            }
        }
    </style>
</head>
<body>

    <!-- Left Admin Sidebar -->
    <aside class="sidebar" id="dashboardSidebar">
        <div class="sidebar-logo" onclick="switchAdminTab('overview')">
            <div class="sidebar-logo-icon">🏢</div>
            <div class="sidebar-logo-text">{{ $organization->name }}</div>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Workspace</div>
            <button class="nav-tab-btn active" onclick="switchAdminTab('overview')">
                <span>📊</span> Overview
            </button>
            <a href="{{ route('office') }}" class="nav-tab-btn" style="text-decoration: none;">
                <span>🚀</span> Virtual Office
            </a>
            <a href="{{ route('editor') }}" class="nav-tab-btn" style="text-decoration: none;">
                <span>🎨</span> Floor Map Editor
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('Administration') }}</div>
            <button class="nav-tab-btn" onclick="switchAdminTab('members')">
                <span>👥</span> {{ __('Team Members') }} ({{ $members->count() }})
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('billing')">
                <span>💎</span> {{ __('Billing & Subscription') }}
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('rooms')">
                <span>🏢</span> {{ __('Rooms & Doors') }} ({{ $rooms->count() }})
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('guests')">
                <span>🔗</span> {{ __('Guest Links') }} ({{ $guestInvitations->count() }})
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('departments')">
                <span>🏛️</span> {{ __('Departments & Teams') }}
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('audit')">
                <span>📋</span> {{ __('Audit Logs') }}
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('settings')">
                <span>⚙️</span> {{ __('Settings') }}
            </button>

            @if($user->isSuperAdmin())
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('superadmin.dashboard') }}" class="nav-tab-btn" style="background: rgba(99, 102, 241, 0.15); color: #c7d2fe; border: 1px solid rgba(99, 102, 241, 0.3); text-decoration: none;">
                    <span>⚡</span> <strong>{{ __('Super Admin Portal') }}</strong>
                </a>
            </div>
            @endif
        </div>

        <div style="padding: 10px; margin-bottom: 10px;">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 6px; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); border-radius: 8px; color: white; text-decoration: none; font-size: 12px; font-weight: 700;">🌐 English</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 6px; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); border-radius: 8px; color: white; text-decoration: none; font-size: 12px; font-weight: 700;">🌐 العربية</a>
            @endif
        </div>

        <div class="sidebar-user">
            <div class="sidebar-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $user->name }}</div>
                <div style="font-size: 11px; color: var(--text-muted);">{{ $membership->role->name ?? 'Company Admin' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 15px;" title="Logout">🚪</button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">

        <!-- 1. OVERVIEW TAB -->
        <div id="tab-overview" class="tab-view active">
            <div class="page-header">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button class="mobile-menu-btn" onclick="toggleDashboardSidebar()">☰</button>
                    <div>
                        <h1 class="page-title">{{ __('Executive Dashboard') }}</h1>
                        <p class="page-subtitle">{{ __('Welcome back') }}, {{ explode(' ', $user->name)[0] }}! {{ __('Manage your workplace and live presence.') }}</p>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('office') }}" class="header-btn btn-primary">
                        <span>🚀</span> {{ __('Enter Office') }}
                    </a>
                    <button onclick="openInviteModal()" class="header-btn btn-success">
                        <span>+</span> {{ __('Invite Member / Guest') }}
                    </button>
                </div>
            </div>

            <!-- Executive KPIs Grid -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <div class="stat-card" style="border-top: 3px solid var(--brand-teal);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div class="stat-lbl" style="font-size: 12px; font-weight: 800; color: var(--brand-ocean);">👥 {{ __('Workplace Presence') }}</div>
                        <span style="font-size: 10px; font-weight: 800; color: var(--brand-green); background: rgba(0, 104, 71, 0.1); padding: 2px 6px; border-radius: 6px;">▲ +14%</span>
                    </div>
                    <div class="stat-val" style="color: var(--brand-teal); font-size: 28px; font-weight: 900; margin-bottom: 4px;">{{ $stats['presence_rate'] }}%</div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ $stats['members'] }} {{ __('Registered team members') }}</div>
                </div>

                <div class="stat-card" style="border-top: 3px solid var(--brand-pine);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div class="stat-lbl" style="font-size: 12px; font-weight: 800; color: var(--brand-ocean);">🎙️ {{ __('Meetings & Sessions') }}</div>
                        <span style="font-size: 10px; font-weight: 800; color: var(--brand-teal); background: rgba(0, 180, 179, 0.1); padding: 2px 6px; border-radius: 6px;">{{ $stats['collaboration_hours'] }}h</span>
                    </div>
                    <div class="stat-val" style="color: var(--brand-pine); font-size: 28px; font-weight: 900; margin-bottom: 4px;">{{ $stats['meetings_count'] }}</div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ __('Spatial audio & video sessions') }}</div>
                </div>

                <div class="stat-card" style="border-top: 3px solid var(--brand-orange);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div class="stat-lbl" style="font-size: 12px; font-weight: 800; color: var(--brand-ocean);">🏢 {{ __('Room Occupancy') }}</div>
                        <span style="font-size: 10px; font-weight: 800; color: var(--brand-orange); background: rgba(245, 123, 54, 0.1); padding: 2px 6px; border-radius: 6px;">{{ $rooms->count() }} {{ __('Rooms') }}</span>
                    </div>
                    <div class="stat-val" style="color: var(--brand-orange); font-size: 28px; font-weight: 900; margin-bottom: 4px;">{{ $stats['occupancy_rate'] }}%</div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ __('Active collaborative spaces') }}</div>
                </div>

                <div class="stat-card" style="border-top: 3px solid var(--brand-navy);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div class="stat-lbl" style="font-size: 12px; font-weight: 800; color: var(--brand-ocean);">💎 {{ __('Company Plan') }}</div>
                        <span style="font-size: 10px; font-weight: 800; color: var(--brand-teal); background: rgba(0, 180, 179, 0.1); padding: 2px 6px; border-radius: 6px;">{{ $stats['guests'] }} {{ __('Guests') }}</span>
                    </div>
                    <div class="stat-val" style="color: var(--brand-navy); font-size: 22px; font-weight: 900; margin-bottom: 4px;">{{ $organization->plan->name ?? 'Enterprise' }}</div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ __('Capacity up to') }} {{ $organization->plan->max_users ?? '500' }} {{ __('Users') }}</div>
                </div>
            </div>

            <!-- Productivity Health Score & System Metrics Strip -->
            <div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: linear-gradient(135deg, rgba(0, 180, 179, 0.05), rgba(0, 104, 71, 0.03)); border: 1px solid rgba(0, 180, 179, 0.2);">
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, var(--brand-green), var(--brand-teal)); color: white; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 900;">
                            ⚡
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 900; color: var(--brand-navy);">{{ __('Workplace Health & Productivity Index') }}</div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ __('Real-time collaboration metrics and system uptime') }}</div>
                        </div>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: center;">
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">{{ __('Health Score') }}</div>
                            <div style="font-size: 16px; font-weight: 900; color: var(--brand-green);">{{ $stats['productivity_score'] }} <span style="font-size: 11px; color: var(--text-muted);">/ 100</span></div>
                        </div>
                        <div style="width: 1px; height: 30px; background: var(--border-panel);"></div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">{{ __('Screen Share Usage') }}</div>
                            <div style="font-size: 16px; font-weight: 900; color: var(--brand-teal);">{{ $stats['screen_share_rate'] }}%</div>
                        </div>
                        <div style="width: 1px; height: 30px; background: var(--border-panel);"></div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">{{ __('Spatial Audio Uptime') }}</div>
                            <div style="font-size: 16px; font-weight: 900; color: var(--brand-pine);">{{ $stats['audio_quality'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Analytics Widgets: Department Breakdown & Room Utilization -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Widget 1: Department Staff Allocation -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        <h3 class="card-title" style="font-size: 14px; font-weight: 800; color: var(--brand-navy);">📊 {{ __('Staff Distribution by Department') }}</h3>
                        <span style="font-size: 11px; color: var(--brand-teal); font-weight: 800;">{{ $departments->count() }} {{ __('Departments') }}</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @forelse($departments as $d)
                            @php
                                $dMems = $members->filter(function($mem) use ($d, $organization) {
                                    $prof = $mem->user->profiles->where('organization_id', $organization->id)->first();
                                    return $prof && $prof->department_id == $d->id;
                                });
                                $pct = $stats['members'] > 0 ? round(($dMems->count() / $stats['members']) * 100) : 0;
                            @endphp
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; margin-bottom: 4px;">
                                    <span style="color: var(--brand-navy);">🏛️ {{ $d->name }}</span>
                                    <span style="color: var(--text-muted);">{{ $dMems->count() }} {{ __('Staff') }} ({{ $pct }}%)</span>
                                </div>
                                <div style="width: 100%; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                                    <div style="width: {{ max(8, $pct) }}%; height: 100%; background: linear-gradient(90deg, var(--brand-teal), var(--brand-pine)); border-radius: 4px;"></div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-dim); font-size: 12px; padding: 20px;">
                                {{ __('No departments created yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Widget 2: Room Occupancy & Capacity -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        <h3 class="card-title" style="font-size: 14px; font-weight: 800; color: var(--brand-navy);">🏢 {{ __('Room Occupancy & Status') }}</h3>
                        <span style="font-size: 11px; color: var(--brand-green); font-weight: 800;">{{ $rooms->count() }} {{ __('Live Spaces') }}</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @forelse($rooms->take(4) as $r)
                            <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid var(--border-panel); padding: 8px 12px; border-radius: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 12px; height: 12px; border-radius: 50%; background: {{ $r->color ?? 'var(--brand-teal)' }};"></div>
                                    <div>
                                        <div style="font-size: 13px; font-weight: 800; color: var(--brand-navy);">{{ $r->name }}</div>
                                        <div style="font-size: 10px; color: var(--text-muted);">{{ __('Capacity') }}: {{ $r->capacity ?? 10 }} {{ __('seats') }}</div>
                                    </div>
                                </div>
                                <span class="badge {{ $r->access_mode === 'private' ? 'badge-amber' : 'badge-green' }}" style="font-size: 10px;">
                                    {{ $r->access_mode === 'private' ? '🔒 ' . __('Private / Knock') : '👥 ' . __('Open Access') }}
                                </span>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-dim); font-size: 12px; padding: 20px;">
                                {{ __('No rooms configured yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Content Grid (Quick Actions & Recent Activity) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Quick Actions') }}</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <a href="{{ route('office') }}" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; text-decoration: none; color: var(--brand-navy); display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center; transition: all 0.2s;">
                            <span style="font-size: 26px;">🚀</span>
                            <strong style="font-size: 13px;">{{ __('Virtual Workplace') }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ __('Spatial voice & video') }}</span>
                        </a>
                        <a href="{{ route('editor') }}" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; text-decoration: none; color: var(--brand-navy); display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center; transition: all 0.2s;">
                            <span style="font-size: 26px;">🎨</span>
                            <strong style="font-size: 13px;">{{ __('Floor Designer') }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ __('Furniture & partitions') }}</span>
                        </a>
                        <div onclick="openInviteModal()" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; color: var(--brand-navy); display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <span style="font-size: 26px;">🔗</span>
                            <strong style="font-size: 13px;">{{ __('Instant Guest Link') }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ __('No login needed') }}</span>
                        </div>
                        <div onclick="switchAdminTab('rooms')" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; color: var(--brand-navy); display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <span style="font-size: 26px;">🚪</span>
                            <strong style="font-size: 13px;">{{ __('Manage Room Doors') }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ __('Lock & permissions') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Recent Activity') }}</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @forelse($auditLogs as $log)
                            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                                <div>
                                    <strong style="color: var(--brand-navy);">{{ $log->action }}</strong>
                                    <span style="color: var(--text-muted);">on {{ class_basename($log->auditable_type) }}</span>
                                </div>
                                <span style="color: var(--text-muted); font-size: 11px;">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        @empty
                            <div style="color: var(--text-muted); font-size: 13px;">{{ __('Workspace created and running smoothly.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TEAM MEMBERS TAB -->
        <div id="tab-members" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ __('Team Members & Roles') }}</h1>
                    <p class="page-subtitle">{{ __('Manage organization membership, departments, teams, and security roles.') }}</p>
                </div>
                <button onclick="openInviteModal()" class="header-btn btn-primary">
                    <span>+</span> {{ __('Invite Member') }}
                </button>
            </div>

            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Member') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Department & Team') }}</th>
                            <th>{{ __('Job Title') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $m)
                            @php
                                $profile = $m->user->profiles->where('organization_id', $organization->id)->first();
                                $memberDept = $departments->where('id', $profile?->department_id)->first();
                                $memberTeam = $teams->where('id', $profile?->team_id)->first();
                            @endphp
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 34px; height: 34px; border-radius: 8px; background: linear-gradient(135deg, var(--brand-teal), var(--brand-pine)); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; color: white;">
                                            {{ strtoupper(substr($m->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <strong style="color: var(--brand-navy); font-size: 13px;">{{ $m->user->name }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);">{{ $m->user->email }}</td>
                                <td>
                                    @if($memberDept)
                                        <div style="display: flex; flex-direction: column; gap: 2px;">
                                            <span class="badge badge-purple" style="font-size: 11px;">🏛️ {{ $memberDept->name }}</span>
                                            @if($memberTeam)
                                                <span style="font-size: 10px; color: var(--text-muted); font-weight: 700;">↳ 👥 {{ $memberTeam->name }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color: var(--text-dim); font-size: 11px; font-style: italic;">— {{ __('Not Assigned') }} —</span>
                                    @endif
                                </td>
                                <td style="font-size: 12px; font-weight: 600; color: var(--brand-ocean);">
                                    {{ $profile?->job_title ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge badge-teal">{{ $m->role->name ?? __('Company Admin') }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-green">{{ __('Active') }}</span>
                                </td>
                                <td>
                                    <button onclick="openAssignModal('{{ $m->id }}', '{{ addslashes($m->user->name) }}', '{{ $profile?->department_id }}', '{{ $profile?->team_id }}', '{{ $m->role_id }}', '{{ addslashes($profile?->job_title ?? '') }}')" class="header-btn btn-outline" style="padding: 5px 12px; font-size: 11px; font-weight: 800;">
                                        <span>⚙️</span> {{ __('Assign Department / Role') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2.5 BILLING & SUBSCRIPTION TAB -->
        <div id="tab-billing" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ __('Billing & Subscription') }}</h1>
                    <p class="page-subtitle">{{ __('Manage your plan tier, seat capacity, and workspace upgrade') }}</p>
                </div>
            </div>

            @php
                $currentPlan = $organization->plan ?? \App\Domains\Tenancy\Models\Plan::where('slug', 'free')->first();
                $seatLimit = $currentPlan?->seat_limit ?? 5;
                $usedSeats = $members->count();
                $isUnlimited = $seatLimit === 0;
                $seatPercent = $isUnlimited ? 20 : min(100, round(($usedSeats / max(1, $seatLimit)) * 100));
            @endphp

            <!-- Current Plan Card -->
            <div class="card" style="margin-bottom: 28px; background: linear-gradient(135deg, rgba(0, 180, 179, 0.08), rgba(255, 255, 255, 0.9)); border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <span style="font-size: 11px; font-weight: 800; color: var(--brand-teal); text-transform: uppercase; letter-spacing: 0.5px;">{{ __('Current Plan') }}</span>
                        <h2 style="font-size: 24px; font-weight: 900; color: var(--brand-navy); margin-top: 4px;">💎 {{ $currentPlan->name ?? __('Free Plan') }}</h2>
                        <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                            ${{ number_format($currentPlan->price ?? 0, 2) }}/month &bull; {{ $isUnlimited ? __('Unlimited Seats') : $seatLimit . ' ' . __('Total Seats') }}
                        </p>
                    </div>
                    <div style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">
                        <div style="font-size: 24px; font-weight: 900; color: var(--brand-green);">
                            {{ $usedSeats }} / {{ $isUnlimited ? '∞' : $seatLimit }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">{{ __('Seats used') }}</div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div style="background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden; margin-bottom: 16px;">
                    <div style="width: {{ $seatPercent }}%; background: {{ $seatPercent > 90 ? 'var(--brand-crimson)' : 'var(--brand-teal)' }}; height: 100%; border-radius: 4px;"></div>
                </div>
            </div>

            <!-- Available Upgrade Plans Grid -->
            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 16px; color: var(--brand-navy);">{{ __('Available Subscription Plans') }}</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
                @foreach($allPlans as $p)
                @php
                    $isCurrent = ($organization->plan_id == $p->id);
                @endphp
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-color: {{ $isCurrent ? 'var(--brand-teal)' : 'var(--border-color)' }}; background: {{ $isCurrent ? 'rgba(0, 180, 179, 0.05)' : '#ffffff' }};">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="font-size: 16px; color: var(--brand-navy);">💎 {{ $p->name }}</strong>
                            @if($isCurrent)
                                <span class="badge badge-green">{{ __('Current') }}</span>
                            @endif
                        </div>

                        <div style="font-size: 24px; font-weight: 900; color: var(--brand-navy); margin-bottom: 14px;">
                            ${{ number_format($p->price, 2) }}
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);">/mo</span>
                        </div>

                        <div style="font-size: 13px; color: var(--text-secondary); display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px;">
                            <div>👥 <strong>{{ $p->seat_limit === 0 ? __('Unlimited') : $p->seat_limit }}</strong> {{ __('Users / Seats') }}</div>
                            <div>🚪 <strong>{{ $p->room_limit === 0 ? __('Unlimited') : $p->room_limit }}</strong> {{ __('Meeting Rooms') }}</div>
                            <div>💾 <strong>{{ $p->storage_limit_gb === 0 ? __('Unlimited') : $p->storage_limit_gb . ' GB' }}</strong> {{ __('Storage') }}</div>
                        </div>
                    </div>

                    <div>
                        @if($isCurrent)
                            <button disabled class="header-btn btn-outline" style="width: 100%; justify-content: center; opacity: 0.6;">
                                ✅ {{ __('Active Plan') }}
                            </button>
                        @else
                            <form method="POST" action="{{ route('organization.upgrade_plan') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $p->id }}">
                                <button type="submit" class="header-btn btn-primary" style="width: 100%; justify-content: center;">
                                    🚀 {{ __('Upgrade to') }} {{ $p->name }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 3. ROOMS TAB -->
        <div id="tab-rooms" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ __('Meeting Rooms & Doors') }}</h1>
                    <p class="page-subtitle">{{ __('Configure private offices, conference rooms, and door lock states.') }}</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('editor') }}" class="header-btn btn-primary">
                        <span>🎨</span> {{ __('Launch Floor Editor') }}
                    </a>
                </div>
            </div>

            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Room Name') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Capacity') }}</th>
                            <th>{{ __('Door Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rooms as $r)
                            <tr>
                                <td>
                                    <strong style="color: var(--brand-navy);">🏢 {{ $r->name }}</strong>
                                </td>
                                <td>{{ ucfirst($r->type) }}</td>
                                <td>{{ $r->capacity }}</td>
                                <td>
                                    <span class="badge {{ $r->access_mode === 'private' ? 'badge-amber' : 'badge-green' }}">
                                        {{ $r->access_mode === 'private' ? __('Locked') : __('Open') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('office') }}" class="header-btn btn-outline" style="padding: 4px 10px; font-size: 11px; text-decoration: none;">{{ __('Enter Office') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. GUEST INVITATIONS TAB -->
        <div id="tab-guests" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ __('Guest Meeting Links') }}</h1>
                    <p class="page-subtitle">{{ __('Generate instant join links for clients, interviewees, and external partners.') }}</p>
                </div>
                <button onclick="openInviteModal()" class="header-btn btn-success">
                    <span>⚡</span> {{ __('Create Guest Link') }}
                </button>
            </div>

            <div class="card">
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
                                    <strong style="color: var(--brand-green);">👤 {{ $inv->guest_name }}</strong>
                                </td>
                                <td>🏢 {{ $inv->room->name ?? 'Main Conference' }}</td>
                                <td>{{ $inv->expires_at ? $inv->expires_at->diffForHumans() : __('Never') }}</td>
                                <td>
                                    <code style="background: #f8fafc; border: 1px solid var(--border-color); padding: 4px 8px; border-radius: 4px; font-size: 11px; color: var(--brand-teal);">
                                        /guest/join/{{ substr($inv->token, 0, 16) }}...
                                    </code>
                                </td>
                                <td>
                                    <a href="{{ url('/guest/join/' . $inv->token) }}" target="_blank" class="header-btn btn-outline" style="padding: 4px 10px; font-size: 11px; text-decoration: none;">
                                        👁️ {{ __('Open') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No guest invitations generated yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. DEPARTMENTS & TEAMS TAB -->
        <div id="tab-departments" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ __('Departments & Teams') }}</h1>
                    <p class="page-subtitle">{{ __('Organize your organization staff, distribute members across departments, and manage sub-teams.') }}</p>
                </div>
                <button onclick="openDepartmentModal()" class="header-btn btn-primary">
                    <span>+</span> {{ __('New Department') }}
                </button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px;">
                @forelse($departments as $dept)
                    @php
                        $deptMembers = $members->filter(function($mem) use ($dept, $organization) {
                            $prof = $mem->user->profiles->where('organization_id', $organization->id)->first();
                            return $prof && $prof->department_id == $dept->id;
                        });
                    @endphp
                    <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--border-panel); box-shadow: 0 4px 15px rgba(1, 44, 65, 0.04);">
                        <div>
                            <!-- Department Header -->
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(0, 180, 179, 0.1); color: var(--brand-teal); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                        🏛️
                                    </div>
                                    <div>
                                        <h3 style="font-size: 16px; font-weight: 800; color: var(--brand-navy); margin-bottom: 2px;">{{ $dept->name }}</h3>
                                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 700;">{{ $dept->teams->count() }} {{ __('Teams') }} • {{ $deptMembers->count() }} {{ __('Members') }}</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button onclick="editDepartment('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" style="background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 6px; padding: 5px 8px; cursor: pointer; color: var(--brand-navy);" title="{{ __('Edit Department') }}">✏️</button>
                                    <form action="{{ route('departments.delete', $dept->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this department?') }}');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 6px; padding: 5px 8px; cursor: pointer; color: var(--brand-crimson);" title="{{ __('Delete Department') }}">🗑️</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Sub-Teams Section -->
                            <div style="background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 10px; padding: 12px; margin-bottom: 14px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                    <span style="font-size: 11px; font-weight: 800; color: var(--brand-ocean); text-transform: uppercase;">{{ __('Sub-Teams') }}</span>
                                    <button onclick="openTeamModal('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" style="background: none; border: none; font-size: 11px; font-weight: 800; color: var(--brand-teal); cursor: pointer;">+ {{ __('Add Team') }}</button>
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    @forelse($dept->teams as $t)
                                        <div style="background: #ffffff; border: 1px solid var(--border-panel); border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 700; color: var(--brand-navy); display: flex; align-items: center; gap: 6px;">
                                            <span>👥 {{ $t->name }}</span>
                                            <form action="{{ route('teams.delete', $t->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this team?') }}');" style="display: inline; margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 11px; padding: 0; line-height: 1;" title="{{ __('Delete Team') }}">✕</button>
                                            </form>
                                        </div>
                                    @empty
                                        <span style="font-size: 11px; color: var(--text-dim); font-style: italic;">{{ __('No sub-teams created yet.') }}</span>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Assigned Department Members -->
                            <div>
                                <span style="font-size: 11px; font-weight: 800; color: var(--brand-ocean); text-transform: uppercase; display: block; margin-bottom: 8px;">{{ __('Assigned Staff') }} ({{ $deptMembers->count() }})</span>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    @forelse($deptMembers->take(4) as $dm)
                                        @php
                                            $prof = $dm->user->profiles->where('organization_id', $organization->id)->first();
                                            $tObj = $teams->where('id', $prof?->team_id)->first();
                                        @endphp
                                        <div style="display: flex; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid #f1f5f9; padding: 6px 10px; border-radius: 8px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="width: 24px; height: 24px; border-radius: 6px; background: var(--brand-navy); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800;">
                                                    {{ strtoupper(substr($dm->user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <span style="font-size: 12px; font-weight: 800; color: var(--brand-navy);">{{ $dm->user->name }}</span>
                                                    @if($prof?->job_title)
                                                        <span style="font-size: 10px; color: var(--text-muted);"> • {{ $prof->job_title }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($tObj)
                                                <span style="font-size: 10px; background: rgba(0, 180, 179, 0.1); color: var(--brand-teal); font-weight: 700; padding: 2px 6px; border-radius: 4px;">{{ $tObj->name }}</span>
                                            @endif
                                        </div>
                                    @empty
                                        <div style="text-align: center; padding: 10px; font-size: 11px; color: var(--text-dim); background: #ffffff; border: 1px dashed var(--border-panel); border-radius: 8px;">
                                            {{ __('No members assigned to this department yet.') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card" style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px;">
                        <div style="font-size: 32px; margin-bottom: 8px;">🏛️</div>
                        <h3 style="font-size: 16px; font-weight: 800; color: var(--brand-navy); margin-bottom: 6px;">{{ __('No departments found') }}</h3>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">{{ __('Create departments and divide your organization into structured functional teams.') }}</p>
                        <button onclick="openDepartmentModal()" class="header-btn btn-primary">
                            <span>+</span> {{ __('New Department') }}
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 6. AUDIT LOGS TAB -->
        <div id="tab-audit" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ __('Audit Logs') }}</h1>
                    <p class="page-subtitle">{{ __('Track administrative actions and security events across the workplace.') }}</p>
                </div>
            </div>

            <div class="card">
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
                                <td><span class="badge badge-purple">{{ $log->action }}</span></td>
                                <td>{{ class_basename($log->auditable_type) }}</td>
                                <td>{{ substr($log->user_id ?? 'System', 0, 8) }}</td>
                                <td>{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    Audit trail is clean and recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 7. SETTINGS TAB -->
        <div id="tab-settings" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ __('Workspace Settings') }}</h1>
                    <p class="page-subtitle">{{ __('Configure organization branding, security policies, and localization.') }}</p>
                </div>
            </div>

            <div class="card" style="max-width: 600px;">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--brand-ocean); margin-bottom: 6px; text-transform: uppercase;">{{ __('Workspace Name') }}</label>
                        <input type="text" value="{{ $organization->name }}" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--brand-navy); font-size: 13px; font-weight: 600;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--brand-ocean); margin-bottom: 6px; text-transform: uppercase;">{{ __('URL Slug') }}</label>
                        <input type="text" value="{{ $organization->slug }}" readonly style="width: 100%; background: #f1f5f9; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--brand-teal); font-size: 13px; font-family: monospace; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--brand-ocean); margin-bottom: 6px; text-transform: uppercase;">{{ __('Timezone') }}</label>
                        <input type="text" value="{{ $organization->timezone }}" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--brand-navy); font-size: 13px; font-weight: 600;">
                    </div>
                    <button class="header-btn btn-primary" style="margin-top: 10px; width: fit-content;">{{ __('Save Workspace Changes') }}</button>
                </div>
            </div>
        </div>

    </main>

    <!-- Invite Modal -->
    <div id="invite-modal" class="modal">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 800; color: var(--brand-navy);">📨 {{ __('Invite & Guest Access') }}</h3>
                <button onclick="closeInviteModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
            </div>

            <!-- Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 20px; background: #f1f5f9; padding: 4px; border-radius: 10px;">
                <button onclick="switchInviteTab('guest')" id="tab-guest-btn" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 13px; font-weight: 700; cursor: pointer; background: var(--brand-teal); color: white;">
                    🔗 {{ __('Guest Meeting Link') }}
                </button>
                <button onclick="switchInviteTab('member')" id="tab-member-btn" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 13px; font-weight: 700; cursor: pointer; background: none; color: var(--text-muted);">
                    👤 {{ __('Team Member') }}
                </button>
            </div>

            <!-- Guest Form -->
            <div id="guest-tab-content">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 6px;">{{ __('Destination Room') }}</label>
                        <select id="invite-room-select" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">🏢 {{ $r->name }} ({{ ucfirst($r->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 6px;">{{ __('Guest Name / Label') }}</label>
                        <input type="text" id="invite-guest-name" value="Investor / Partner" placeholder="e.g. Sarah Miller" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 6px;">{{ __('Link Expiration') }}</label>
                        <select id="invite-guest-hours" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="1">1 Hour</option>
                            <option value="12">12 Hours</option>
                            <option value="24" selected>24 Hours (1 Day)</option>
                            <option value="72">72 Hours (3 Days)</option>
                        </select>
                    </div>

                    <button onclick="generateGuestLink()" id="btn-generate-guest" style="margin-top: 6px; background: linear-gradient(135deg, var(--brand-green), #004d34); color: white; font-weight: 800; border: none; border-radius: 10px; padding: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; box-shadow: 0 4px 14px rgba(0, 104, 71, 0.25);">
                        <span>⚡</span> {{ __('Generate Instant Guest Link') }}
                    </button>

                    <div id="guest-result-box" style="display: none; background: rgba(0, 104, 71, 0.08); border: 1px solid rgba(0, 104, 71, 0.25); border-radius: 10px; padding: 12px; margin-top: 10px;">
                        <div style="font-size: 11px; font-weight: 800; color: var(--brand-green); text-transform: uppercase; margin-bottom: 6px;">✅ Invitation Link Ready!</div>
                        <input type="text" id="guest-link-output" readonly style="width: 100%; background: #ffffff; border: 1px solid var(--border-color); border-radius: 6px; padding: 8px; color: var(--brand-navy); font-size: 12px; font-family: monospace; margin-bottom: 8px;">
                        <div style="display: flex; gap: 8px;">
                            <button onclick="copyGuestLink()" id="btn-copy-link" style="flex: 1; background: var(--brand-teal); color: white; font-weight: 700; border: none; border-radius: 6px; padding: 8px; cursor: pointer; font-size: 12px;">
                                📋 {{ __('Copy Link') }}
                            </button>
                            <a id="guest-open-link" href="#" target="_blank" style="background: #ffffff; border: 1px solid var(--border-color); color: var(--brand-navy); font-weight: 700; text-decoration: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; display: flex; align-items: center;">
                                👁️ {{ __('Open') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Form -->
            <div id="member-tab-content" style="display: none;">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 6px;">{{ __('Email Address') }}</label>
                        <input type="email" id="invite-member-email" placeholder="colleague@company.com" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 6px;">{{ __('Role') }}</label>
                        <select id="invite-member-role" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button onclick="sendMemberInvite()" id="btn-send-member-invite" style="margin-top: 6px; background: var(--brand-teal); color: white; font-weight: 800; border: none; border-radius: 10px; padding: 12px; cursor: pointer; font-size: 14px;">
                        📨 {{ __('Send Invitation Email') }}
                    </button>
                    <div id="member-invite-status" style="display: none; font-size: 12px; text-align: center; margin-top: 6px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Department Create / Edit -->
    <div id="department-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title" id="department-modal-title">🏛️ {{ __('New Department') }}</h3>
                <button onclick="closeDepartmentModal()" class="modal-close">✕</button>
            </div>
            <form id="department-form" method="POST" action="{{ route('departments.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div id="department-method-field"></div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 6px;">{{ __('Department Name') }}</label>
                    <input type="text" name="name" id="department-name-input" required placeholder="e.g. Engineering & IT, Marketing, Sales" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Save Department') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Team Create -->
    <div id="team-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title" id="team-modal-title">👥 {{ __('Add Sub-Team') }}</h3>
                <button onclick="closeTeamModal()" class="modal-close">✕</button>
            </div>
            <form method="POST" action="{{ route('teams.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <input type="hidden" name="department_id" id="team-department-id">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 6px;">{{ __('Team Name') }}</label>
                    <input type="text" name="name" required placeholder="e.g. Frontend Team, Enterprise Sales, UI/UX Design" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Add Team') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Assign Member to Department / Team / Role -->
    <div id="assign-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title">⚙️ {{ __('Assign Department & Role') }}</h3>
                <button onclick="closeAssignModal()" class="modal-close">✕</button>
            </div>
            <form id="assign-form" method="POST" action="" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Employee / Member') }}</label>
                    <div id="assign-member-name" style="font-size: 14px; font-weight: 800; color: var(--brand-navy); background: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-panel);">
                        Member Name
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Department') }}</label>
                    <select name="department_id" id="assign-dept-select" onchange="filterTeamsForAssign(this.value)" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="">— {{ __('No Department') }} —</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Sub-Team') }}</label>
                    <select name="team_id" id="assign-team-select" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="">— {{ __('No Team') }} —</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Job Title') }}</label>
                    <input type="text" name="job_title" id="assign-job-title" placeholder="e.g. Lead Software Architect, Growth Specialist" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Access Role') }}</label>
                    <select name="role_id" id="assign-role-select" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Save Assignment') }}
                </button>
            </form>
        </div>
    </div>

    <script>
        const ORG_ID = "{{ $organization->id }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const ALL_TEAMS = @json($teams);

        function switchAdminTab(tabName) {
            document.querySelectorAll('.tab-view').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tab-btn').forEach(el => el.classList.remove('active'));

            const targetTab = document.getElementById(`tab-${tabName}`);
            if (targetTab) targetTab.classList.add('active');

            event?.target?.closest('.nav-tab-btn')?.classList.add('active');
        }

        // ── Department Modals ──
        function openDepartmentModal() {
            document.getElementById('department-modal-title').textContent = '🏛️ {{ __('New Department') }}';
            document.getElementById('department-form').action = "{{ route('departments.store') }}";
            document.getElementById('department-method-field').innerHTML = '';
            document.getElementById('department-name-input').value = '';
            document.getElementById('department-modal').style.display = 'flex';
        }

        function editDepartment(id, name) {
            document.getElementById('department-modal-title').textContent = '✏️ {{ __('Edit Department') }}';
            document.getElementById('department-form').action = `/departments/${id}`;
            document.getElementById('department-method-field').innerHTML = '@method("PUT")';
            document.getElementById('department-name-input').value = name;
            document.getElementById('department-modal').style.display = 'flex';
        }

        function closeDepartmentModal() {
            document.getElementById('department-modal').style.display = 'none';
        }

        // ── Team Modals ──
        function openTeamModal(deptId, deptName) {
            document.getElementById('team-modal-title').textContent = `👥 {{ __('Add Sub-Team to') }} ${deptName}`;
            document.getElementById('team-department-id').value = deptId;
            document.getElementById('team-modal').style.display = 'flex';
        }

        function closeTeamModal() {
            document.getElementById('team-modal').style.display = 'none';
        }

        // ── Assign Member Modal ──
        function openAssignModal(memberId, memberName, deptId, teamId, roleId, jobTitle) {
            document.getElementById('assign-form').action = `/members/${memberId}/assign`;
            document.getElementById('assign-member-name').textContent = memberName;
            document.getElementById('assign-dept-select').value = deptId || '';
            filterTeamsForAssign(deptId, teamId);
            document.getElementById('assign-job-title').value = jobTitle || '';
            if (roleId) {
                document.getElementById('assign-role-select').value = roleId;
            }
            document.getElementById('assign-modal').style.display = 'flex';
        }

        function filterTeamsForAssign(deptId, selectedTeamId = '') {
            const teamSelect = document.getElementById('assign-team-select');
            teamSelect.innerHTML = '<option value="">— {{ __('No Team') }} —</option>';
            if (!deptId) return;

            const filtered = ALL_TEAMS.filter(t => t.department_id == deptId);
            filtered.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                if (selectedTeamId && t.id == selectedTeamId) {
                    opt.selected = true;
                }
                teamSelect.appendChild(opt);
            });
        }

        function closeAssignModal() {
            document.getElementById('assign-modal').style.display = 'none';
        }

        function openInviteModal() {
            document.getElementById('invite-modal').style.display = 'flex';
        }

        function closeInviteModal() {
            document.getElementById('invite-modal').style.display = 'none';
        }

        function switchInviteTab(tab) {
            const guestTab = document.getElementById('guest-tab-content');
            const memberTab = document.getElementById('member-tab-content');
            const guestBtn = document.getElementById('tab-guest-btn');
            const memberBtn = document.getElementById('tab-member-btn');

            if (tab === 'guest') {
                guestTab.style.display = 'block';
                memberTab.style.display = 'none';
                guestBtn.style.background = 'var(--accent-primary)';
                guestBtn.style.color = 'white';
                memberBtn.style.background = 'none';
                memberBtn.style.color = '#94a3b8';
            } else {
                guestTab.style.display = 'none';
                memberTab.style.display = 'block';
                memberBtn.style.background = 'var(--accent-primary)';
                memberBtn.style.color = 'white';
                guestBtn.style.background = 'none';
                guestBtn.style.color = '#94a3b8';
            }
        }

        async function generateGuestLink() {
            const roomId = document.getElementById('invite-room-select').value;
            const guestName = document.getElementById('invite-guest-name').value.trim() || 'Guest';
            const hours = parseInt(document.getElementById('invite-guest-hours').value) || 24;

            if (!roomId) {
                alert('Please select or create a destination room first.');
                return;
            }

            const btn = document.getElementById('btn-generate-guest');
            btn.innerHTML = '<span>⏳</span> Generating...';

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/rooms/${roomId}/guest-invitations`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        guest_name: guestName,
                        expires_in_hours: hours
                    })
                });

                if (!res.ok) {
                    const errData = await res.json();
                    alert(errData.message || 'Failed to generate guest link.');
                    return;
                }

                const data = await res.json();
                if (data.join_url) {
                    document.getElementById('guest-link-output').value = data.join_url;
                    document.getElementById('guest-open-link').href = data.join_url;
                    document.getElementById('guest-result-box').style.display = 'block';
                }
            } catch (e) {
                console.error(e);
                alert('Error generating guest link: ' + (e.message || 'Network error'));
            } finally {
                btn.innerHTML = '<span>⚡</span> Generate Instant Guest Link';
            }
        }

        function copyGuestLink() {
            const input = document.getElementById('guest-link-output');
            input.select();
            navigator.clipboard.writeText(input.value);
            const btn = document.getElementById('btn-copy-link');
            btn.textContent = '✅ Copied!';
            setTimeout(() => { btn.textContent = '📋 Copy Link'; }, 2000);
        }

        async function sendMemberInvite() {
            const email = document.getElementById('invite-member-email').value.trim();
            const roleId = document.getElementById('invite-member-role').value;
            const statusBox = document.getElementById('member-invite-status');

            if (!email) {
                alert('Please enter an email address.');
                return;
            }

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/members/invite`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ email, role_id: roleId })
                });

                const data = await res.json();
                statusBox.style.display = 'block';
                statusBox.style.color = '#10b981';
                statusBox.textContent = `✅ ${data.message || 'Invitation sent successfully!'}`;
            } catch (e) {
                statusBox.style.display = 'block';
                statusBox.style.color = '#ef4444';
                statusBox.textContent = '❌ Failed to send invitation.';
            }
        }

        function toggleDashboardSidebar() {
            document.getElementById('dashboardSidebar').classList.toggle('open');
        }
    </script>
</body>
</html>
