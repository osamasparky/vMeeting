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
            /* Modern Digital Workplace OS Palette */
            --bg-primary: #0b0f19;
            --bg-secondary: #111827;
            --bg-card: #111827;
            --bg-elevated: #1e293b;
            --bg-glass: rgba(17, 24, 39, 0.88);
            --border-color: rgba(255, 255, 255, 0.09);
            --border-panel: rgba(255, 255, 255, 0.09);

            --brand-primary: #3b82f6;
            --brand-secondary: #8b5cf6;
            --brand-teal: #06b6d4;
            --brand-pine: #0d9488;
            --brand-ocean: #0284c7;
            --brand-navy: #f8fafc;
            --brand-green: #10b981;
            --brand-lime: #84cc16;
            --brand-gold: #f59e0b;
            --brand-orange: #f97316;
            --brand-coral: #fb7185;
            --brand-crimson: #ef4444;

            --accent-primary: #3b82f6;
            --accent-gradient: linear-gradient(135deg, #3b82f6, #8b5cf6);
            --accent-green: #10b981;
            --accent-amber: #f59e0b;

            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --text-dim: #475569;

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-full: 9999px;
            --shadow-card: 0 4px 20px -2px rgba(0, 0, 0, 0.35);
            --shadow-hover: 0 12px 28px -4px rgba(59, 130, 246, 0.25);

            --font-family: {{ app()->getLocale() === 'ar' ? "'IBM Plex Sans Arabic', 'Cairo', 'Plus Jakarta Sans', sans-serif" : "'Plus Jakarta Sans', 'Inter', 'IBM Plex Sans Arabic', sans-serif" }};
        }

        [data-theme="light"] {
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --bg-elevated: #f1f5f9;
            --bg-glass: rgba(255, 255, 255, 0.92);
            --border-color: #e2e8f0;
            --border-panel: #e2e8f0;
            --brand-navy: #0f172a;
            --text-primary: #0f172a;
            --text-secondary: #334155;
            --text-muted: #64748b;
            --shadow-card: 0 4px 20px -2px rgba(15, 23, 42, 0.06);
            --shadow-hover: 0 12px 28px -4px rgba(59, 130, 246, 0.15);
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
            background: var(--bg-elevated);
            color: var(--text-primary);
        }

        .nav-tab-btn.active {
            background: var(--accent-gradient);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
        }

        .sidebar-user {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: var(--radius-md);
            background: var(--bg-elevated);
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
            color: var(--text-primary);
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
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(59, 130, 246, 0.45);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        .btn-outline {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-outline:hover {
            background: var(--bg-elevated);
            border-color: var(--brand-primary);
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
            color: var(--text-primary);
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
            color: var(--text-primary);
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
            color: var(--text-primary);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }

        .data-table th {
            padding: 12px 14px;
            background: var(--bg-elevated);
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
            color: var(--text-primary);
            background: var(--bg-card);
        }
        .data-table tr:hover td {
            background: var(--bg-elevated);
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
        }

        .badge-green { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-purple { background: rgba(139, 92, 246, 0.15); color: #c084fc; border: 1px solid rgba(139, 92, 246, 0.3); }
        .badge-teal { background: rgba(6, 182, 212, 0.15); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.3); }
        .badge-amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .badge-crimson { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        .badge-gray { background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3); }

        /* ── Live Timer Topbar ── */
        .live-timer-strip {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(139, 92, 246, 0.12));
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: var(--radius-lg);
            padding: 12px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .timer-pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: pulseDot 1.5s infinite;
        }
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.6; }
        }

        /* ── Kanban Board ── */
        .kanban-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
            align-items: start;
        }

        .kanban-column {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 14px;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .kanban-col-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
            font-weight: 800;
        }

        .kanban-card {
            background: var(--bg-elevated);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .kanban-card:hover {
            transform: translateY(-2px);
            border-color: var(--brand-primary);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.2);
        }

        /* ── Progress Bar ── */
        .progress-bar-bg {
            width: 100%;
            height: 8px;
            background: var(--bg-elevated);
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 0.3s ease;
        }

        /* ── Modal Overlay ── */
        .modal, .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .modal-box, .modal-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 28px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
            color: var(--text-primary);
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
            color: var(--text-primary);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
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
            background: var(--bg-elevated);
            border: 1px solid var(--border-color);
            font-size: 20px;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--text-primary);
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

        /* Toast Notification System */
        #toast-container {
            position: fixed;
            bottom: 24px;
            inset-inline-end: 24px;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast-popup {
            pointer-events: auto;
            background: rgba(15, 23, 42, 0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #ffffff;
            border: 1px solid rgba(16, 185, 129, 0.5);
            box-shadow: 0 12px 32px -5px rgba(0, 0, 0, 0.6), 0 0 20px rgba(16, 185, 129, 0.3);
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: toastSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transition: all 0.3s ease;
        }

        .toast-popup.toast-fadeout {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
        }

        @keyframes toastSlideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .btn-copied-pulse {
            animation: copyPulseAnim 0.6s ease;
        }

        @keyframes copyPulseAnim {
            0% { transform: scale(1); }
            50% { transform: scale(1.12); box-shadow: 0 0 15px rgba(16, 185, 129, 0.6); }
            100% { transform: scale(1); }
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
            <div class="sidebar-section-title">{{ __('Project Management') }}</div>
            <button class="nav-tab-btn" onclick="switchAdminTab('projects')">
                <span>📁</span> {{ __('Projects Portfolio') }} ({{ $projects->count() }})
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('all-tasks')">
                <span>📑</span> {{ __('All Tasks Manager') }} ({{ $tasks->count() }})
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('my-tasks')">
                <span>✅</span> {{ __('My Tasks') }} ({{ $myTasks->where('status', '!=', 'done')->count() }})
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('timesheets')">
                <span>⏱️</span> {{ __('Timesheets & Time') }}
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('workload')">
                <span>👥</span> {{ __('Team Workload') }}
            </button>
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

        <div style="padding: 10px; margin-bottom: 10px; display: flex; gap: 8px;">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 7px; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); text-decoration: none; font-size: 12px; font-weight: 700;">🌐 EN</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 7px; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); text-decoration: none; font-size: 12px; font-weight: 700;">🌐 العربية</a>
            @endif
            <button onclick="toggleGlobalTheme()" style="padding: 7px 12px; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); cursor: pointer; font-size: 13px;">
                <span id="theme-icon">🌙</span>
            </button>
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

        <!-- Universal Live Timer Banner Strip -->
        <div id="universal-timer-strip" class="live-timer-strip" style="{{ $activeTimer ? '' : 'display: none;' }}">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div class="timer-pulse-dot"></div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--brand-teal); letter-spacing: 0.5px;">{{ __('Active Timer Running') }}</span>
                        <span id="timer-project-tag" class="badge badge-blue" style="font-size: 10px;">{{ $activeTimer->project->name ?? 'Project' }}</span>
                    </div>
                    <div id="timer-task-title" style="font-size: 14px; font-weight: 800; color: var(--text-primary);">
                        {{ $activeTimer->task->title ?? ($activeTimer->description ?? 'General Work Session') }}
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <div id="live-timer-clock" style="font-size: 22px; font-weight: 900; font-family: monospace; color: #34d399; letter-spacing: 1px;">
                    00:00:00
                </div>
                <button onclick="stopGlobalTimer()" class="header-btn" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); padding: 7px 14px;">
                    ⏹ {{ __('Stop Timer') }}
                </button>
            </div>
        </div>

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
                        <div class="stat-lbl" style="font-size: 12px; font-weight: 800; color: var(--text-secondary);">👥 {{ __('Workplace Presence') }}</div>
                        <span style="font-size: 10px; font-weight: 800; color: #34d399; background: rgba(16, 185, 129, 0.15); padding: 2px 6px; border-radius: 6px;">▲ +14%</span>
                    </div>
                    <div class="stat-val" style="color: var(--brand-teal); font-size: 28px; font-weight: 900; margin-bottom: 4px;">{{ $stats['presence_rate'] }}%</div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ $stats['members'] }} {{ __('Registered team members') }}</div>
                </div>

                <div class="stat-card" style="border-top: 3px solid var(--brand-pine);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div class="stat-lbl" style="font-size: 12px; font-weight: 800; color: var(--text-secondary);">🎙️ {{ __('Meetings & Sessions') }}</div>
                        <span style="font-size: 10px; font-weight: 800; color: var(--brand-teal); background: rgba(6, 182, 212, 0.15); padding: 2px 6px; border-radius: 6px;">{{ $stats['collaboration_hours'] }}h</span>
                    </div>
                    <div class="stat-val" style="color: var(--brand-pine); font-size: 28px; font-weight: 900; margin-bottom: 4px;">{{ $stats['meetings_count'] }}</div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ __('Spatial audio & video sessions') }}</div>
                </div>

                <div class="stat-card" style="border-top: 3px solid var(--brand-orange);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div class="stat-lbl" style="font-size: 12px; font-weight: 800; color: var(--text-secondary);">🏢 {{ __('Room Occupancy') }}</div>
                        <span style="font-size: 10px; font-weight: 800; color: var(--brand-orange); background: rgba(249, 115, 22, 0.15); padding: 2px 6px; border-radius: 6px;">{{ $rooms->count() }} {{ __('Rooms') }}</span>
                    </div>
                    <div class="stat-val" style="color: var(--brand-orange); font-size: 28px; font-weight: 900; margin-bottom: 4px;">{{ $stats['occupancy_rate'] }}%</div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ __('Active collaborative spaces') }}</div>
                </div>

                <div class="stat-card" style="border-top: 3px solid var(--brand-primary);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div class="stat-lbl" style="font-size: 12px; font-weight: 800; color: var(--text-secondary);">💎 {{ __('Company Plan') }}</div>
                        <span style="font-size: 10px; font-weight: 800; color: var(--brand-teal); background: rgba(6, 182, 212, 0.15); padding: 2px 6px; border-radius: 6px;">{{ $stats['guests'] }} {{ __('Guests') }}</span>
                    </div>
                    <div class="stat-val" style="color: var(--text-primary); font-size: 22px; font-weight: 900; margin-bottom: 4px;">{{ $organization->plan->name ?? 'Enterprise' }}</div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ __('Capacity up to') }} {{ $organization->plan->max_users ?? '500' }} {{ __('Users') }}</div>
                </div>
            </div>

            <!-- Productivity Health Score & System Metrics Strip -->
            <div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), var(--bg-card)); border: 1px solid var(--border-color);">
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #3b82f6); color: white; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 900;">
                            ⚡
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 900; color: var(--text-primary);">{{ __('Workplace Health & Productivity Index') }}</div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ __('Real-time collaboration metrics and system uptime') }}</div>
                        </div>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: center;">
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">{{ __('Health Score') }}</div>
                            <div style="font-size: 16px; font-weight: 900; color: #34d399;">{{ $stats['productivity_score'] }} <span style="font-size: 11px; color: var(--text-muted);">/ 100</span></div>
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
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Widget 1: Department Staff Allocation -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        <h3 class="card-title" style="font-size: 14px; font-weight: 800; color: var(--text-primary);">📊 {{ __('Staff Distribution by Department') }}</h3>
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
                                    <span style="color: var(--text-primary);">🏛️ {{ $d->name }}</span>
                                    <span style="color: var(--text-muted);">{{ $dMems->count() }} {{ __('Staff') }} ({{ $pct }}%)</span>
                                </div>
                                <div style="width: 100%; height: 8px; background: var(--bg-elevated); border-radius: 4px; overflow: hidden; border: 1px solid var(--border-color);">
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
                        <h3 class="card-title" style="font-size: 14px; font-weight: 800; color: var(--text-primary);">🏢 {{ __('Room Occupancy & Status') }}</h3>
                        <span style="font-size: 11px; color: var(--brand-green); font-weight: 800;">{{ $rooms->count() }} {{ __('Live Spaces') }}</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @forelse($rooms->take(4) as $r)
                            <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-elevated); border: 1px solid var(--border-color); padding: 8px 12px; border-radius: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 12px; height: 12px; border-radius: 50%; background: {{ $r->color ?? 'var(--brand-teal)' }};"></div>
                                    <div>
                                        <div style="font-size: 13px; font-weight: 800; color: var(--text-primary);">{{ $r->name }}</div>
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
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Quick Actions') }}</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <a href="{{ route('office') }}" style="background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; text-decoration: none; color: var(--text-primary); display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center; transition: all 0.2s;">
                            <span style="font-size: 26px;">🚀</span>
                            <strong style="font-size: 13px; color: var(--text-primary);">{{ __('Virtual Workplace') }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ __('Spatial voice & video') }}</span>
                        </a>
                        <a href="{{ route('editor') }}" style="background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; text-decoration: none; color: var(--text-primary); display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center; transition: all 0.2s;">
                            <span style="font-size: 26px;">🎨</span>
                            <strong style="font-size: 13px; color: var(--text-primary);">{{ __('Floor Designer') }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ __('Furniture & partitions') }}</span>
                        </a>
                        <div onclick="openInviteModal()" style="background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; color: var(--text-primary); display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <span style="font-size: 26px;">🔗</span>
                            <strong style="font-size: 13px; color: var(--text-primary);">{{ __('Instant Guest Link') }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ __('No login needed') }}</span>
                        </div>
                        <div onclick="switchAdminTab('rooms')" style="background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; color: var(--text-primary); display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <span style="font-size: 26px;">🚪</span>
                            <strong style="font-size: 13px; color: var(--text-primary);">{{ __('Manage Room Doors') }}</strong>
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
                                    <strong style="color: var(--text-primary);">{{ $log->action }}</strong>
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
                                            <strong style="color: var(--text-primary); font-size: 13px;">{{ $m->user->name }}</strong>
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
                                <td style="font-size: 12px; font-weight: 600; color: var(--text-secondary);">
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
            <div class="card" style="margin-bottom: 28px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), var(--bg-card)); border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <span style="font-size: 11px; font-weight: 800; color: var(--brand-teal); text-transform: uppercase; letter-spacing: 0.5px;">{{ __('Current Plan') }}</span>
                        <h2 style="font-size: 24px; font-weight: 900; color: var(--text-primary); margin-top: 4px;">💎 {{ $currentPlan->name ?? __('Free Plan') }}</h2>
                        <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                            ${{ number_format($currentPlan->price ?? 0, 2) }}/month &bull; {{ $isUnlimited ? __('Unlimited Seats') : $seatLimit . ' ' . __('Total Seats') }}
                        </p>
                    </div>
                    <div style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">
                        <div style="font-size: 24px; font-weight: 900; color: #34d399;">
                            {{ $usedSeats }} / {{ $isUnlimited ? '∞' : $seatLimit }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">{{ __('Seats used') }}</div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div style="background: var(--bg-elevated); height: 8px; border-radius: 4px; overflow: hidden; margin-bottom: 16px; border: 1px solid var(--border-color);">
                    <div style="width: {{ $seatPercent }}%; background: {{ $seatPercent > 90 ? 'var(--brand-crimson)' : 'var(--brand-teal)' }}; height: 100%; border-radius: 4px;"></div>
                </div>
            </div>

            <!-- Available Upgrade Plans Grid -->
            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 16px; color: var(--text-primary);">{{ __('Available Subscription Plans') }}</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
                @foreach($allPlans as $p)
                @php
                    $isCurrent = ($organization->plan_id == $p->id);
                @endphp
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-color: {{ $isCurrent ? 'var(--brand-primary)' : 'var(--border-color)' }}; background: {{ $isCurrent ? 'rgba(59, 130, 246, 0.1)' : 'var(--bg-card)' }}; box-shadow: var(--shadow-card);">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="font-size: 16px; color: var(--text-primary);">💎 {{ $p->name }}</strong>
                            @if($isCurrent)
                                <span class="badge badge-green">{{ __('Current') }}</span>
                            @endif
                        </div>

                        <div style="font-size: 24px; font-weight: 900; color: var(--text-primary); margin-bottom: 14px;">
                            ${{ number_format($p->price, 2) }}
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);">/mo</span>
                        </div>

                        <div style="font-size: 13px; color: var(--text-secondary); display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px;">
                            <div>👥 <strong style="color: var(--text-primary);">{{ $p->seat_limit === 0 ? __('Unlimited') : $p->seat_limit }}</strong> {{ __('Users / Seats') }}</div>
                            <div>🚪 <strong style="color: var(--text-primary);">{{ $p->room_limit === 0 ? __('Unlimited') : $p->room_limit }}</strong> {{ __('Meeting Rooms') }}</div>
                            <div>💾 <strong style="color: var(--text-primary);">{{ $p->storage_limit_gb === 0 ? __('Unlimited') : $p->storage_limit_gb . ' GB' }}</strong> {{ __('Storage') }}</div>
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
                                    <strong style="color: var(--text-primary);">🏢 {{ $r->name }}</strong>
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
                <div style="display: flex; gap: 10px; align-items: center;">
                    @if($guestInvitations->count() > 0)
                        <form method="POST" action="{{ route('guest_invitations.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to delete all guest meeting links?') }}');" style="display: inline;">
                            @csrf
                            <button type="submit" class="header-btn" style="background: #ef4444; color: white; border: none; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                                <span>🗑️</span> {{ __('Clear All Links') }}
                            </button>
                        </form>
                    @endif
                    <button onclick="openInviteModal()" class="header-btn btn-success">
                        <span>⚡</span> {{ __('Create Guest Link') }}
                    </button>
                </div>
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
                                    <strong style="color: #34d399;">👤 {{ $inv->guest_name }}</strong>
                                </td>
                                <td>🏢 {{ $inv->room->name ?? 'Main Conference' }}</td>
                                <td>{{ $inv->expires_at ? $inv->expires_at->diffForHumans() : __('Never') }}</td>
                                <td>
                                    <code style="background: var(--bg-elevated); border: 1px solid var(--border-color); padding: 4px 8px; border-radius: 4px; font-size: 11px; color: var(--brand-teal);">
                                        /guest/join/{{ substr($inv->token, 0, 16) }}...
                                    </code>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <button type="button" onclick="copyTableGuestLink('{{ url('/guest/join/' . $inv->token) }}', this)" class="header-btn btn-primary" style="padding: 4px 10px; font-size: 11px; cursor: pointer;">
                                            📋 {{ __('Copy Link') }}
                                        </button>
                                        <a href="{{ url('/guest/join/' . $inv->token) }}" target="_blank" class="header-btn btn-outline" style="padding: 4px 10px; font-size: 11px; text-decoration: none;">
                                            👁️ {{ __('Open') }}
                                        </a>
                                    </div>
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
                    <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--border-color); box-shadow: var(--shadow-card);">
                        <div>
                            <!-- Department Header -->
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); color: var(--brand-teal); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                        🏛️
                                    </div>
                                    <div>
                                        <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 2px;">{{ $dept->name }}</h3>
                                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 700;">{{ $dept->teams->count() }} {{ __('Teams') }} • {{ $deptMembers->count() }} {{ __('Members') }}</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button onclick="editDepartment('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" style="background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 6px; padding: 5px 8px; cursor: pointer; color: var(--text-primary);" title="{{ __('Edit Department') }}">✏️</button>
                                    <form action="{{ route('departments.delete', $dept->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this department?') }}');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 6px; padding: 5px 8px; cursor: pointer; color: var(--brand-crimson);" title="{{ __('Delete Department') }}">🗑️</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Sub-Teams Section -->
                            <div style="background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px; margin-bottom: 14px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                    <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase;">{{ __('Sub-Teams') }}</span>
                                    <button onclick="openTeamModal('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" style="background: none; border: none; font-size: 11px; font-weight: 800; color: var(--brand-teal); cursor: pointer;">+ {{ __('Add Team') }}</button>
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    @forelse($dept->teams as $t)
                                        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                                            <span>👥 {{ $t->name }}</span>
                                            <form action="{{ route('teams.delete', $t->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this team?') }}');" style="display: inline; margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 11px; padding: 0; line-height: 1;" title="{{ __('Delete Team') }}">✕</button>
                                            </form>
                                        </div>
                                    @empty
                                        <span style="font-size: 11px; color: var(--text-dim); font-style: italic;">{{ __('No sub-teams created yet.') }}</span>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Assigned Department Members -->
                            <div>
                                <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; display: block; margin-bottom: 8px;">{{ __('Assigned Staff') }} ({{ $deptMembers->count() }})</span>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    @forelse($deptMembers->take(4) as $dm)
                                        @php
                                            $prof = $dm->user->profiles->where('organization_id', $organization->id)->first();
                                            $tObj = $teams->where('id', $prof?->team_id)->first();
                                        @endphp
                                        <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-card); border: 1px solid var(--border-color); padding: 6px 10px; border-radius: 8px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="width: 24px; height: 24px; border-radius: 6px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800;">
                                                    {{ strtoupper(substr($dm->user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <span style="font-size: 12px; font-weight: 800; color: var(--text-primary);">{{ $dm->user->name }}</span>
                                                    @if($prof?->job_title)
                                                        <span style="font-size: 10px; color: var(--text-muted);"> • {{ $prof->job_title }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($tObj)
                                                <span style="font-size: 10px; background: rgba(59, 130, 246, 0.15); color: var(--brand-teal); font-weight: 700; padding: 2px 6px; border-radius: 4px;">{{ $tObj->name }}</span>
                                            @endif
                                        </div>
                                    @empty
                                        <div style="text-align: center; padding: 10px; font-size: 11px; color: var(--text-dim); background: var(--bg-card); border: 1px dashed var(--border-color); border-radius: 8px;">
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
                        <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">{{ __('No departments found') }}</h3>
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
                <div>
                    @if($auditLogs->count() > 0)
                        <form method="POST" action="{{ route('audit_logs.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to purge all audit logs?') }}');" style="display: inline;">
                            @csrf
                            <button type="submit" class="header-btn" style="background: #ef4444; color: white; border: none; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                                <span>🗑️</span> {{ __('Clear All Logs') }}
                            </button>
                        </form>
                    @endif
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
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Workspace Name') }}</label>
                        <input type="text" value="{{ $organization->name }}" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); font-size: 13px; font-weight: 600;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('URL Slug') }}</label>
                        <input type="text" value="{{ $organization->slug }}" readonly style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--brand-teal); font-size: 13px; font-family: monospace; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Timezone') }}</label>
                        <input type="text" value="{{ $organization->timezone }}" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); font-size: 13px; font-weight: 600;">
                    </div>
                    <button class="header-btn btn-primary" style="margin-top: 10px; width: fit-content;">{{ __('Save Workspace Changes') }}</button>
                </div>
            </div>
        </div>

        <!-- 8. PROJECTS PORTFOLIO TAB -->
        <div id="tab-projects" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">📁 {{ __('Projects Portfolio') }}</h1>
                    <p class="page-subtitle">{{ __('Manage company initiatives, milestones, tasks, and budgets.') }}</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="openNewProjectModal()" class="header-btn btn-primary">
                        <span>+</span> {{ __('New Project') }}
                    </button>
                </div>
            </div>

            <!-- Project KPI Metrics -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div class="stat-card" style="border-top: 3px solid var(--brand-primary);">
                    <div class="stat-lbl">📁 {{ __('Total Projects') }}</div>
                    <div class="stat-val" style="color: var(--brand-primary);">{{ $projects->count() }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ $projects->where('status', 'active')->count() }} {{ __('Active initiatives') }}</div>
                </div>
                <div class="stat-card" style="border-top: 3px solid var(--brand-teal);">
                    <div class="stat-lbl">✅ {{ __('Total Tasks') }}</div>
                    <div class="stat-val" style="color: var(--brand-teal);">{{ $tasks->count() }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ $tasks->where('status', '!=', 'done')->count() }} {{ __('In progress / Backlog') }}</div>
                </div>
                <div class="stat-card" style="border-top: 3px solid var(--brand-pine);">
                    <div class="stat-lbl">⏱️ {{ __('Logged Hours') }}</div>
                    <div class="stat-val" style="color: var(--brand-pine);">{{ round($projects->sum(fn($p) => $p->actualHours()), 1) }}h</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ __('Tracked across all tasks') }}</div>
                </div>
                <div class="stat-card" style="border-top: 3px solid var(--brand-gold);">
                    <div class="stat-lbl">💰 {{ __('Total Budget') }}</div>
                    <div class="stat-val" style="color: var(--brand-gold);">${{ number_format($projects->sum('budget_amount'), 0) }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ __('Allocated capital') }}</div>
                </div>
            </div>

            <!-- Projects Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📋 {{ __('Active Initiatives') }} ({{ $projects->count() }})</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Project Name') }}</th>
                                <th>{{ __('Manager') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Progress') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Budget') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $p)
                                <tr onclick="openProjectHub('{{ $p->id }}')" style="cursor: pointer;" title="{{ __('Click to open project dashboard & tasks') }}">
                                    <td><span class="badge badge-blue" style="font-family: monospace;">{{ $p->code ?? 'PRJ' }}</span></td>
                                    <td>
                                        <div style="font-weight: 800; color: var(--text-primary);">{{ $p->name }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($p->description, 50) }}</div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 24px; height: 24px; border-radius: 6px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800;">
                                                {{ strtoupper(substr($p->manager->name ?? 'NA', 0, 2)) }}
                                            </div>
                                            <span>{{ $p->manager->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($p->status === 'active')
                                            <span class="badge badge-green">{{ __('Active') }}</span>
                                        @elseif($p->status === 'completed')
                                            <span class="badge badge-teal">{{ __('Completed') }}</span>
                                        @elseif($p->status === 'on_hold')
                                            <span class="badge badge-amber">{{ __('On Hold') }}</span>
                                        @else
                                            <span class="badge badge-gray">{{ ucfirst($p->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->priority === 'urgent')
                                            <span class="badge badge-crimson">🔥 {{ __('Urgent') }}</span>
                                        @elseif($p->priority === 'high')
                                            <span class="badge badge-amber">⚡ {{ __('High') }}</span>
                                        @else
                                            <span class="badge badge-gray">{{ ucfirst($p->priority) }}</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 140px;">
                                        @php $pct = $p->progressPercentage(); @endphp
                                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px; font-weight: 700;">
                                            <span>{{ $pct }}%</span>
                                            <span style="color: var(--text-muted);">{{ $p->tasks_count }} {{ __('tasks') }}</span>
                                        </div>
                                        <div class="progress-bar-bg">
                                            <div class="progress-bar-fill" style="width: {{ $pct }}%; background: {{ $pct === 100 ? '#10b981' : 'var(--brand-primary)' }};"></div>
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; font-weight: 600;">{{ $p->due_date ? $p->due_date->format('M d, Y') : '—' }}</td>
                                    <td style="font-weight: 700; color: var(--brand-teal);">${{ number_format($p->budget_amount ?? 0, 0) }}</td>
                                    <td>
                                        <button onclick="event.stopPropagation(); openProjectHub('{{ $p->id }}');" class="header-btn btn-primary" style="padding: 4px 10px; font-size: 11px;">
                                            📊 {{ __('Open Hub') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        📁 {{ __('No projects created yet. Click "+ New Project" to get started.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 9. ALL TASKS MANAGER TAB (Project Manager View) -->
        <div id="tab-all-tasks" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">📑 {{ __('All Tasks & Work Orders') }}</h1>
                    <p class="page-subtitle">{{ __('Workspace-wide task tracking, workload distribution, and Kanban workflow control.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <div style="display: flex; gap: 4px; background: var(--bg-elevated); padding: 3px; border-radius: 8px; border: 1px solid var(--border-color);">
                        <button onclick="switchAllTasksView('table')" id="alltasks-btn-table" class="header-btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
                            📋 {{ __('Table View') }}
                        </button>
                        <button onclick="switchAllTasksView('kanban')" id="alltasks-btn-kanban" class="header-btn btn-outline" style="padding: 6px 12px; font-size: 12px;">
                            📌 {{ __('Kanban Board') }}
                        </button>
                    </div>
                    <button onclick="openNewTaskModal()" class="header-btn btn-primary">
                        <span>+</span> {{ __('New Task') }}
                    </button>
                </div>
            </div>

            <!-- Task KPIs Summary -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px;">
                <div class="stat-card" style="padding: 14px; border-top: 3px solid var(--brand-primary);">
                    <div class="stat-lbl">📑 {{ __('Total Tasks') }}</div>
                    <div class="stat-val" style="font-size: 22px; color: var(--brand-primary);">{{ $tasks->count() }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ __('Across all active projects') }}</div>
                </div>
                <div class="stat-card" style="padding: 14px; border-top: 3px solid var(--brand-teal);">
                    <div class="stat-lbl">⚡ {{ __('In Progress') }}</div>
                    <div class="stat-val" style="font-size: 22px; color: var(--brand-teal);">{{ $tasks->where('status', 'in_progress')->count() }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ __('Active work execution') }}</div>
                </div>
                <div class="stat-card" style="padding: 14px; border-top: 3px solid var(--brand-gold);">
                    <div class="stat-lbl">🔍 {{ __('Under Review / QA') }}</div>
                    <div class="stat-val" style="font-size: 22px; color: var(--brand-gold);">{{ $tasks->whereIn('status', ['review', 'qa'])->count() }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ __('Pending approval') }}</div>
                </div>
                <div class="stat-card" style="padding: 14px; border-top: 3px solid #10b981;">
                    <div class="stat-lbl">🎉 {{ __('Completed') }}</div>
                    <div class="stat-val" style="font-size: 22px; color: #10b981;">{{ $tasks->where('status', 'done')->count() }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ __('Delivered features & fixes') }}</div>
                </div>
                <div class="stat-card" style="padding: 14px; border-top: 3px solid var(--brand-crimson);">
                    <div class="stat-lbl">⏱️ {{ __('Estimated vs Actual') }}</div>
                    <div class="stat-val" style="font-size: 22px; color: var(--text-primary); font-family: monospace;">{{ $tasks->sum('estimated_hours') }}h / {{ round($projects->sum(fn($p) => $p->actualHours()), 1) }}h</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ __('Total tracked effort') }}</div>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="card" style="padding: 16px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: center;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">🔍 {{ __('Search Tasks') }}</label>
                        <input type="text" id="alltasks-filter-search" oninput="filterAllTasksTable()" placeholder="Task title or #..." style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 10px; color: var(--text-primary); outline: none; font-size: 12px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">📁 {{ __('Project') }}</label>
                        <select id="alltasks-filter-project" onchange="filterAllTasksTable()" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 10px; color: var(--text-primary); outline: none; font-size: 12px;">
                            <option value="">— {{ __('All Projects') }} —</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">⚡ {{ __('Status') }}</label>
                        <select id="alltasks-filter-status" onchange="filterAllTasksTable()" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 10px; color: var(--text-primary); outline: none; font-size: 12px;">
                            <option value="">— {{ __('All Statuses') }} —</option>
                            <option value="backlog">📌 Backlog</option>
                            <option value="ready">🎯 Ready</option>
                            <option value="in_progress">⚡ In Progress</option>
                            <option value="review">🔍 Review / QA</option>
                            <option value="done">🎉 Done</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">⚡ {{ __('Priority') }}</label>
                        <select id="alltasks-filter-priority" onchange="filterAllTasksTable()" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 10px; color: var(--text-primary); outline: none; font-size: 12px;">
                            <option value="">— {{ __('All Priorities') }} —</option>
                            <option value="urgent">🔥 Urgent</option>
                            <option value="high">⚡ High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">👤 {{ __('Assignee') }}</label>
                        <select id="alltasks-filter-assignee" onchange="filterAllTasksTable()" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 10px; color: var(--text-primary); outline: none; font-size: 12px;">
                            <option value="">— {{ __('All Members') }} —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- View 1: Tasks Table / List -->
            <div id="alltasks-view-table" class="card" style="display: block;">
                <div class="card-header">
                    <h3 class="card-title">📋 {{ __('All Organization Tasks') }} (<span id="alltasks-filtered-count">{{ $tasks->count() }}</span>)</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Task Title') }}</th>
                                <th>{{ __('Project') }}</th>
                                <th>{{ __('Assignee') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Estimated / Actual') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="alltasks-table-body">
                            @forelse($tasks as $t)
                                <tr class="alltask-row" 
                                    data-id="{{ $t->id }}"
                                    data-title="{{ strtolower($t->title) }}"
                                    data-project-id="{{ $t->project_id }}"
                                    data-status="{{ $t->status }}"
                                    data-priority="{{ $t->priority }}"
                                    data-assignee-id="{{ $t->assignee_id }}"
                                    onclick="openTaskDetails('{{ $t->id }}')" 
                                    style="cursor: pointer;">
                                    <td><span class="badge badge-blue" style="font-family: monospace;">#{{ $t->task_number ?? 1 }}</span></td>
                                    <td>
                                        <div style="font-weight: 800; color: var(--text-primary);">{{ $t->title }}</div>
                                        @if($t->description)
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($t->description, 45) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-gray" style="font-weight: 700;">📁 {{ $t->project->name ?? 'General' }}</span>
                                    </td>
                                    <td>
                                        @if($t->assignee)
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div style="width: 22px; height: 22px; border-radius: 6px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800;">
                                                    {{ strtoupper(substr($t->assignee->name, 0, 2)) }}
                                                </div>
                                                <span style="font-weight: 600;">{{ $t->assignee->name }}</span>
                                            </div>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 11px;">— {{ __('Unassigned') }} —</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select onchange="event.stopPropagation(); updateTaskStatusDirect('${t.id}', this.value)" style="background: var(--bg-elevated); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 11px; font-weight: 700; border-radius: 6px; padding: 3px 6px;">
                                            <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>📌 Backlog</option>
                                            <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>🎯 Ready</option>
                                            <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                                            <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>🔍 Review</option>
                                            <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>🎉 Done</option>
                                        </select>
                                    </td>
                                    <td>
                                        @if($t->priority === 'urgent')
                                            <span class="badge badge-crimson">🔥 {{ __('Urgent') }}</span>
                                        @elseif($t->priority === 'high')
                                            <span class="badge badge-amber">⚡ {{ __('High') }}</span>
                                        @else
                                            <span class="badge badge-gray">{{ ucfirst($t->priority) }}</span>
                                        @endif
                                    </td>
                                    <td style="font-family: monospace; font-weight: 700;">
                                        {{ $t->estimated_hours ?? 0 }}h / {{ $t->actualHours() }}h
                                    </td>
                                    <td>
                                        @php
                                            $isOverdue = $t->due_date && $t->due_date->isPast() && $t->status !== 'done';
                                        @endphp
                                        <span style="font-size: 12px; font-weight: 700; color: {{ $isOverdue ? '#ef4444' : 'var(--text-secondary)' }};">
                                            {{ $t->due_date ? $t->due_date->format('M d, Y') : '—' }}
                                            @if($isOverdue) <span class="badge badge-crimson" style="font-size: 9px;">Overdue</span> @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px;" onclick="event.stopPropagation();">
                                            <button onclick="startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="header-btn" style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 3px 8px; font-size: 11px;">
                                                ▶ {{ __('Timer') }}
                                            </button>
                                            <button onclick="openTaskDetails('{{ $t->id }}')" class="header-btn btn-outline" style="padding: 3px 8px; font-size: 11px;">
                                                🔍 {{ __('Inspect') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        📑 {{ __('No tasks created yet. Click "+ New Task" to create one.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View 2: Global Kanban Board -->
            <div id="alltasks-view-kanban" style="display: none;">
                <div class="kanban-grid" style="grid-template-columns: repeat(5, minmax(220px, 1fr));">
                    <!-- Backlog -->
                    <div class="kanban-column">
                        <div class="kanban-col-header" style="color: var(--text-secondary);">
                            <span>📌 Backlog</span>
                            <span class="badge badge-gray">{{ $tasks->where('status', 'backlog')->count() }}</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($tasks->where('status', 'backlog') as $t)
                                <div class="kanban-card" onclick="openTaskDetails('{{ $t->id }}')">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <span class="badge badge-blue" style="font-size: 9px;">#{{ $t->task_number }}</span>
                                        <span class="badge {{ $t->priority === 'urgent' ? 'badge-crimson' : ($t->priority === 'high' ? 'badge-amber' : 'badge-gray') }}" style="font-size: 9px;">{{ $t->priority }}</span>
                                    </div>
                                    <div style="font-weight: 800; font-size: 13px; color: var(--text-primary); margin-bottom: 4px;">{{ $t->title }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">📁 {{ $t->project->name ?? 'General' }}</div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 6px; font-size: 11px;">
                                        <span>👤 {{ $t->assignee ? explode(' ', $t->assignee->name)[0] : 'Unassigned' }}</span>
                                        <button onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="header-btn" style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 2px 6px; font-size: 10px;">▶</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- Ready -->
                    <div class="kanban-column">
                        <div class="kanban-col-header" style="color: #60a5fa;">
                            <span>🎯 Ready</span>
                            <span class="badge badge-blue">{{ $tasks->where('status', 'ready')->count() }}</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($tasks->where('status', 'ready') as $t)
                                <div class="kanban-card" onclick="openTaskDetails('{{ $t->id }}')">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <span class="badge badge-blue" style="font-size: 9px;">#{{ $t->task_number }}</span>
                                        <span class="badge {{ $t->priority === 'urgent' ? 'badge-crimson' : ($t->priority === 'high' ? 'badge-amber' : 'badge-gray') }}" style="font-size: 9px;">{{ $t->priority }}</span>
                                    </div>
                                    <div style="font-weight: 800; font-size: 13px; color: var(--text-primary); margin-bottom: 4px;">{{ $t->title }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">📁 {{ $t->project->name ?? 'General' }}</div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 6px; font-size: 11px;">
                                        <span>👤 {{ $t->assignee ? explode(' ', $t->assignee->name)[0] : 'Unassigned' }}</span>
                                        <button onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="header-btn" style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 2px 6px; font-size: 10px;">▶</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- In Progress -->
                    <div class="kanban-column">
                        <div class="kanban-col-header" style="color: #22d3ee;">
                            <span>⚡ In Progress</span>
                            <span class="badge badge-teal">{{ $tasks->where('status', 'in_progress')->count() }}</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($tasks->where('status', 'in_progress') as $t)
                                <div class="kanban-card" onclick="openTaskDetails('{{ $t->id }}')">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <span class="badge badge-blue" style="font-size: 9px;">#{{ $t->task_number }}</span>
                                        <span class="badge {{ $t->priority === 'urgent' ? 'badge-crimson' : ($t->priority === 'high' ? 'badge-amber' : 'badge-gray') }}" style="font-size: 9px;">{{ $t->priority }}</span>
                                    </div>
                                    <div style="font-weight: 800; font-size: 13px; color: var(--text-primary); margin-bottom: 4px;">{{ $t->title }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">📁 {{ $t->project->name ?? 'General' }}</div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 6px; font-size: 11px;">
                                        <span>👤 {{ $t->assignee ? explode(' ', $t->assignee->name)[0] : 'Unassigned' }}</span>
                                        <button onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="header-btn" style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 2px 6px; font-size: 10px;">▶</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- Review / QA -->
                    <div class="kanban-column">
                        <div class="kanban-col-header" style="color: #fbbf24;">
                            <span>🔍 Review / QA</span>
                            <span class="badge badge-amber">{{ $tasks->whereIn('status', ['review', 'qa'])->count() }}</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($tasks->whereIn('status', ['review', 'qa']) as $t)
                                <div class="kanban-card" onclick="openTaskDetails('{{ $t->id }}')">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <span class="badge badge-blue" style="font-size: 9px;">#{{ $t->task_number }}</span>
                                        <span class="badge {{ $t->priority === 'urgent' ? 'badge-crimson' : ($t->priority === 'high' ? 'badge-amber' : 'badge-gray') }}" style="font-size: 9px;">{{ $t->priority }}</span>
                                    </div>
                                    <div style="font-weight: 800; font-size: 13px; color: var(--text-primary); margin-bottom: 4px;">{{ $t->title }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">📁 {{ $t->project->name ?? 'General' }}</div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 6px; font-size: 11px;">
                                        <span>👤 {{ $t->assignee ? explode(' ', $t->assignee->name)[0] : 'Unassigned' }}</span>
                                        <button onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="header-btn" style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 2px 6px; font-size: 10px;">▶</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- Done -->
                    <div class="kanban-column">
                        <div class="kanban-col-header" style="color: #34d399;">
                            <span>🎉 Done</span>
                            <span class="badge badge-green">{{ $tasks->where('status', 'done')->count() }}</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($tasks->where('status', 'done') as $t)
                                <div class="kanban-card" onclick="openTaskDetails('{{ $t->id }}')" style="opacity: 0.85;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <span class="badge badge-blue" style="font-size: 9px;">#{{ $t->task_number }}</span>
                                        <span class="badge badge-green" style="font-size: 9px;">Done</span>
                                    </div>
                                    <div style="font-weight: 800; font-size: 13px; color: var(--text-primary); margin-bottom: 4px;">{{ $t->title }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted);">📁 {{ $t->project->name ?? 'General' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 10. MY TASKS TAB -->
        <div id="tab-my-tasks" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">✅ {{ __('My Tasks & Action Items') }}</h1>
                    <p class="page-subtitle">{{ __('Track and log time against your personal assigned tasks.') }}</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="openNewTaskModal()" class="header-btn btn-primary">
                        <span>+</span> {{ __('New Task') }}
                    </button>
                </div>
            </div>

            <!-- Task Status Columns Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                <!-- Due Today / In Progress -->
                <div class="card">
                    <div class="card-header" style="border-bottom: 2px solid var(--brand-teal); padding-bottom: 10px;">
                        <h3 class="card-title" style="font-size: 14px;">⚡ {{ __('In Progress & Active') }}</h3>
                        <span class="badge badge-teal">{{ $myTasks->where('status', 'in_progress')->count() }}</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 12px;">
                        @forelse($myTasks->where('status', 'in_progress') as $t)
                            <div class="kanban-card">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                    <span class="badge badge-blue" style="font-size: 10px;">{{ $t->project->code ?? 'PRJ' }}-{{ $t->task_number }}</span>
                                    <span class="badge {{ $t->priority === 'urgent' ? 'badge-crimson' : ($t->priority === 'high' ? 'badge-amber' : 'badge-gray') }}" style="font-size: 10px;">{{ ucfirst($t->priority) }}</span>
                                </div>
                                <div style="font-weight: 800; font-size: 14px; margin-bottom: 4px; color: var(--text-primary);">{{ $t->title }}</div>
                                <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 10px;">📁 {{ $t->project->name }}</div>
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 10px;">
                                    <span style="font-size: 11px; font-weight: 600; color: var(--text-secondary);">📅 {{ $t->due_date ? $t->due_date->format('M d') : 'No date' }}</span>
                                    <button onclick="startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name) }}')" class="header-btn" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 4px 10px; font-size: 11px;">
                                        ▶ {{ __('Start Timer') }}
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 12px;">
                                {{ __('No tasks currently in progress.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Backlog / Ready -->
                <div class="card">
                    <div class="card-header" style="border-bottom: 2px solid var(--brand-primary); padding-bottom: 10px;">
                        <h3 class="card-title" style="font-size: 14px;">📌 {{ __('Ready & Backlog') }}</h3>
                        <span class="badge badge-blue">{{ $myTasks->whereIn('status', ['backlog', 'ready'])->count() }}</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 12px;">
                        @forelse($myTasks->whereIn('status', ['backlog', 'ready']) as $t)
                            <div class="kanban-card">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                    <span class="badge badge-blue" style="font-size: 10px;">{{ $t->project->code ?? 'PRJ' }}-{{ $t->task_number }}</span>
                                    <span class="badge {{ $t->priority === 'urgent' ? 'badge-crimson' : ($t->priority === 'high' ? 'badge-amber' : 'badge-gray') }}" style="font-size: 10px;">{{ ucfirst($t->priority) }}</span>
                                </div>
                                <div style="font-weight: 800; font-size: 14px; margin-bottom: 4px; color: var(--text-primary);">{{ $t->title }}</div>
                                <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 10px;">📁 {{ $t->project->name }}</div>
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 10px;">
                                    <span style="font-size: 11px; font-weight: 600; color: var(--text-secondary);">📅 {{ $t->due_date ? $t->due_date->format('M d') : 'No date' }}</span>
                                    <button onclick="startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name) }}')" class="header-btn btn-outline" style="padding: 4px 10px; font-size: 11px;">
                                        ▶ {{ __('Start Timer') }}
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 12px;">
                                {{ __('No pending tasks.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Review / QA / Done -->
                <div class="card">
                    <div class="card-header" style="border-bottom: 2px solid #10b981; padding-bottom: 10px;">
                        <h3 class="card-title" style="font-size: 14px;">🎉 {{ __('Completed & Under Review') }}</h3>
                        <span class="badge badge-green">{{ $myTasks->whereIn('status', ['review', 'qa', 'done'])->count() }}</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 12px;">
                        @forelse($myTasks->whereIn('status', ['review', 'qa', 'done']) as $t)
                            <div class="kanban-card" style="opacity: 0.85;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                    <span class="badge badge-blue" style="font-size: 10px;">{{ $t->project->code ?? 'PRJ' }}-{{ $t->task_number }}</span>
                                    <span class="badge badge-green" style="font-size: 10px;">{{ ucfirst($t->status) }}</span>
                                </div>
                                <div style="font-weight: 800; font-size: 14px; margin-bottom: 4px; color: var(--text-primary);">{{ $t->title }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">📁 {{ $t->project->name }}</div>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 12px;">
                                {{ __('No completed tasks yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- 10. TIMESHEETS & TIME TRACKING TAB -->
        <div id="tab-timesheets" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">⏱️ {{ __('Timesheets & Time Tracking') }}</h1>
                    <p class="page-subtitle">{{ __('Log working hours, view weekly timesheets, and review employee submissions.') }}</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="openManualTimeModal()" class="header-btn btn-outline">
                        <span>✍️</span> {{ __('Manual Time Entry') }}
                    </button>
                    <button onclick="submitMyCurrentTimesheet()" class="header-btn btn-primary">
                        <span>📤</span> {{ __('Submit Weekly Timesheet') }}
                    </button>
                </div>
            </div>

            <!-- Recent Time Entries -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🕒 {{ __('My Recent Time Log') }}</h3>
                    <span style="font-size: 12px; font-weight: 700; color: var(--brand-teal);">
                        {{ round($recentTimeEntries->sum('duration_seconds') / 3600, 1) }} {{ __('Hours logged recently') }}
                    </span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Project') }}</th>
                                <th>{{ __('Task') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTimeEntries as $te)
                                <tr>
                                    <td style="font-weight: 600;">{{ $te->started_at->format('M d, Y') }}</td>
                                    <td><span class="badge badge-blue">{{ $te->project->name ?? 'General' }}</span></td>
                                    <td style="font-weight: 700; color: var(--text-primary);">{{ $te->task->title ?? '—' }}</td>
                                    <td style="color: var(--text-secondary); font-size: 12px;">{{ $te->description ?? 'Work session' }}</td>
                                    <td style="font-weight: 800; color: #34d399; font-family: monospace; font-size: 14px;">{{ $te->hours() }}h</td>
                                    <td><span class="badge badge-gray">{{ ucfirst($te->entry_type) }}</span></td>
                                    <td>
                                        @if($te->status === 'approved')
                                            <span class="badge badge-green">🔒 {{ __('Approved') }}</span>
                                        @elseif($te->status === 'submitted')
                                            <span class="badge badge-amber">⏳ {{ __('Submitted') }}</span>
                                        @elseif($te->status === 'rejected')
                                            <span class="badge badge-crimson">❌ {{ __('Rejected') }}</span>
                                        @else
                                            <span class="badge badge-gray">{{ __('Draft') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                        ⏱️ {{ __('No time entries logged yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Manager Timesheet Review Queue -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📋 {{ __('Timesheet Submissions Review Queue') }}</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Period') }}</th>
                                <th>{{ __('Total Hours') }}</th>
                                <th>{{ __('Billable') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allTimesheets as $ts)
                                <tr>
                                    <td style="font-weight: 800; color: var(--text-primary);">{{ $ts->user->name ?? 'Member' }}</td>
                                    <td>{{ $ts->period_start->format('M d') }} — {{ $ts->period_end->format('M d, Y') }}</td>
                                    <td style="font-weight: 900; color: var(--text-primary); font-family: monospace;">{{ $ts->total_hours }}h</td>
                                    <td style="color: var(--brand-teal); font-weight: 800; font-family: monospace;">{{ $ts->billable_hours }}h</td>
                                    <td>
                                        @if($ts->status === 'approved')
                                            <span class="badge badge-green">✅ {{ __('Approved') }}</span>
                                        @elseif($ts->status === 'submitted')
                                            <span class="badge badge-amber">⏳ {{ __('Pending Review') }}</span>
                                        @elseif($ts->status === 'rejected')
                                            <span class="badge badge-crimson">❌ {{ __('Rejected') }}</span>
                                        @else
                                            <span class="badge badge-gray">{{ ucfirst($ts->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ts->status === 'submitted' && ($membership->hasPermission('timesheets.approve') || $user->isSuperAdmin()))
                                            <div style="display: flex; gap: 6px;">
                                                <button onclick="approveTimesheet('{{ $ts->id }}')" class="header-btn" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 4px 8px; font-size: 11px;">
                                                    ✓ {{ __('Approve') }}
                                                </button>
                                                <button onclick="openRejectModal('{{ $ts->id }}')" class="header-btn" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); padding: 4px 8px; font-size: 11px;">
                                                    ✕ {{ __('Reject') }}
                                                </button>
                                            </div>
                                        @else
                                            <span style="font-size: 11px; color: var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                        {{ __('No timesheets submitted for review.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 11. TEAM WORKLOAD TAB -->
        <div id="tab-workload" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">👥 {{ __('Team Capacity & Workload Matrix') }}</h1>
                    <p class="page-subtitle">{{ __('Monitor weekly employee availability, assigned hours, and capacity utilization.') }}</p>
                </div>
            </div>

            <!-- Team Capacity Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📊 {{ __('Employee Workload Distribution') }}</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Team Member') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Weekly Capacity') }}</th>
                                <th>{{ __('Assigned Tasks') }}</th>
                                <th>{{ __('Estimated Hours') }}</th>
                                <th>{{ __('Capacity Utilization') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $m)
                                @php
                                    $capacity = $m->weekly_capacity_hours ?? 40.00;
                                    $memberTasks = $tasks->where('assignee_id', $m->user_id)->where('status', '!=', 'done');
                                    $assignedHours = $memberTasks->sum('estimated_hours');
                                    $utilization = ($capacity > 0) ? round(($assignedHours / $capacity) * 100) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">
                                                {{ strtoupper(substr($m->user->name ?? 'M', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 800; color: var(--text-primary);">{{ $m->user->name ?? 'Member' }}</div>
                                                <div style="font-size: 11px; color: var(--text-muted);">{{ $m->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-purple">{{ $m->role->name ?? 'Member' }}</span></td>
                                    <td style="font-weight: 800; font-family: monospace;">{{ $capacity }}h / wk</td>
                                    <td style="font-weight: 700;">{{ $memberTasks->count() }} {{ __('active') }}</td>
                                    <td style="font-weight: 800; color: var(--brand-teal); font-family: monospace;">{{ $assignedHours }}h</td>
                                    <td style="min-width: 180px;">
                                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px; font-weight: 800;">
                                            <span style="color: {{ $utilization > 100 ? '#ef4444' : ($utilization > 80 ? '#fbbf24' : '#34d399') }};">{{ $utilization }}%</span>
                                            <span style="color: var(--text-muted);">{{ $assignedHours }} / {{ $capacity }}h</span>
                                        </div>
                                        <div class="progress-bar-bg">
                                            <div class="progress-bar-fill" style="width: {{ min(100, $utilization) }}%; background: {{ $utilization > 100 ? '#ef4444' : ($utilization > 80 ? '#fbbf24' : '#10b981') }};"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- Modal: New Project -->
    <div id="new-project-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 540px;">
            <div class="modal-header">
                <h3 class="modal-title">📁 {{ __('Create New Project') }}</h3>
                <button onclick="closeNewProjectModal()" class="modal-close">✕</button>
            </div>
            <form id="new-project-form" onsubmit="createProjectSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Project Name') }} *</label>
                    <input type="text" name="name" required placeholder="e.g. Mobile App Redesign, Cloud Migration" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Project Code') }}</label>
                        <input type="text" name="code" placeholder="e.g. MOB-01" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Priority') }}</label>
                        <select name="priority" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="low">{{ __('Low') }}</option>
                            <option value="high">{{ __('High') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Project Manager') }}</label>
                        <select name="manager_id" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('Select Manager') }} —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Department') }}</label>
                        <select name="department_id" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('No Department') }} —</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Budget ($)') }}</label>
                        <input type="number" step="0.01" name="budget_amount" placeholder="10000" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Planned Hours') }}</label>
                        <input type="number" step="0.5" name="planned_hours" placeholder="160" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Due Date') }}</label>
                    <input type="date" name="due_date" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" placeholder="Brief project summary..." style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;"></textarea>
                </div>
                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    🚀 {{ __('Create Project') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: New Task -->
    <div id="new-task-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 540px;">
            <div class="modal-header">
                <h3 class="modal-title">✅ {{ __('Create New Task') }}</h3>
                <button onclick="closeNewTaskModal()" class="modal-close">✕</button>
            </div>
            <form id="new-task-form" onsubmit="createTaskSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Project') }} *</label>
                    <select name="project_id" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">📁 {{ $p->name }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Task Title') }} *</label>
                    <input type="text" name="title" required placeholder="e.g. Implement authentication middleware" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Assignee') }}</label>
                        <select name="assignee_id" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('Unassigned') }} —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Priority') }}</label>
                        <select name="priority" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="low">{{ __('Low') }}</option>
                            <option value="high">{{ __('High') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Estimated Hours') }}</label>
                        <input type="number" step="0.5" name="estimated_hours" placeholder="4.0" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Due Date') }}</label>
                        <input type="date" name="due_date" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>
                </div>
                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Create Task') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Manual Time Entry -->
    <div id="manual-time-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">✍️ {{ __('Log Manual Time Entry') }}</h3>
                <button onclick="closeManualTimeModal()" class="modal-close">✕</button>
            </div>
            <form id="manual-time-form" onsubmit="logManualTimeSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Project') }} *</label>
                    <select name="project_id" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">📁 {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Start Time') }} *</label>
                        <input type="datetime-local" name="started_at" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('End Time') }} *</label>
                        <input type="datetime-local" name="ended_at" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600;">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Description') }}</label>
                    <input type="text" name="description" placeholder="What did you work on?" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    ⏱️ {{ __('Log Time') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Reject Timesheet -->
    <div id="reject-timesheet-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 480px;">
            <div class="modal-header">
                <h3 class="modal-title">❌ {{ __('Reject Timesheet') }}</h3>
                <button onclick="closeRejectModal()" class="modal-close">✕</button>
            </div>
            <form onsubmit="rejectTimesheetSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Feedback Reason for Employee') }} *</label>
                    <textarea id="reject-reason-input" required rows="3" placeholder="Please clarify the 6 hours logged on Friday..." style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;"></textarea>
                </div>
                <button type="submit" class="header-btn" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center; background: #ef4444; color: white;">
                    ❌ {{ __('Confirm Rejection & Send Feedback') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Project Hub & KPI Dashboard Drawer -->
    <div id="project-hub-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 1100px; width: 95vw; max-height: 90vh; display: flex; flex-direction: column; padding: 24px; overflow: hidden;">
            <!-- Hub Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                        <span id="hub-proj-code" class="badge badge-blue" style="font-family: monospace; font-size: 12px;">PRJ-01</span>
                        <h2 id="hub-proj-name" style="font-size: 20px; font-weight: 900; margin: 0; color: var(--text-primary);">Project Name</h2>
                        <span id="hub-proj-status" class="badge badge-green">Active</span>
                        <span id="hub-proj-priority" class="badge badge-amber">High</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 16px; font-size: 12px; color: var(--text-muted);">
                        <span>👤 {{ __('Manager') }}: <strong id="hub-proj-manager" style="color: var(--text-primary);">Name</strong></span>
                        <span>🏛️ {{ __('Department') }}: <strong id="hub-proj-dept" style="color: var(--text-primary);">Dept</strong></span>
                        <span>📅 {{ __('Due Date') }}: <strong id="hub-proj-due" style="color: var(--text-primary);">Date</strong></span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button onclick="openNewTaskForCurrentProject()" class="header-btn btn-primary" style="padding: 6px 14px; font-size: 12px;">
                        <span>+</span> {{ __('Add Task') }}
                    </button>
                    <button onclick="closeProjectHub()" class="modal-close" style="font-size: 22px;">✕</button>
                </div>
            </div>

            <!-- Hub KPI Stats Bar -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 16px;">
                <!-- Progress KPI -->
                <div class="stat-card" style="padding: 14px; border-top: 3px solid var(--brand-teal);">
                    <div class="stat-lbl">{{ __('Progress') }}</div>
                    <div id="hub-kpi-progress-pct" class="stat-val" style="font-size: 20px; color: var(--brand-teal);">0%</div>
                    <div id="hub-kpi-tasks-ratio" style="font-size: 11px; color: var(--text-muted);">0 / 0 tasks done</div>
                </div>
                <!-- Hours & Effort KPI -->
                <div class="stat-card" style="padding: 14px; border-top: 3px solid var(--brand-primary);">
                    <div class="stat-lbl">{{ __('Actual vs Planned Hours') }}</div>
                    <div id="hub-kpi-hours" class="stat-val" style="font-size: 20px; color: var(--brand-primary);">0 / 0 h</div>
                    <div id="hub-kpi-hours-var" style="font-size: 11px; color: var(--text-muted);">Variance: 0h</div>
                </div>
                <!-- Financials & Margin KPI -->
                <div class="stat-card" style="padding: 14px; border-top: 3px solid var(--brand-gold);">
                    <div class="stat-lbl">{{ __('Budget & Labor Cost') }}</div>
                    <div id="hub-kpi-budget" class="stat-val" style="font-size: 20px; color: var(--brand-gold);">$0 / $0</div>
                    <div id="hub-kpi-margin" style="font-size: 11px; color: #34d399;">Margin: $0 (0%)</div>
                </div>
                <!-- Health & Overdue KPI -->
                <div class="stat-card" style="padding: 14px; border-top: 3px solid var(--brand-crimson);">
                    <div class="stat-lbl">{{ __('Active & Overdue') }}</div>
                    <div id="hub-kpi-active-tasks" class="stat-val" style="font-size: 20px; color: #f87171;">0 Active</div>
                    <div id="hub-kpi-overdue-tasks" style="font-size: 11px; color: #f87171;">0 Overdue</div>
                </div>
            </div>

            <!-- Hub Inner Navigation Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 14px; background: var(--bg-elevated); padding: 4px; border-radius: 10px; border: 1px solid var(--border-color);">
                <button onclick="switchHubTab('kanban')" id="hub-tab-btn-kanban" class="header-btn btn-primary" style="flex: 1; padding: 7px; font-size: 12px; justify-content: center;">
                    📌 {{ __('Kanban Board') }}
                </button>
                <button onclick="switchHubTab('tasks')" id="hub-tab-btn-tasks" class="header-btn btn-outline" style="flex: 1; padding: 7px; font-size: 12px; justify-content: center;">
                    📋 {{ __('Task Table') }}
                </button>
                <button onclick="switchHubTab('timelog')" id="hub-tab-btn-timelog" class="header-btn btn-outline" style="flex: 1; padding: 7px; font-size: 12px; justify-content: center;">
                    ⏱️ {{ __('Time Entries Log') }}
                </button>
            </div>

            <!-- Hub Content Area (Scrollable) -->
            <div style="flex: 1; overflow-y: auto; padding-right: 4px;">
                <!-- 1. Kanban View -->
                <div id="hub-view-kanban" style="display: block;">
                    <div style="display: grid; grid-template-columns: repeat(5, minmax(200px, 1fr)); gap: 12px; align-items: start;">
                        <!-- Backlog -->
                        <div class="kanban-column">
                            <div class="kanban-col-header" style="color: var(--text-secondary);">
                                <span>📌 Backlog</span>
                                <span id="col-count-backlog" class="badge badge-gray">0</span>
                            </div>
                            <div id="kanban-col-backlog" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- Ready -->
                        <div class="kanban-column">
                            <div class="kanban-col-header" style="color: #60a5fa;">
                                <span>🎯 Ready</span>
                                <span id="col-count-ready" class="badge badge-blue">0</span>
                            </div>
                            <div id="kanban-col-ready" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- In Progress -->
                        <div class="kanban-column">
                            <div class="kanban-col-header" style="color: #22d3ee;">
                                <span>⚡ In Progress</span>
                                <span id="col-count-in_progress" class="badge badge-teal">0</span>
                            </div>
                            <div id="kanban-col-in_progress" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- Review / QA -->
                        <div class="kanban-column">
                            <div class="kanban-col-header" style="color: #fbbf24;">
                                <span>🔍 Review / QA</span>
                                <span id="col-count-review" class="badge badge-amber">0</span>
                            </div>
                            <div id="kanban-col-review" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- Done -->
                        <div class="kanban-column">
                            <div class="kanban-col-header" style="color: #34d399;">
                                <span>🎉 Done</span>
                                <span id="col-count-done" class="badge badge-green">0</span>
                            </div>
                            <div id="kanban-col-done" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 2. Task Table View -->
                <div id="hub-view-tasks" style="display: none;">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Task Title') }}</th>
                                    <th>{{ __('Assignee') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Hours (Est/Act)') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="hub-task-table-body"></tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Time Log View -->
                <div id="hub-view-timelog" style="display: none;">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Task') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="hub-timelog-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Task Inspector & Activity Drawer -->
    <div id="task-details-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 850px; width: 95vw; max-height: 90vh; display: flex; flex-direction: column; padding: 24px; overflow: hidden;">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                        <span id="task-modal-code" class="badge badge-blue" style="font-family: monospace;">#1</span>
                        <h2 id="task-modal-title" style="font-size: 18px; font-weight: 900; margin: 0; color: var(--text-primary);">Task Title</h2>
                        <span id="task-modal-status-badge" class="badge badge-teal">In Progress</span>
                        <span id="task-modal-priority-badge" class="badge badge-crimson">Urgent</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px; font-size: 12px; color: var(--text-muted);">
                        <span>📁 {{ __('Project') }}: <strong id="task-modal-project" style="color: var(--text-primary);">Project Name</strong></span>
                        <span>👤 {{ __('Assignee') }}: <strong id="task-modal-assignee" style="color: var(--text-primary);">Assignee</strong></span>
                        <span>📅 {{ __('Due Date') }}: <strong id="task-modal-due" style="color: var(--text-primary);">Date</strong></span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button id="task-modal-timer-btn" class="header-btn" style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 6px 12px; font-size: 12px;">
                        ▶ {{ __('Start Timer') }}
                    </button>
                    <button onclick="closeTaskDetailsModal()" class="modal-close" style="font-size: 22px;">✕</button>
                </div>
            </div>

            <!-- Task Quick Status Changer Bar -->
            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-elevated); padding: 8px 14px; border-radius: 8px; margin-bottom: 14px; border: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">⚡ {{ __('Quick Status Update') }}:</span>
                    <select id="task-modal-status-select" onchange="updateCurrentTaskStatus(this.value)" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 12px; font-weight: 700; border-radius: 6px; padding: 4px 10px;">
                        <option value="backlog">📌 Backlog</option>
                        <option value="ready">🎯 Ready</option>
                        <option value="in_progress">⚡ In Progress</option>
                        <option value="review">🔍 In Review / QA</option>
                        <option value="done">🎉 Done / Completed</option>
                    </select>
                </div>
                <div style="font-size: 12px; font-family: monospace; font-weight: 700; color: var(--text-primary);">
                    ⏱️ <span id="task-modal-hours">0h / 0h</span>
                </div>
            </div>

            <!-- Sub-Tabs -->
            <div style="display: flex; gap: 6px; margin-bottom: 14px; background: var(--bg-elevated); padding: 4px; border-radius: 8px;">
                <button onclick="switchTaskInspectorTab('details')" id="task-tab-btn-details" class="header-btn btn-primary" style="flex: 1; padding: 6px; font-size: 12px; justify-content: center;">
                    📝 {{ __('Details') }}
                </button>
                <button onclick="switchTaskInspectorTab('checklist')" id="task-tab-btn-checklist" class="header-btn btn-outline" style="flex: 1; padding: 6px; font-size: 12px; justify-content: center;">
                    ☑️ {{ __('Checklist') }} (<span id="task-checklist-count">0</span>)
                </button>
                <button onclick="switchTaskInspectorTab('comments')" id="task-tab-btn-comments" class="header-btn btn-outline" style="flex: 1; padding: 6px; font-size: 12px; justify-content: center;">
                    💬 {{ __('Discussions') }} (<span id="task-comments-count">0</span>)
                </button>
                <button onclick="switchTaskInspectorTab('dependencies')" id="task-tab-btn-dependencies" class="header-btn btn-outline" style="flex: 1; padding: 6px; font-size: 12px; justify-content: center;">
                    🔗 {{ __('Dependencies') }}
                </button>
                <button onclick="switchTaskInspectorTab('timelog')" id="task-tab-btn-timelog" class="header-btn btn-outline" style="flex: 1; padding: 6px; font-size: 12px; justify-content: center;">
                    ⏱️ {{ __('Time Log') }}
                </button>
            </div>

            <!-- Tab Contents -->
            <div style="flex: 1; overflow-y: auto; padding-right: 4px;">
                <!-- 1. Details -->
                <div id="task-inspector-details" style="display: block;">
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 4px;">{{ __('Description') }}</label>
                        <div id="task-modal-description" style="background: var(--bg-elevated); padding: 12px; border-radius: 8px; font-size: 13px; color: var(--text-primary); line-height: 1.5; border: 1px solid var(--border-color);">
                            —
                        </div>
                    </div>
                </div>

                <!-- 2. Checklist -->
                <div id="task-inspector-checklist" style="display: none;">
                    <form onsubmit="addTaskChecklistItem(event)" style="display: flex; gap: 8px; margin-bottom: 14px;">
                        <input type="text" id="new-checklist-title-input" required placeholder="{{ __('Add checklist sub-item (e.g. Write unit tests, create migration)...') }}" style="flex: 1; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 12px; color: var(--text-primary); outline: none; font-size: 12px;">
                        <button type="submit" class="header-btn btn-primary" style="padding: 8px 14px; font-size: 12px;">
                            <span>+</span> {{ __('Add Item') }}
                        </button>
                    </form>
                    <div id="task-checklist-items-container" style="display: flex; flex-direction: column; gap: 6px;"></div>
                </div>

                <!-- 3. Comments -->
                <div id="task-inspector-comments" style="display: none;">
                    <div id="task-comments-feed" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 14px; max-height: 280px; overflow-y: auto;"></div>
                    <form onsubmit="addTaskCommentSubmit(event)" style="display: flex; gap: 8px;">
                        <input type="text" id="new-comment-body-input" required placeholder="{{ __('Write a comment or status update...') }}" style="flex: 1; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 12px; color: var(--text-primary); outline: none; font-size: 12px;">
                        <button type="submit" class="header-btn btn-primary" style="padding: 8px 14px; font-size: 12px;">
                            💬 {{ __('Post') }}
                        </button>
                    </form>
                </div>

                <!-- 4. Dependencies -->
                <div id="task-inspector-dependencies" style="display: none;">
                    <div style="background: var(--bg-elevated); padding: 12px; border-radius: 8px; margin-bottom: 14px; border: 1px solid var(--border-color);">
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">🔗 {{ __('Add Predecessor / Blocker Task') }}</label>
                        <form onsubmit="addTaskDependencySubmit(event)" style="display: flex; gap: 8px;">
                            <select id="dependency-blocker-select" required style="flex: 1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px; color: var(--text-primary); font-size: 12px;">
                                <option value="">— {{ __('Select Blocker Task') }} —</option>
                                @foreach($tasks as $oth)
                                    <option value="{{ $oth->id }}">#{{ $oth->task_number }} {{ $oth->title }} ({{ $oth->project->code ?? 'PRJ' }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="header-btn btn-primary" style="padding: 8px 14px; font-size: 12px;">
                                <span>+</span> {{ __('Add Blocker') }}
                            </button>
                        </form>
                    </div>
                    <div id="task-dependencies-container" style="display: flex; flex-direction: column; gap: 6px;"></div>
                </div>

                <!-- 5. Time Log -->
                <div id="task-inspector-timelog" style="display: none;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Member') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="task-modal-timelog-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Destination Room') }}</label>
                        <select id="invite-room-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">🏢 {{ $r->name }} ({{ ucfirst($r->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Guest Name / Label') }}</label>
                        <input type="text" id="invite-guest-name" value="Investor / Partner" placeholder="e.g. Sarah Miller" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Link Expiration') }}</label>
                        <select id="invite-guest-hours" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="1">1 Hour</option>
                            <option value="12">12 Hours</option>
                            <option value="24" selected>24 Hours (1 Day)</option>
                            <option value="72">72 Hours (3 Days)</option>
                        </select>
                    </div>

                    <button onclick="generateGuestLink()" id="btn-generate-guest" style="margin-top: 6px; background: linear-gradient(135deg, #10b981, #059669); color: white; font-weight: 800; border: none; border-radius: 10px; padding: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);">
                        <span>⚡</span> {{ __('Generate Instant Guest Link') }}
                    </button>

                    <div id="guest-result-box" style="display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px; padding: 12px; margin-top: 10px;">
                        <div style="font-size: 11px; font-weight: 800; color: #34d399; text-transform: uppercase; margin-bottom: 6px;">✅ Invitation Link Ready!</div>
                        <input type="text" id="guest-link-output" readonly style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 6px; padding: 8px; color: var(--brand-teal); font-size: 12px; font-family: monospace; margin-bottom: 8px;">
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="copyModalGuestLink(this)" id="btn-copy-link" style="flex: 1; background: var(--brand-primary); color: white; font-weight: 700; border: none; border-radius: 6px; padding: 8px; cursor: pointer; font-size: 12px;">
                                📋 {{ __('Copy Link') }}
                            </button>
                            <a id="guest-open-link" href="#" target="_blank" style="background: var(--bg-elevated); border: 1px solid var(--border-color); color: var(--text-primary); font-weight: 700; text-decoration: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; display: flex; align-items: center;">
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
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Email Address') }}</label>
                        <input type="email" id="invite-member-email" placeholder="colleague@company.com" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Role') }}</label>
                        <select id="invite-member-role" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button onclick="sendMemberInvite()" id="btn-send-member-invite" style="margin-top: 6px; background: var(--brand-primary); color: white; font-weight: 800; border: none; border-radius: 10px; padding: 12px; cursor: pointer; font-size: 14px;">
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
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Department Name') }}</label>
                    <input type="text" name="name" id="department-name-input" required placeholder="e.g. Engineering & IT, Marketing, Sales" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
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
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Team Name') }}</label>
                    <input type="text" name="name" required placeholder="e.g. Frontend Team, Enterprise Sales, UI/UX Design" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
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
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Employee / Member') }}</label>
                    <div id="assign-member-name" style="font-size: 14px; font-weight: 800; color: var(--text-primary); background: var(--bg-elevated); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                        Member Name
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Department') }}</label>
                    <select name="department_id" id="assign-dept-select" onchange="filterTeamsForAssign(this.value)" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="">— {{ __('No Department') }} —</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Sub-Team') }}</label>
                    <select name="team_id" id="assign-team-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="">— {{ __('No Team') }} —</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Job Title') }}</label>
                    <input type="text" name="job_title" id="assign-job-title" placeholder="e.g. Lead Software Architect, Growth Specialist" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Access Role') }}</label>
                    <select name="role_id" id="assign-role-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
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
            if (targetTab) {
                targetTab.classList.add('active');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                if (window.history && window.history.pushState) {
                    window.history.pushState(null, null, '#' + tabName);
                }
            }

            // Highlight corresponding sidebar button
            document.querySelectorAll('.nav-tab-btn').forEach(btn => {
                const onclickAttr = btn.getAttribute('onclick') || '';
                if (onclickAttr.includes(`'${tabName}'`) || onclickAttr.includes(`"${tabName}"`)) {
                    btn.classList.add('active');
                }
            });
        }

        // Auto-open tab from URL hash on load (e.g. /dashboard#projects)
        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(`tab-${hash}`)) {
                switchAdminTab(hash);
            }
        });

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

        function showToastNotification(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'toast-popup';
            toast.innerHTML = message;
            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('toast-fadeout');
                setTimeout(() => {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 300);
            }, 3000);
        }

        function triggerCopySuccess(btn) {
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '✅ {{ __('Copied!') }}';
                btn.style.background = '#10b981';
                btn.style.borderColor = '#10b981';
                btn.style.color = '#ffffff';
                btn.classList.remove('btn-copied-pulse');
                void btn.offsetWidth; // Force CSS reflow to re-trigger pulse animation
                btn.classList.add('btn-copied-pulse');

                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                    btn.style.borderColor = '';
                    btn.style.color = '';
                    btn.classList.remove('btn-copied-pulse');
                }, 2200);
            }
            showToastNotification('📋 <strong>' + "{{ __('Link Copied!') }}" + '</strong> — ' + "{{ __('Guest meeting link copied to clipboard.') }}", 'success');
        }

        function executeClipboardCopy(text) {
            if (!text) return false;
            let copied = false;

            // Strategy 1: Firefox Native Copy Event Interceptor
            try {
                const onCopy = function(e) {
                    if (e.clipboardData) {
                        e.clipboardData.setData('text/plain', text);
                        e.preventDefault();
                        copied = true;
                    }
                };
                document.addEventListener('copy', onCopy, { once: true });
                document.execCommand('copy');
                document.removeEventListener('copy', onCopy);
            } catch (err) {}

            // Strategy 2: DOM Textarea selection fallback
            if (!copied) {
                try {
                    const temp = document.createElement('textarea');
                    temp.value = text;
                    temp.style.position = 'fixed';
                    temp.style.top = '10px';
                    temp.style.left = '10px';
                    temp.style.width = '100px';
                    temp.style.height = '40px';
                    temp.style.padding = '0';
                    temp.style.border = 'none';
                    temp.style.outline = 'none';
                    temp.style.boxShadow = 'none';
                    temp.style.background = 'transparent';
                    temp.style.opacity = '0.01';
                    temp.style.zIndex = '-9999';
                    document.body.appendChild(temp);
                    temp.focus();
                    temp.select();
                    temp.setSelectionRange(0, text.length);
                    copied = document.execCommand('copy');
                    document.body.removeChild(temp);
                } catch (e) {}
            }

            // Strategy 3: Async Clipboard API
            if (!copied && navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).catch(() => {});
                copied = true;
            }

            return copied;
        }

        function copyTableGuestLink(url, btn) {
            if (!url) return;
            // Ensure origin is current
            if (url.startsWith('http://') || url.startsWith('https://')) {
                const path = url.replace(/^https?:\/\/[^\/]+/, '');
                url = window.location.origin + path;
            }
            executeClipboardCopy(url);
            triggerCopySuccess(btn);
        }

        function copyModalGuestLink(btn) {
            const input = document.getElementById('guest-link-output');
            const text = input ? input.value : '';
            if (!text) return;
            executeClipboardCopy(text);
            triggerCopySuccess(btn);
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

        function toggleGlobalTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('vw_theme', next);
            document.getElementById('theme-icon').textContent = next === 'dark' ? '🌙' : '☀️';
        }

        // ── PROJECT MANAGEMENT CLIENT CONTROLLERS ──
        let activeTimerSeconds = {{ $activeTimer ? $activeTimer->elapsedSeconds() : 0 }};
        let activeTimerInterval = null;

        function formatTimerClock(totalSeconds) {
            const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
            const s = String(totalSeconds % 60).padStart(2, '0');
            return `${h}:${m}:${s}`;
        }

        function initLiveTimerTicker() {
            if (activeTimerInterval) clearInterval(activeTimerInterval);
            const clockEl = document.getElementById('live-timer-clock');
            if (clockEl) clockEl.textContent = formatTimerClock(activeTimerSeconds);

            @if($activeTimer)
                activeTimerInterval = setInterval(() => {
                    activeTimerSeconds++;
                    if (clockEl) clockEl.textContent = formatTimerClock(activeTimerSeconds);
                }, 1000);
            @endif
        }
        initLiveTimerTicker();

        async function startTaskTimer(projectId, taskId, taskTitle, projectName) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/time/timer/start`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ project_id: projectId, task_id: taskId })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Failed to start timer.');
                    return;
                }

                // Update UI timer strip
                document.getElementById('universal-timer-strip').style.display = 'flex';
                document.getElementById('timer-project-tag').textContent = projectName;
                document.getElementById('timer-task-title').textContent = taskTitle;
                activeTimerSeconds = 0;
                if (activeTimerInterval) clearInterval(activeTimerInterval);
                activeTimerInterval = setInterval(() => {
                    activeTimerSeconds++;
                    const clock = document.getElementById('live-timer-clock');
                    if (clock) clock.textContent = formatTimerClock(activeTimerSeconds);
                }, 1000);
            } catch (e) {
                console.error(e);
                alert('Network error starting timer.');
            }
        }

        async function stopGlobalTimer() {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/time/timer/stop`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({})
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Failed to stop timer.');
                    return;
                }

                if (activeTimerInterval) clearInterval(activeTimerInterval);
                document.getElementById('universal-timer-strip').style.display = 'none';
                alert('✅ Timer stopped and work session logged successfully!');
                window.location.reload();
            } catch (e) {
                console.error(e);
                alert('Network error stopping timer.');
            }
        }

        // New Project Modal
        function openNewProjectModal() { document.getElementById('new-project-modal').style.display = 'flex'; }
        function closeNewProjectModal() { document.getElementById('new-project-modal').style.display = 'none'; }

        async function createProjectSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('new-project-form');
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error creating project.');
                    return;
                }
                closeNewProjectModal();
                alert('✅ Project created successfully!');
                window.location.reload();
            } catch (err) {
                alert('Network error creating project.');
            }
        }

        // New Task Modal
        function openNewTaskModal() { document.getElementById('new-task-modal').style.display = 'flex'; }
        function closeNewTaskModal() { document.getElementById('new-task-modal').style.display = 'none'; }

        async function createTaskSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('new-task-form');
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error creating task.');
                    return;
                }
                closeNewTaskModal();
                alert('✅ Task created successfully!');
                window.location.reload();
            } catch (err) {
                alert('Network error creating task.');
            }
        }

        // Manual Time Entry
        function openManualTimeModal() { document.getElementById('manual-time-modal').style.display = 'flex'; }
        function closeManualTimeModal() { document.getElementById('manual-time-modal').style.display = 'none'; }

        async function logManualTimeSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('manual-time-form');
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/time/entries/manual`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error logging time.');
                    return;
                }
                closeManualTimeModal();
                alert('✅ Time entry logged successfully!');
                window.location.reload();
            } catch (err) {
                alert('Network error logging time.');
            }
        }

        // Timesheets Actions
        async function submitMyCurrentTimesheet() {
            if (!confirm('Submit your weekly timesheet for manager review? Logged entries will be locked.')) return;
            const now = new Date();
            const first = now.getDate() - now.getDay() + 1;
            const monday = new Date(now.setDate(first)).toISOString().split('T')[0];
            const sunday = new Date(now.setDate(first + 6)).toISOString().split('T')[0];

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/timesheets/submit`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ period_start: monday, period_end: sunday })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error submitting timesheet.');
                    return;
                }
                alert('✅ Timesheet submitted successfully!');
                window.location.reload();
            } catch (e) {
                alert('Network error submitting timesheet.');
            }
        }

        async function approveTimesheet(timesheetId) {
            if (!confirm('Approve and permanently lock this timesheet?')) return;
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/timesheets/${timesheetId}/approve`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error approving timesheet.');
                    return;
                }
                alert('✅ Timesheet approved!');
                window.location.reload();
            } catch (e) {
                alert('Network error approving timesheet.');
            }
        }

        let currentRejectTimesheetId = null;
        function openRejectModal(id) {
            currentRejectTimesheetId = id;
            document.getElementById('reject-timesheet-modal').style.display = 'flex';
        }
        function closeRejectModal() { document.getElementById('reject-timesheet-modal').style.display = 'none'; }

        async function rejectTimesheetSubmit(e) {
            e.preventDefault();
            const reason = document.getElementById('reject-reason-input').value;
            if (!reason) return alert('Please enter a feedback reason.');

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/timesheets/${currentRejectTimesheetId}/reject`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ rejection_reason: reason })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error rejecting timesheet.');
                    return;
                }
                closeRejectModal();
                alert('✅ Timesheet rejected and feedback returned to employee.');
                window.location.reload();
            } catch (e) {
                alert('Network error rejecting timesheet.');
            }
        }

        // ── PROJECT HUB & KPI DASHBOARD CONTROLLER ──
        let activeHubProjectId = null;

        async function openProjectHub(projectId) {
            activeHubProjectId = projectId;
            const modal = document.getElementById('project-hub-modal');
            modal.style.display = 'flex';

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects/${projectId}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error loading project details.');
                    return;
                }
                const data = await res.json();
                renderProjectHub(data);
            } catch (e) {
                console.error(e);
                alert('Network error loading project hub.');
            }
        }

        function closeProjectHub() {
            document.getElementById('project-hub-modal').style.display = 'none';
            activeHubProjectId = null;
        }

        function switchHubTab(tab) {
            ['kanban', 'tasks', 'timelog'].forEach(t => {
                const view = document.getElementById(`hub-view-${t}`);
                const btn = document.getElementById(`hub-tab-btn-${t}`);
                if (view) view.style.display = (t === tab) ? 'block' : 'none';
                if (btn) {
                    if (t === tab) {
                        btn.className = 'header-btn btn-primary';
                    } else {
                        btn.className = 'header-btn btn-outline';
                    }
                }
            });
        }

        function openNewTaskForCurrentProject() {
            if (!activeHubProjectId) return;
            const select = document.querySelector('#new-task-form select[name="project_id"]');
            if (select) select.value = activeHubProjectId;
            openNewTaskModal();
        }

        async function updateHubTaskStatus(taskId, newStatus) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: newStatus })
                });
                if (!res.ok) {
                    alert('Error updating task status.');
                    return;
                }
                // Refresh project hub
                if (activeHubProjectId) openProjectHub(activeHubProjectId);
            } catch (e) {
                alert('Network error updating task.');
            }
        }

        function renderProjectHub(data) {
            const p = data.project;
            const k = data.kpis || {};

            // Header info
            document.getElementById('hub-proj-code').textContent = p.code || 'PRJ';
            document.getElementById('hub-proj-name').textContent = p.name;
            document.getElementById('hub-proj-status').textContent = (p.status || 'active').toUpperCase();
            document.getElementById('hub-proj-priority').textContent = (p.priority || 'medium').toUpperCase();
            document.getElementById('hub-proj-manager').textContent = p.manager ? p.manager.name : 'Unassigned';
            document.getElementById('hub-proj-dept').textContent = p.department ? p.department.name : 'General';
            document.getElementById('hub-proj-due').textContent = p.due_date ? new Date(p.due_date).toLocaleDateString() : '—';

            // KPI Cards
            document.getElementById('hub-kpi-progress-pct').textContent = `${k.progress_pct || 0}%`;
            document.getElementById('hub-kpi-tasks-ratio').textContent = `${k.completed_tasks || 0} / ${k.total_tasks || 0} tasks done`;
            
            document.getElementById('hub-kpi-hours').textContent = `${k.actual_hours || 0} / ${k.planned_hours || 0} h`;
            document.getElementById('hub-kpi-hours-var').textContent = `Variance: ${k.hours_variance || 0}h`;

            document.getElementById('hub-kpi-budget').textContent = `$${Number(k.budget_amount || 0).toLocaleString()} / $${Number(k.labor_cost || 0).toLocaleString()}`;
            document.getElementById('hub-kpi-margin').textContent = `Margin: $${Number(k.gross_margin || 0).toLocaleString()} (${k.gross_margin_pct || 0}%)`;

            document.getElementById('hub-kpi-active-tasks').textContent = `${k.in_progress_tasks || 0} Active`;
            document.getElementById('hub-kpi-overdue-tasks').textContent = `${k.overdue_tasks || 0} Overdue`;

            // Clear Kanban columns
            const cols = ['backlog', 'ready', 'in_progress', 'review', 'done'];
            cols.forEach(c => {
                const el = document.getElementById(`kanban-col-${c}`);
                if (el) el.innerHTML = '';
                const cnt = document.getElementById(`col-count-${c}`);
                if (cnt) cnt.textContent = '0';
            });

            // Populate Kanban Cards & Task Table
            const tasks = p.tasks || [];
            const taskCounts = { backlog: 0, ready: 0, in_progress: 0, review: 0, done: 0 };
            const taskTableBody = document.getElementById('hub-task-table-body');
            if (taskTableBody) taskTableBody.innerHTML = '';

            tasks.forEach(t => {
                const status = (t.status === 'qa') ? 'review' : t.status;
                if (taskCounts[status] !== undefined) taskCounts[status]++;

                // Kanban Card HTML
                const colEl = document.getElementById(`kanban-col-${status}`);
                if (colEl) {
                    const card = document.createElement('div');
                    card.className = 'kanban-card';
                    card.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                            <span class="badge badge-blue" style="font-size: 10px;">#${t.task_number || 1}</span>
                            <select onchange="updateHubTaskStatus('${t.id}', this.value)" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 10px; font-weight: 700; border-radius: 4px; padding: 2px;">
                                <option value="backlog" ${t.status === 'backlog' ? 'selected' : ''}>Backlog</option>
                                <option value="ready" ${t.status === 'ready' ? 'selected' : ''}>Ready</option>
                                <option value="in_progress" ${t.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                <option value="review" ${t.status === 'review' || t.status === 'qa' ? 'selected' : ''}>Review</option>
                                <option value="done" ${t.status === 'done' ? 'selected' : ''}>Done</option>
                            </select>
                        </div>
                        <div style="font-weight: 800; font-size: 13px; margin-bottom: 4px; color: var(--text-primary);">${t.title}</div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; border-top: 1px solid var(--border-color); padding-top: 6px; font-size: 11px;">
                            <span style="color: var(--text-muted);">👤 ${t.assignee ? t.assignee.name.split(' ')[0] : 'Unassigned'}</span>
                            <button onclick="startTaskTimer('${p.id}', '${t.id}', '${t.title.replace(/'/g, "\\'")}', '${p.name.replace(/'/g, "\\'")}')" class="header-btn" style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 2px 6px; font-size: 10px;">
                                ▶ Timer
                            </button>
                        </div>
                    `;
                    colEl.appendChild(card);
                }

                // Task Table Row
                if (taskTableBody) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td style="font-family: monospace; font-weight: 700;">#${t.task_number || 1}</td>
                        <td style="font-weight: 800; color: var(--text-primary);">${t.title}</td>
                        <td>${t.assignee ? t.assignee.name : '<span style="color: var(--text-muted);">Unassigned</span>'}</td>
                        <td><span class="badge ${t.status === 'done' ? 'badge-green' : (t.status === 'in_progress' ? 'badge-teal' : 'badge-gray')}">${t.status}</span></td>
                        <td><span class="badge ${t.priority === 'urgent' ? 'badge-crimson' : (t.priority === 'high' ? 'badge-amber' : 'badge-gray')}">${t.priority}</span></td>
                        <td style="font-family: monospace;">${t.estimated_hours || 0}h / ${t.actual_hours || 0}h</td>
                        <td>${t.due_date ? new Date(t.due_date).toLocaleDateString() : '—'}</td>
                        <td>
                            <button onclick="startTaskTimer('${p.id}', '${t.id}', '${t.title.replace(/'/g, "\\'")}', '${p.name.replace(/'/g, "\\'")}')" class="header-btn btn-outline" style="padding: 3px 8px; font-size: 10px;">
                                ▶ Timer
                            </button>
                        </td>
                    `;
                    taskTableBody.appendChild(row);
                }
            });

            // Update column count badges
            cols.forEach(c => {
                const cnt = document.getElementById(`col-count-${c}`);
                if (cnt) cnt.textContent = taskCounts[c] || 0;
            });

            // Populate Time Log Table
            const timelogBody = document.getElementById('hub-timelog-table-body');
            if (timelogBody) {
                timelogBody.innerHTML = '';
                const entries = p.time_entries || [];
                if (entries.length === 0) {
                    timelogBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">No time tracked on this project yet.</td></tr>';
                } else {
                    entries.forEach(e => {
                        const tr = document.createElement('tr');
                        const hrs = (e.duration_seconds / 3600).toFixed(2);
                        tr.innerHTML = `
                            <td>${new Date(e.started_at).toLocaleDateString()}</td>
                            <td style="font-weight: 700;">${e.user ? e.user.name : 'Member'}</td>
                            <td>${e.task ? e.task.title : '—'}</td>
                            <td style="font-weight: 800; color: #34d399; font-family: monospace;">${hrs}h</td>
                            <td style="font-size: 11px; color: var(--text-secondary);">${e.description || 'Work session'}</td>
                            <td><span class="badge badge-gray">${e.entry_type}</span></td>
                            <td><span class="badge ${e.status === 'approved' ? 'badge-green' : (e.status === 'submitted' ? 'badge-amber' : 'badge-gray')}">${e.status}</span></td>
                        `;
                        timelogBody.appendChild(tr);
                    });
                }
            }
        }

        // ── ALL TASKS MANAGER CONTROLLER ──
        function switchAllTasksView(view) {
            const tblView = document.getElementById('alltasks-view-table');
            const knbView = document.getElementById('alltasks-view-kanban');
            const tblBtn = document.getElementById('alltasks-btn-table');
            const knbBtn = document.getElementById('alltasks-btn-kanban');

            if (view === 'table') {
                tblView.style.display = 'block';
                knbView.style.display = 'none';
                tblBtn.className = 'header-btn btn-primary';
                knbBtn.className = 'header-btn btn-outline';
            } else {
                tblView.style.display = 'none';
                knbView.style.display = 'block';
                tblBtn.className = 'header-btn btn-outline';
                knbBtn.className = 'header-btn btn-primary';
            }
        }

        function filterAllTasksTable() {
            const query = (document.getElementById('alltasks-filter-search')?.value || '').toLowerCase().trim();
            const proj = document.getElementById('alltasks-filter-project')?.value || '';
            const status = document.getElementById('alltasks-filter-status')?.value || '';
            const priority = document.getElementById('alltasks-filter-priority')?.value || '';
            const assignee = document.getElementById('alltasks-filter-assignee')?.value || '';

            const rows = document.querySelectorAll('.alltask-row');
            let visibleCount = 0;

            rows.forEach(r => {
                const title = r.dataset.title || '';
                const rProj = r.dataset.projectId || '';
                const rStatus = r.dataset.status || '';
                const rPriority = r.dataset.priority || '';
                const rAssignee = r.dataset.assigneeId || '';

                const matchesQuery = !query || title.includes(query);
                const matchesProj = !proj || rProj === proj;
                const matchesStatus = !status || rStatus === status;
                const matchesPriority = !priority || rPriority === priority;
                const matchesAssignee = !assignee || rAssignee === assignee;

                if (matchesQuery && matchesProj && matchesStatus && matchesPriority && matchesAssignee) {
                    r.style.display = '';
                    visibleCount++;
                } else {
                    r.style.display = 'none';
                }
            });

            const cntEl = document.getElementById('alltasks-filtered-count');
            if (cntEl) cntEl.textContent = visibleCount;
        }

        async function updateTaskStatusDirect(taskId, newStatus) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: newStatus })
                });
                if (!res.ok) {
                    alert('Error updating task status.');
                    return;
                }
                window.location.reload();
            } catch (e) {
                alert('Network error updating task.');
            }
        }

        // ── TASK INSPECTOR / DETAILS DRAWER ──
        let activeInspectorTaskId = null;
        let currentInspectorTask = null;

        async function openTaskDetails(taskId) {
            activeInspectorTaskId = taskId;
            const modal = document.getElementById('task-details-modal');
            modal.style.display = 'flex';

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error loading task details.');
                    return;
                }
                const data = await res.json();
                currentInspectorTask = data.task || data;
                renderTaskDetails(currentInspectorTask);
            } catch (e) {
                console.error(e);
                alert('Network error loading task details.');
            }
        }

        function closeTaskDetailsModal() {
            document.getElementById('task-details-modal').style.display = 'none';
            activeInspectorTaskId = null;
            currentInspectorTask = null;
        }

        function switchTaskInspectorTab(tab) {
            ['details', 'checklist', 'comments', 'dependencies', 'timelog'].forEach(t => {
                const view = document.getElementById(`task-inspector-${t}`);
                const btn = document.getElementById(`task-tab-btn-${t}`);
                if (view) view.style.display = (t === tab) ? 'block' : 'none';
                if (btn) {
                    btn.className = (t === tab) ? 'header-btn btn-primary' : 'header-btn btn-outline';
                }
            });
        }

        async function updateCurrentTaskStatus(newStatus) {
            if (!activeInspectorTaskId) return;
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectorTaskId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: newStatus })
                });
                if (!res.ok) {
                    alert('Error updating status.');
                    return;
                }
                openTaskDetails(activeInspectorTaskId);
            } catch (e) {
                alert('Network error updating status.');
            }
        }

        async function addTaskChecklistItem(e) {
            e.preventDefault();
            const input = document.getElementById('new-checklist-title-input');
            const title = input.value.trim();
            if (!title || !activeInspectorTaskId) return;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectorTaskId}/checklist`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ title: title })
                });
                if (!res.ok) {
                    alert('Error adding checklist item.');
                    return;
                }
                input.value = '';
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error adding checklist item.');
            }
        }

        async function toggleTaskChecklistItem(itemId) {
            if (!activeInspectorTaskId) return;
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectorTaskId}/checklist/${itemId}/toggle`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error toggling checklist item.');
                    return;
                }
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error toggling checklist item.');
            }
        }

        async function addTaskCommentSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('new-comment-body-input');
            const body = input.value.trim();
            if (!body || !activeInspectorTaskId) return;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectorTaskId}/comments`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ body: body })
                });
                if (!res.ok) {
                    alert('Error posting comment.');
                    return;
                }
                input.value = '';
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error posting comment.');
            }
        }

        async function addTaskDependencySubmit(e) {
            e.preventDefault();
            const select = document.getElementById('dependency-blocker-select');
            const blockerId = select.value;
            if (!blockerId || !activeInspectorTaskId) return;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectorTaskId}/dependencies`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ depends_on_task_id: blockerId })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error adding dependency.');
                    return;
                }
                select.value = '';
                alert('✅ Dependency linked successfully!');
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error linking dependency.');
            }
        }

        function renderTaskDetails(t) {
            // Header
            document.getElementById('task-modal-code').textContent = `#${t.task_number || 1}`;
            document.getElementById('task-modal-title').textContent = t.title;
            document.getElementById('task-modal-status-badge').textContent = (t.status || 'backlog').toUpperCase();
            document.getElementById('task-modal-priority-badge').textContent = (t.priority || 'medium').toUpperCase();
            document.getElementById('task-modal-project').textContent = t.project ? t.project.name : 'General';
            document.getElementById('task-modal-assignee').textContent = t.assignee ? t.assignee.name : 'Unassigned';
            document.getElementById('task-modal-due').textContent = t.due_date ? new Date(t.due_date).toLocaleDateString() : '—';
            document.getElementById('task-modal-status-select').value = t.status || 'backlog';
            document.getElementById('task-modal-description').textContent = t.description || 'No description provided.';
            document.getElementById('task-modal-hours').textContent = `${t.estimated_hours || 0}h est / ${t.actual_hours || 0}h act`;

            // Timer Button
            const timerBtn = document.getElementById('task-modal-timer-btn');
            if (timerBtn) {
                const pId = t.project_id || '';
                const pName = t.project ? t.project.name : 'Project';
                timerBtn.onclick = () => startTaskTimer(pId, t.id, t.title, pName);
            }

            // Checklist
            const items = t.checklist_items || [];
            document.getElementById('task-checklist-count').textContent = items.length;
            const checkContainer = document.getElementById('task-checklist-items-container');
            if (checkContainer) {
                checkContainer.innerHTML = '';
                if (items.length === 0) {
                    checkContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px;">No checklist items yet. Add sub-items above.</div>';
                } else {
                    items.forEach(item => {
                        const div = document.createElement('div');
                        div.style = 'display: flex; align-items: center; justify-content: space-between; background: var(--bg-elevated); padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color);';
                        div.innerHTML = `
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: var(--text-primary); text-decoration: ${item.is_completed ? 'line-through' : 'none'}; opacity: ${item.is_completed ? 0.6 : 1};">
                                <input type="checkbox" onchange="toggleTaskChecklistItem('${item.id}')" ${item.is_completed ? 'checked' : ''}>
                                <span>${item.title}</span>
                            </label>
                            <span class="badge ${item.is_completed ? 'badge-green' : 'badge-gray'}" style="font-size: 10px;">${item.is_completed ? 'Done' : 'Pending'}</span>
                        `;
                        checkContainer.appendChild(div);
                    });
                }
            }

            // Comments
            const comments = t.comments || [];
            document.getElementById('task-comments-count').textContent = comments.length;
            const commContainer = document.getElementById('task-comments-feed');
            if (commContainer) {
                commContainer.innerHTML = '';
                if (comments.length === 0) {
                    commContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px;">No discussions or comments yet.</div>';
                } else {
                    comments.forEach(c => {
                        const box = document.createElement('div');
                        box.style = 'background: var(--bg-elevated); padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px;';
                        const author = c.user ? c.user.name : 'Team Member';
                        const time = new Date(c.created_at).toLocaleString();
                        box.innerHTML = `
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 11px;">
                                <strong style="color: var(--brand-teal);">👤 ${author}</strong>
                                <span style="color: var(--text-muted);">${time}</span>
                            </div>
                            <div style="color: var(--text-primary); line-height: 1.4;">${c.body || ''}</div>
                        `;
                        commContainer.appendChild(box);
                    });
                }
            }

            // Dependencies
            const deps = t.dependencies || [];
            const depContainer = document.getElementById('task-dependencies-container');
            if (depContainer) {
                depContainer.innerHTML = '';
                if (deps.length === 0) {
                    depContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px;">No blocker dependencies. This task can be started immediately.</div>';
                } else {
                    deps.forEach(d => {
                        const item = document.createElement('div');
                        item.style = 'background: var(--bg-elevated); padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; display: flex; justify-content: space-between; align-items: center;';
                        const depTask = d.depends_on_task || {};
                        item.innerHTML = `
                            <span>🔒 <strong>Depends On:</strong> #${depTask.task_number || ''} ${depTask.title || 'Predecessor Task'}</span>
                            <span class="badge ${depTask.status === 'done' ? 'badge-green' : 'badge-crimson'}">${depTask.status || 'pending'}</span>
                        `;
                        depContainer.appendChild(item);
                    });
                }
            }

            // Time Log
            const timeBody = document.getElementById('task-modal-timelog-body');
            if (timeBody) {
                timeBody.innerHTML = '';
                const entries = t.time_entries || [];
                if (entries.length === 0) {
                    timeBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 14px; color: var(--text-muted);">No time tracked on this task yet.</td></tr>';
                } else {
                    entries.forEach(e => {
                        const tr = document.createElement('tr');
                        const hrs = (e.duration_seconds / 3600).toFixed(2);
                        tr.innerHTML = `
                            <td>${new Date(e.started_at).toLocaleDateString()}</td>
                            <td style="font-weight: 700;">${e.user ? e.user.name : 'Member'}</td>
                            <td style="font-weight: 800; color: #34d399; font-family: monospace;">${hrs}h</td>
                            <td style="font-size: 11px;">${e.description || 'Work session'}</td>
                            <td><span class="badge ${e.status === 'approved' ? 'badge-green' : 'badge-gray'}">${e.status}</span></td>
                        `;
                        timeBody.appendChild(tr);
                    });
                }
            }
        }
    </script>
</body>
</html>
