<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $organization->name }} — Workspace Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #070913;
            --bg-secondary: #0f172a;
            --bg-card: rgba(15, 23, 42, 0.85);
            --accent-primary: #6366f1;
            --accent-gradient: linear-gradient(135deg, #6366f1, #8b5cf6, #a855f7);
            --accent-green: #10b981;
            --accent-amber: #f59e0b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border-color: rgba(255, 255, 255, 0.08);
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 50;
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
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .sidebar-logo-text {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .sidebar-section {
            margin-bottom: 20px;
        }

        .sidebar-section-title {
            font-size: 10px;
            font-weight: 700;
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
            padding: 9px 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            background: transparent;
            border: none;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .nav-tab-btn:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
        }

        .nav-tab-btn.active {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            font-weight: 600;
        }

        .sidebar-user {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.04);
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
            font-weight: 700;
        }

        /* ── Main Content ── */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 32px 40px;
            max-width: 1300px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 13px;
            margin-top: 4px;
        }

        .header-btn {
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
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
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--accent-green);
            color: black;
            font-weight: 700;
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.12);
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
            backdrop-filter: blur(12px);
        }

        .stat-val {
            font-size: 28px;
            font-weight: 800;
            margin: 6px 0 2px;
        }

        .stat-lbl {
            font-size: 12px;
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
            backdrop-filter: blur(12px);
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table th {
            text-align: left;
            padding: 10px 14px;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table td {
            padding: 14px;
            border-bottom: 1px solid var(--border-color);
            color: #cbd5e1;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-green { background: rgba(16, 185, 129, 0.15); color: #86efac; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-purple { background: rgba(99, 102, 241, 0.15); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3); }
        .badge-amber { background: rgba(245, 158, 11, 0.15); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.3); }

        /* ── Modal Overlay ── */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-box {
            background: rgba(15, 23, 42, 0.98);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 28px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.8);
            color: white;
        }
    </style>
</head>
<body>

    <!-- Left Admin Sidebar -->
    <aside class="sidebar">
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
            <div class="sidebar-section-title">Administration</div>
            <button class="nav-tab-btn" onclick="switchAdminTab('members')">
                <span>👥</span> Team Members ({{ $members->count() }})
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('rooms')">
                <span>🏢</span> Rooms & Doors ({{ $rooms->count() }})
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('guests')">
                <span>🔗</span> Guest Invitations ({{ $guestInvitations->count() }})
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('departments')">
                <span>🏛️</span> Departments & Teams
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('audit')">
                <span>📋</span> Audit Logs
            </button>
            <button class="nav-tab-btn" onclick="switchAdminTab('settings')">
                <span>⚙️</span> Settings
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

        <!-- 1. OVERVIEW TAB -->
        <div id="tab-overview" class="tab-view active">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Executive Dashboard</h1>
                    <p class="page-subtitle">Welcome back, {{ explode(' ', $user->name)[0] }}! Manage your workplace and live presence.</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('office') }}" class="header-btn btn-primary">
                        <span>🚀</span> Enter Office
                    </a>
                    <button onclick="openInviteModal()" class="header-btn btn-success">
                        <span>+</span> Invite Member / Guest
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-lbl">Active Members</div>
                    <div class="stat-val" style="color: #818cf8;">{{ $stats['members'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-lbl">Rooms & Zones</div>
                    <div class="stat-val" style="color: #34d399;">{{ $rooms->count() }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-lbl">Departments</div>
                    <div class="stat-val" style="color: #fbbf24;">{{ $departments->count() }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-lbl">Current Plan</div>
                    <div class="stat-val" style="color: #f472b6; font-size: 22px;">{{ $organization->plan->name ?? 'Free Plan' }}</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <a href="{{ route('office') }}" style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; text-decoration: none; color: white; display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center;">
                            <span style="font-size: 26px;">🚀</span>
                            <strong style="font-size: 13px;">Virtual Workplace</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">Spatial voice & video</span>
                        </a>
                        <a href="{{ route('editor') }}" style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; text-decoration: none; color: white; display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center;">
                            <span style="font-size: 26px;">🎨</span>
                            <strong style="font-size: 13px;">Floor Designer</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">Furniture & partitions</span>
                        </a>
                        <div onclick="openInviteModal()" style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; color: white; display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center; cursor: pointer;">
                            <span style="font-size: 26px;">🔗</span>
                            <strong style="font-size: 13px;">Instant Guest Link</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">No login needed</span>
                        </div>
                        <div onclick="switchAdminTab('rooms')" style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; color: white; display: flex; flex-direction: column; gap: 6px; align-items: center; text-align: center; cursor: pointer;">
                            <span style="font-size: 26px;">🚪</span>
                            <strong style="font-size: 13px;">Manage Room Doors</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">Lock & permissions</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @forelse($auditLogs as $log)
                            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
                                <div>
                                    <strong style="color: #cbd5e1;">{{ $log->action }}</strong>
                                    <span style="color: var(--text-muted);">on {{ class_basename($log->auditable_type) }}</span>
                                </div>
                                <span style="color: var(--text-muted); font-size: 11px;">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        @empty
                            <div style="color: var(--text-muted); font-size: 13px;">Workspace created and running smoothly.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TEAM MEMBERS TAB -->
        <div id="tab-members" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Team Members & Roles</h1>
                    <p class="page-subtitle">Manage organization membership, permissions, and security roles.</p>
                </div>
                <button onclick="openInviteModal()" class="header-btn btn-primary">
                    <span>+</span> Invite Member
                </button>
            </div>

            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $m)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 30px; height: 30px; border-radius: 6px; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 11px;">
                                            {{ strtoupper(substr($m->user->name, 0, 2)) }}
                                        </div>
                                        <strong>{{ $m->user->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $m->user->email }}</td>
                                <td>
                                    <span class="badge badge-purple">{{ $m->role->name ?? 'Company Admin' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-green">Active</span>
                                </td>
                                <td>{{ $m->created_at ? $m->created_at->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <button class="header-btn btn-outline" style="padding: 4px 10px; font-size: 11px;">Edit Role</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. ROOMS TAB -->
        <div id="tab-rooms" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Meeting Rooms & Doors</h1>
                    <p class="page-subtitle">Configure private offices, conference rooms, and door lock states.</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('editor') }}" class="header-btn btn-primary">
                        <span>🎨</span> Launch Floor Editor
                    </a>
                </div>
            </div>

            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Room Name</th>
                            <th>Type</th>
                            <th>Access Mode</th>
                            <th>Capacity</th>
                            <th>Door Policy</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rooms as $r)
                            <tr>
                                <td>
                                    <strong style="color: white;">🏢 {{ $r->name }}</strong>
                                </td>
                                <td>{{ ucfirst($r->type) }}</td>
                                <td>
                                    <span class="badge {{ $r->access_mode === 'private' ? 'badge-amber' : 'badge-green' }}">
                                        {{ ucfirst($r->access_mode) }}
                                    </span>
                                </td>
                                <td>{{ $r->capacity }} Persons</td>
                                <td>
                                    <span>{{ $r->access_mode === 'private' ? '🔒 Knock & Ring Required' : '🚪 Open / Freely Accessible' }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('office') }}" class="header-btn btn-outline" style="padding: 4px 10px; font-size: 11px; text-decoration: none;">Enter</a>
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
                    <h1 class="page-title">Guest Invitations & Meeting Links</h1>
                    <p class="page-subtitle">Instant 1-click meeting links for partners, candidates, and external visitors.</p>
                </div>
                <button onclick="openInviteModal()" class="header-btn btn-success">
                    <span>⚡</span> Generate New Guest Link
                </button>
            </div>

            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Guest / Meeting Name</th>
                            <th>Target Room</th>
                            <th>Expiration</th>
                            <th>Join Link</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guestInvitations as $inv)
                            <tr>
                                <td>
                                    <strong style="color: #86efac;">👤 {{ $inv->guest_name }}</strong>
                                </td>
                                <td>🏢 {{ $inv->room->name ?? 'Main Conference' }}</td>
                                <td>{{ $inv->expires_at ? $inv->expires_at->diffForHumans() : '24h' }}</td>
                                <td>
                                    <code style="background: rgba(0,0,0,0.5); padding: 4px 8px; border-radius: 4px; font-size: 11px; color: #a5b4fc;">
                                        /guest/join/{{ substr($inv->token, 0, 16) }}...
                                    </code>
                                </td>
                                <td>
                                    <a href="{{ url('/guest/join/' . $inv->token) }}" target="_blank" class="header-btn btn-outline" style="padding: 4px 10px; font-size: 11px; text-decoration: none;">
                                        👁️ Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    No guest invitations generated yet. Click "Generate New Guest Link" to create one.
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
                    <h1 class="page-title">Departments & Teams</h1>
                    <p class="page-subtitle">Organize personnel and workspace room zoning.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                @forelse($departments as $dept)
                    <div class="card" style="margin-bottom: 0;">
                        <div style="font-size: 24px; margin-bottom: 8px;">🏛️</div>
                        <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 4px;">{{ $dept->name }}</h3>
                        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">{{ $dept->teams_count }} Teams assigned</p>
                        <span class="badge badge-purple">Active Department</span>
                    </div>
                @empty
                    <div class="card" style="grid-column: 1 / -1; text-align: center; color: var(--text-muted);">
                        Engineering, Product, and Design default departments initialized.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 6. AUDIT LOGS TAB -->
        <div id="tab-audit" class="tab-view">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Security & Audit Timeline</h1>
                    <p class="page-subtitle">Chronological record of organizational administrative actions.</p>
                </div>
            </div>

            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Resource</th>
                            <th>User ID</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
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
                    <h1 class="page-title">Workspace Settings</h1>
                    <p class="page-subtitle">Configure organization branding, security policies, and localization.</p>
                </div>
            </div>

            <div class="card" style="max-width: 600px;">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #94a3b8; margin-bottom: 6px; text-transform: uppercase;">Workspace Name</label>
                        <input type="text" value="{{ $organization->name }}" style="width: 100%; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: white; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #94a3b8; margin-bottom: 6px; text-transform: uppercase;">URL Slug</label>
                        <input type="text" value="{{ $organization->slug }}" readonly style="width: 100%; background: rgba(0,0,0,0.4); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: #818cf8; font-size: 13px; font-family: monospace;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #94a3b8; margin-bottom: 6px; text-transform: uppercase;">Timezone</label>
                        <input type="text" value="{{ $organization->timezone }}" style="width: 100%; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: white; font-size: 13px;">
                    </div>
                    <button class="header-btn btn-primary" style="margin-top: 10px; width: fit-content;">Save Workspace Changes</button>
                </div>
            </div>
        </div>

    </main>

    <!-- Invite Modal -->
    <div id="invite-modal" class="modal">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 700;">📨 Invite & Guest Access</h3>
                <button onclick="closeInviteModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
            </div>

            <!-- Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 20px; background: rgba(255,255,255,0.05); padding: 4px; border-radius: 10px;">
                <button onclick="switchInviteTab('guest')" id="tab-guest-btn" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; background: var(--accent-primary); color: white;">
                    🔗 Guest Meeting Link
                </button>
                <button onclick="switchInviteTab('member')" id="tab-member-btn" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; background: none; color: #94a3b8;">
                    👤 Team Member
                </button>
            </div>

            <!-- Guest Form -->
            <div id="guest-tab-content">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Destination Room</label>
                        <select id="invite-room-select" style="width: 100%; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: white; outline: none; font-size: 13px;">
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}" style="background: #0f172a;">🏢 {{ $r->name }} ({{ ucfirst($r->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Guest Name / Role</label>
                        <input type="text" id="invite-guest-name" value="Investor / Partner" placeholder="e.g. Sarah Miller" style="width: 100%; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: white; outline: none; font-size: 13px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Link Expiration</label>
                        <select id="invite-guest-hours" style="width: 100%; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: white; outline: none; font-size: 13px;">
                            <option value="1" style="background: #0f172a;">1 Hour</option>
                            <option value="12" style="background: #0f172a;">12 Hours</option>
                            <option value="24" selected style="background: #0f172a;">24 Hours (1 Day)</option>
                            <option value="72" style="background: #0f172a;">72 Hours (3 Days)</option>
                        </select>
                    </div>

                    <button onclick="generateGuestLink()" id="btn-generate-guest" style="margin-top: 6px; background: #10b981; color: white; font-weight: 700; border: none; border-radius: 10px; padding: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; box-shadow: 0 0 15px rgba(16, 185, 129, 0.35);">
                        <span>⚡</span> Generate Instant Guest Link
                    </button>

                    <div id="guest-result-box" style="display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px; padding: 12px; margin-top: 10px;">
                        <div style="font-size: 11px; font-weight: 700; color: #6ee7b7; text-transform: uppercase; margin-bottom: 6px;">✅ Invitation Link Ready!</div>
                        <input type="text" id="guest-link-output" readonly style="width: 100%; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 8px; color: white; font-size: 12px; font-family: monospace; margin-bottom: 8px;">
                        <div style="display: flex; gap: 8px;">
                            <button onclick="copyGuestLink()" id="btn-copy-link" style="flex: 1; background: #6366f1; color: white; font-weight: 600; border: none; border-radius: 6px; padding: 8px; cursor: pointer; font-size: 12px;">
                                📋 Copy Link
                            </button>
                            <a id="guest-open-link" href="#" target="_blank" style="background: rgba(255,255,255,0.1); color: white; font-weight: 600; text-decoration: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; display: flex; align-items: center;">
                                👁️ Open
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Form -->
            <div id="member-tab-content" style="display: none;">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Colleague Email Address</label>
                        <input type="email" id="invite-member-email" placeholder="colleague@company.com" style="width: 100%; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: white; outline: none; font-size: 13px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Role</label>
                        <select id="invite-member-role" style="width: 100%; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: white; outline: none; font-size: 13px;">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" style="background: #0f172a;">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button onclick="sendMemberInvite()" id="btn-send-member-invite" style="margin-top: 6px; background: #6366f1; color: white; font-weight: 700; border: none; border-radius: 10px; padding: 12px; cursor: pointer; font-size: 14px;">
                        📨 Send Invitation Email
                    </button>
                    <div id="member-invite-status" style="display: none; font-size: 12px; text-align: center; margin-top: 6px;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ORG_ID = "{{ $organization->id }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";

        function switchAdminTab(tabName) {
            document.querySelectorAll('.tab-view').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tab-btn').forEach(el => el.classList.remove('active'));

            const targetTab = document.getElementById(`tab-${tabName}`);
            if (targetTab) targetTab.classList.add('active');

            event?.target?.closest('.nav-tab-btn')?.classList.add('active');
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
    </script>
</body>
</html>
