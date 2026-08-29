    <script nonce="{{ $cspNonce ?? '' }}">
        const ORG_ID = "{{ $organization->id }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const ALL_TEAMS = @json($teams);

        // ── Theme Manager (Light / Dark / System) ──
        function applyTheme(theme) {
            let activeTheme = theme;
            if (theme === 'system') {
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                activeTheme = prefersDark ? 'dark' : 'light';
            }
            
            document.documentElement.setAttribute('data-theme', activeTheme);
            if (activeTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark-mode');
            }
            
            const isDark = activeTheme === 'dark';
            document.querySelectorAll('.theme-toggle-icon-label').forEach(el => {
                el.textContent = isDark ? '☀️' : '🌙';
            });
            localStorage.setItem('vw_theme', theme);
        }

        function toggleThemeMode() {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            showToastNotification(next === 'dark' ? '🌙 <strong>{{ __('Dark Spatial Workspace') }}</strong><br>{{ __('Deep calm green mode activated.') }}' : '☀️ <strong>{{ __('Light Natural Mode') }}</strong><br>{{ __('Warm ivory workspace activated.') }}');
        }

        // Initialize saved theme on load
        (function() {
            const savedTheme = localStorage.getItem('vw_theme') || 'light';
            applyTheme(savedTheme);
        })();

        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('dashboardSidebar');
            const mainContent = document.querySelector('.main-content');
            const toggleBtn = document.querySelector('.sidebar-toggle-btn');
            const isRtl = document.documentElement.dir === 'rtl' || '{{ app()->getLocale() }}' === 'ar';

            if (sidebar) sidebar.classList.toggle('sidebar-collapsed');
            if (mainContent) mainContent.classList.toggle('sidebar-collapsed');
            const isCollapsed = sidebar && sidebar.classList.contains('sidebar-collapsed');
            localStorage.setItem('vw_sidebar_collapsed', isCollapsed ? '1' : '0');

            if (toggleBtn) {
                if (isRtl) {
                    toggleBtn.textContent = isCollapsed ? '▶' : '◀';
                } else {
                    toggleBtn.textContent = isCollapsed ? '◀' : '▶';
                }
            }
        }

        // Mobile drawer toggle
        function toggleDashboardSidebar() {
            const sidebar = document.getElementById('dashboardSidebar');
            if (sidebar) {
                sidebar.classList.toggle('open');
            }
        }

        // Restore sidebar state on load
        if (localStorage.getItem('vw_sidebar_collapsed') === '1') {
            document.addEventListener('DOMContentLoaded', () => {
                const sidebar = document.getElementById('dashboardSidebar');
                const mainContent = document.querySelector('.main-content');
                const toggleBtn = document.querySelector('.sidebar-toggle-btn');
                const isRtl = document.documentElement.dir === 'rtl' || '{{ app()->getLocale() }}' === 'ar';

                if (sidebar) sidebar.classList.add('sidebar-collapsed');
                if (mainContent) mainContent.classList.add('sidebar-collapsed');
                if (toggleBtn) {
                    if (isRtl) {
                        toggleBtn.textContent = '▶';
                    } else {
                        toggleBtn.textContent = '◀';
                    }
                }
            });
        }

        function toggleSidebarSection(sectionId) {
            const sec = document.getElementById(sectionId);
            if (sec) {
                sec.classList.toggle('collapsed');
            }
        }

        function previewCompanyLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('logo-preview-img');
                    const placeholder = document.getElementById('logo-preview-placeholder');
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewUserAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('user-profile-preview-avatar');
                    const fallback = document.getElementById('user-profile-avatar-fallback');
                    const sidebarAvatar = document.getElementById('sidebar-user-avatar');
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                    }
                    if (fallback) {
                        fallback.style.display = 'none';
                    }
                    if (sidebarAvatar) {
                        sidebarAvatar.src = e.target.result;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function switchAdminTab(tabName, updateHash = true) {
            tabName = (tabName || 'overview').toLowerCase().trim();
            
            // Map common aliases
            if (tabName === 'kanban' || tabName === 'tasks') tabName = 'all-tasks';
            if (tabName === 'mytasks') tabName = 'my-tasks';
            if (tabName === 'company') tabName = 'settings';
            if (tabName === 'user-profile') tabName = 'profile';

            const targetTab = document.getElementById(`tab-${tabName}`) || document.getElementById('tab-overview');
            if (!targetTab) return;

            const finalTabName = targetTab.id.replace('tab-', '');

            // Deactivate all views and buttons
            document.querySelectorAll('.tab-view').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tab-btn').forEach(el => el.classList.remove('active'));

            // Activate target view
            targetTab.classList.add('active');
            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (updateHash && window.history && window.history.pushState) {
                window.history.pushState(null, null, '#' + finalTabName);
            }

            if (finalTabName === 'my-tasks' || finalTabName === 'all-tasks') {
                setTimeout(initDashboardSortableKanban, 80);
            }

            // Close mobile sidebar if open
            const sidebar = document.getElementById('dashboardSidebar');
            if (sidebar && window.innerWidth <= 900) {
                sidebar.classList.remove('open');
            }

            const breadcrumb = document.getElementById('current-tab-breadcrumb');
            if (breadcrumb) {
                breadcrumb.textContent = finalTabName.replace('-', ' ');
            }

            // Highlight corresponding sidebar button by ID or onclick match & expand parent accordion
            const directNavBtn = document.getElementById(`nav-btn-${finalTabName}`);
            if (directNavBtn) {
                directNavBtn.classList.add('active');
                const parentAccordion = directNavBtn.closest('.sidebar-accordion');
                if (parentAccordion && parentAccordion.classList.contains('collapsed')) {
                    parentAccordion.classList.remove('collapsed');
                }
            } else {
                document.querySelectorAll('.nav-tab-btn').forEach(btn => {
                    const onclickAttr = btn.getAttribute('onclick') || '';
                    if (onclickAttr.includes(`'${finalTabName}'`) || onclickAttr.includes(`"${finalTabName}"`)) {
                        btn.classList.add('active');
                        const parentAccordion = btn.closest('.sidebar-accordion');
                        if (parentAccordion && parentAccordion.classList.contains('collapsed')) {
                            parentAccordion.classList.remove('collapsed');
                        }
                    }
                });
            }

            const titles = {
                'overview': '{{ __('Dashboard') }}',
                'chat': '{{ __('Team Chat & Direct Messages') }}',
                'rooms': '{{ __('Rooms & Doors') }}',
                'members': '{{ __('People & Roles') }}',
                'meetings': '{{ __('Scheduled Meetings & Live Sessions') }}',
                'guests': '{{ __('Meetings & Guest Links') }}',
                'all-tasks': '{{ __('Tasks Manager') }}',
                'my-tasks': '{{ __('My Tasks') }}',
                'projects': '{{ __('Files & Projects') }}',
                'timesheets': '{{ __('Analytics & Timesheets') }}',
                'workload': '{{ __('Team Workload') }}',
                'departments': '{{ __('Departments & Teams') }}',
                'audit': '{{ __('Audit Logs') }}',
                'billing': '{{ __('Billing & Subscription') }}',
                'settings': '{{ __('Workspace Settings') }}',
                'profile': '{{ __('My User Profile') }}'
            };
            const subtitles = {
                'overview': '{{ __('Welcome to your virtual workspace') }}',
                'chat': '{{ __('Realtime company communication, direct colleague messaging, and team channels') }}',
                'rooms': '{{ __('Collaborative 2D & 3D space management') }}',
                'members': '{{ __('Team roster, departments, and permissions') }}',
                'meetings': '{{ __('Scheduled video rooms, attendee sync, and sound alerts') }}',
                'guests': '{{ __('Instant access links without authentication') }}',
                'all-tasks': '{{ __('Track sprints, milestones, and deliverables') }}',
                'my-tasks': '{{ __('Personal checklist and scheduled duties') }}',
                'projects': '{{ __('Shared assets and file repositories') }}',
                'timesheets': '{{ __('Presence trends and productivity tracking') }}',
                'workload': '{{ __('Capacity planning and resource distribution') }}',
                'departments': '{{ __('Organizational structure and hierarchy') }}',
                'audit': '{{ __('Realtime activity logs and security history') }}',
                'billing': '{{ __('Manage subscription tier and payment plans') }}',
                'settings': '{{ __('Workspace configuration and branding') }}',
                'profile': '{{ __('Personal details, hobbies, and security') }}'
            };

            const headerTitle = document.getElementById('page-primary-title');
            const headerSub = document.getElementById('page-primary-subtitle');
            if (headerTitle && titles[finalTabName]) headerTitle.textContent = titles[finalTabName];
            if (headerSub && subtitles[finalTabName]) headerSub.textContent = subtitles[finalTabName];

            if (finalTabName === 'chat' && typeof loadChatConversations === 'function') {
                loadChatConversations();
            }
            if (finalTabName === 'timesheets' && typeof refreshDailyTimesheet === 'function') {
                refreshDailyTimesheet();
            }
        }

        // Global Live Search Filter
        function handleGlobalSearch(query) {
            const q = query.toLowerCase().trim();
            if (!q) {
                document.querySelectorAll('.data-table tbody tr, .card, .kpi-card').forEach(el => el.style.display = '');
                return;
            }
            document.querySelectorAll('.data-table tbody tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }

        // Focus Mode Interactive Toggle
        let isFocusModeActive = false;
        function toggleFocusMode() {
            isFocusModeActive = !isFocusModeActive;
            const bannerBtn = document.querySelector('.focus-mode-banner button');
            const quickBtn = document.getElementById('quick-action-focus');
            
            if (isFocusModeActive) {
                showToastNotification('🌿 <strong>{{ __('Focus Mode Activated') }}</strong><br>{{ __('Notifications muted. Ambient productivity session in progress.') }}');
                if (bannerBtn) bannerBtn.textContent = '{{ __('Disable Focus Mode ✕') }}';
                if (quickBtn) quickBtn.style.background = 'linear-gradient(180deg, #1E4E31 0%, #163823 100%)';
            } else {
                showToastNotification('🌿 {{ __('Focus Mode Disabled. Welcome back!') }}');
                if (bannerBtn) bannerBtn.textContent = '{{ __('Enable Focus Mode →') }}';
                if (quickBtn) quickBtn.style.background = 'var(--accent-gradient)';
            }
        }

        // Fast Task Toggle from Overview
        async function toggleTaskDone(taskId, isDone) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: isDone ? 'done' : 'in_progress' })
                });
                if (res.ok) {
                    showToastNotification(isDone ? '✅ {{ __('Task completed!') }}' : '🔄 {{ __('Task reopened.') }}');
                }
            } catch(e) {
                console.error(e);
            }
        }

        // Auto-open tab from URL hash on load & popstate (e.g. /dashboard#projects or #all-tasks or #kanban)
        function initHashRouting() {
            let hash = window.location.hash.replace(/^#/, '').trim();
            if (hash === 'kanban') {
                hash = 'all-tasks';
                setTimeout(() => { if (typeof switchAllTasksView === 'function') switchAllTasksView('kanban'); }, 120);
            }
            if (hash && document.getElementById(`tab-${hash}`)) {
                switchAdminTab(hash, false);
            }
            setTimeout(initDashboardSortableKanban, 200);
        }

        window.addEventListener('hashchange', initHashRouting);
        window.addEventListener('popstate', initHashRouting);
        if (document.readyState !== 'loading') {
            initHashRouting();
        } else {
            document.addEventListener('DOMContentLoaded', initHashRouting);
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

        // ── Edit Member Modal ──
        function openEditMemberModal(memberId, name, email, deptId, teamId, roleId, jobTitle, status) {
            document.getElementById('edit-member-form').action = `/organization/members/${memberId}`;
            document.getElementById('edit-member-name-input').value = name || '';
            document.getElementById('edit-member-email-input').value = email || '';
            document.getElementById('edit-member-dept-select').value = deptId || '';
            filterTeamsForEditMember(deptId, teamId);
            document.getElementById('edit-member-job-title').value = jobTitle || '';
            if (roleId) {
                document.getElementById('edit-member-role-select').value = roleId;
            }
            if (status) {
                document.getElementById('edit-member-status-select').value = status;
            }

            // Reset checkboxes
            document.querySelectorAll('.edit-member-office-cb').forEach(cb => cb.checked = false);
            document.querySelectorAll('.edit-member-room-cb').forEach(cb => cb.checked = false);

            // Fetch dynamic member profile & allowed offices/rooms
            fetch(`/organization/members/${memberId}/details`)
                .then(r => r.json())
                .then(data => {
                    if (data.member) {
                        if (data.member.allowed_office_ids && Array.isArray(data.member.allowed_office_ids)) {
                            data.member.allowed_office_ids.forEach(id => {
                                const el = document.getElementById(`edit-office-${id}`);
                                if (el) el.checked = true;
                            });
                        }
                        if (data.member.allowed_room_ids && Array.isArray(data.member.allowed_room_ids)) {
                            data.member.allowed_room_ids.forEach(id => {
                                const el = document.getElementById(`edit-room-${id}`);
                                if (el) el.checked = true;
                            });
                        }
                    }
                })
                .catch(err => console.error('Error fetching member details:', err));

            document.getElementById('edit-member-modal').style.display = 'flex';
        }

        // ── Offices Modals ──
        function openNewOfficeModal() {
            document.getElementById('new-office-modal').style.display = 'flex';
        }
        function closeNewOfficeModal() {
            document.getElementById('new-office-modal').style.display = 'none';
        }
        function openEditOfficeModal(officeId, name, city, desc, isDefault) {
            document.getElementById('edit-office-form').action = `/offices/${officeId}`;
            document.getElementById('edit-office-name-input').value = name || '';
            document.getElementById('edit-office-city-input').value = city || '';
            document.getElementById('edit-office-desc-input').value = desc || '';
            document.getElementById('edit-office-default-input').checked = !!isDefault;
            document.getElementById('edit-office-modal').style.display = 'flex';
        }
        function closeEditOfficeModal() {
            document.getElementById('edit-office-modal').style.display = 'none';
        }

        function filterTeamsForEditMember(deptId, selectedTeamId = '') {
            const teamSelect = document.getElementById('edit-member-team-select');
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

        function filterTeamsForInvite(deptId) {
            const teamSelect = document.getElementById('invite-team-select');
            if (!teamSelect) return;
            teamSelect.innerHTML = '<option value="">— {{ __('No Team') }} —</option>';
            if (!deptId) return;

            const filtered = ALL_TEAMS.filter(t => t.department_id == deptId);
            filtered.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                teamSelect.appendChild(opt);
            });
        }

        function closeEditMemberModal() {
            document.getElementById('edit-member-modal').style.display = 'none';
        }

        // ── Change Password Modal ──
        function openChangeMemberPasswordModal(memberId, userName) {
            document.getElementById('change-member-password-form').action = `/organization/members/${memberId}/password`;
            document.getElementById('change-password-user-name').textContent = userName || 'User';
            document.getElementById('change-member-password-modal').style.display = 'flex';
        }

        function closeChangeMemberPasswordModal() {
            document.getElementById('change-member-password-modal').style.display = 'none';
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
            }, 3500);
        }

        /* ── Live Workplace Notification Center Client ── */
        let currentNotifications = [];
        let activeNotifFilter = 'all';
        let previousUnreadCount = 0;
        let isInitialNotifLoad = true;

        function playNotificationChime() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const now = audioCtx.currentTime;
                
                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now); // D5
                osc1.frequency.exponentialRampToValueAtTime(880, now + 0.12); // A5
                gain1.gain.setValueAtTime(0.08, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.start(now);
                osc1.stop(now + 0.35);
            } catch (e) {
                // AudioContext not allowed before user gesture
            }
        }

        function triggerDesktopNotification(notif) {
            if ('Notification' in window && Notification.permission === 'granted') {
                try {
                    const n = new Notification(notif.title || 'Workplace Notification', {
                        body: notif.body || '',
                        icon: '/favicon.ico',
                        badge: '/favicon.ico'
                    });
                    n.onclick = () => {
                        window.focus();
                        if (notif.action_url) window.location.href = notif.action_url;
                    };
                } catch (e) {
                    console.log('Desktop notification error:', e);
                }
            }
        }

        async function fetchUserNotifications() {
            try {
                const res = await fetch('/api/notifications', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const unreadCount = data.unread_count || 0;
                currentNotifications = data.notifications || [];

                // Update UI badge
                const badge = document.getElementById('notifBadge');
                const headerCount = document.getElementById('notifHeaderCount');

                if (badge) {
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }

                if (headerCount) {
                    if (unreadCount > 0) {
                        headerCount.textContent = `${unreadCount} {{ __('new') }}`;
                        headerCount.style.display = 'inline-flex';
                    } else {
                        headerCount.style.display = 'none';
                    }
                }

                // If new notifications arrived after initial load
                if (!isInitialNotifLoad && unreadCount > previousUnreadCount) {
                    playNotificationChime();
                    const newest = currentNotifications.find(n => !n.is_read) || currentNotifications[0];
                    if (newest) {
                        showToastNotification(`${newest.icon || '🔔'} <strong>${newest.title}</strong><br><small style="color: var(--text-muted);">${newest.body || ''}</small>`);
                        triggerDesktopNotification(newest);
                    }
                }

                previousUnreadCount = unreadCount;
                isInitialNotifLoad = false;
                renderNotificationsList();
            } catch (err) {
                // Silently handle polling errors
            }
        }

        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notifDropdown');
            if (!dropdown) return;
            const isOpen = dropdown.style.display === 'flex';
            dropdown.style.display = isOpen ? 'none' : 'flex';

            if (!isOpen) {
                // Request desktop notification permission on user interaction
                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission();
                }
                renderNotificationsList();
            }
        }

        function filterNotifTab(tab, btn) {
            activeNotifFilter = tab;
            document.querySelectorAll('.notif-tab-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            renderNotificationsList();
        }

        function renderNotificationsList() {
            const container = document.getElementById('notifListContainer');
            if (!container) return;

            let filtered = currentNotifications;
            if (activeNotifFilter === 'task') {
                filtered = currentNotifications.filter(n => n.type.startsWith('task'));
            } else if (activeNotifFilter === 'meeting') {
                filtered = currentNotifications.filter(n => n.type.startsWith('meeting'));
            } else if (activeNotifFilter === 'spatial') {
                filtered = currentNotifications.filter(n => n.type === 'door_knock' || n.type === 'wave' || n.type.startsWith('room'));
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="padding: 36px 18px; text-align: center; color: var(--text-muted);">
                        <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
                        <strong style="display: block; font-size: 13px; color: var(--text-primary); margin-bottom: 4px;">{{ __('All caught up!') }}</strong>
                        <span style="font-size: 12px;">{{ __('No notifications in this category.') }}</span>
                    </div>
                `;
                return;
            }

            container.innerHTML = '';
            filtered.forEach(n => {
                const item = document.createElement('div');
                item.className = `notif-item ${n.is_read ? '' : 'unread'}`;
                item.onclick = () => handleNotificationClick(n);

                item.innerHTML = `
                    <div class="notif-icon-box">${n.icon || '🔔'}</div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px; margin-bottom: 2px;">
                            <strong style="font-size: 12px; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${n.title}</strong>
                            <span style="font-size: 10px; color: var(--text-muted); flex-shrink: 0;">${n.created_at_human || ''}</span>
                        </div>
                        <p style="font-size: 11px; color: var(--text-secondary); margin: 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${n.body || ''}</p>
                    </div>
                    ${!n.is_read ? '<div class="notif-unread-dot"></div>' : ''}
                `;
                container.appendChild(item);
            });
        }

        async function handleNotificationClick(notif) {
            if (!notif.is_read) {
                try {
                    await fetch(`/api/notifications/${notif.id}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    notif.is_read = true;
                    fetchUserNotifications();
                } catch (e) {}
            }

            if (notif.action_url) {
                window.location.href = notif.action_url;
            }
        }

        async function markAllNotificationsAsRead() {
            try {
                await fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                currentNotifications.forEach(n => n.is_read = true);
                fetchUserNotifications();
            } catch (e) {}
        }

        async function clearAllNotificationsFromServer() {
            if (!confirm('{{ __("Clear all notifications?") }}')) return;
            try {
                await fetch('/api/notifications/clear', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                currentNotifications = [];
                fetchUserNotifications();
            } catch (e) {}
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const wrapper = document.getElementById('notifWrapper');
            const dropdown = document.getElementById('notifDropdown');
            if (wrapper && dropdown && !wrapper.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Initialize notification polling
        document.addEventListener('DOMContentLoaded', () => {
            fetchUserNotifications();
            setInterval(fetchUserNotifications, 15000);
        });

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

        function onInviteRoomSelected(sel) {
            if (!sel) return;
            const opt = sel.options[sel.selectedIndex];
            if (!opt) return;
            const isDefault = opt.getAttribute('data-is-default') === '1';
            const floorName = opt.getAttribute('data-floor-name') || '';
            const warningBox = document.getElementById('invite-room-branch-warning');
            const warningText = document.getElementById('invite-room-warning-text');
            if (warningBox && warningText) {
                if (!isDefault) {
                    warningBox.style.display = 'block';
                    warningText.textContent = `{{ __('This room belongs to branch') }} "${floorName}" {{ __('which is different from your current default team branch. To meet your guest, please switch to this branch.') }}`;
                } else {
                    warningBox.style.display = 'none';
                }
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

        // ── ALL TASKS & KANBAN BOARD CONTROLLER ──
        function switchAllTasksView(view) {
            const tblView = document.getElementById('alltasks-view-table');
            const knbView = document.getElementById('alltasks-view-kanban');
            const tblBtn = document.getElementById('alltasks-btn-table');
            const knbBtn = document.getElementById('alltasks-btn-kanban');

            if (view === 'table') {
                if (tblView) tblView.style.display = 'block';
                if (knbView) knbView.style.display = 'none';
                if (tblBtn) {
                    tblBtn.className = 'tactile-btn btn-primary';
                    tblBtn.style = 'padding: 7px 14px; font-size: 12px;';
                }
                if (knbBtn) {
                    knbBtn.className = 'tactile-btn btn-secondary';
                    knbBtn.style = 'padding: 7px 14px; font-size: 12px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);';
                }
                localStorage.setItem('alltasks_view', 'table');
            } else {
                if (tblView) tblView.style.display = 'none';
                if (knbView) knbView.style.display = 'block';
                if (knbBtn) {
                    knbBtn.className = 'tactile-btn btn-primary';
                    knbBtn.style = 'padding: 7px 14px; font-size: 12px;';
                }
                if (tblBtn) {
                    tblBtn.className = 'tactile-btn btn-secondary';
                    tblBtn.style = 'padding: 7px 14px; font-size: 12px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);';
                }
                localStorage.setItem('alltasks_view', 'kanban');
            }
            filterAllTasksTable();
        }

        // Restore view preference from localStorage
        (function() {
            const savedView = localStorage.getItem('alltasks_view');
            if (savedView === 'kanban') {
                setTimeout(() => switchAllTasksView('kanban'), 100);
            }
        })();

        function filterAllTasksTable() {
            const query = (document.getElementById('alltasks-filter-search')?.value || '').toLowerCase().trim();
            const proj = document.getElementById('alltasks-filter-project')?.value || '';
            const status = document.getElementById('alltasks-filter-status')?.value || '';
            const priority = document.getElementById('alltasks-filter-priority')?.value || '';
            const assignee = document.getElementById('alltasks-filter-assignee')?.value || '';

            // 1. Filter Table Rows
            const rows = document.querySelectorAll('.alltask-row');
            let visibleCount = 0;

            rows.forEach(r => {
                const title = (r.dataset.title || '').toLowerCase();
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

            // 2. Filter Global Kanban Cards & Update Column Counters
            const colCounts = { backlog: 0, ready: 0, in_progress: 0, review: 0, done: 0 };
            const cards = document.querySelectorAll('.global-kanban-card');

            cards.forEach(card => {
                const title = (card.dataset.title || '').toLowerCase();
                const cProj = card.dataset.projectId || '';
                let cStatus = card.dataset.status || '';
                if (cStatus === 'qa') cStatus = 'review';
                const cPriority = card.dataset.priority || '';
                const cAssignee = card.dataset.assigneeId || '';

                const matchesQuery = !query || title.includes(query);
                const matchesProj = !proj || cProj === proj;
                const matchesStatus = !status || cStatus === status;
                const matchesPriority = !priority || cPriority === priority;
                const matchesAssignee = !assignee || cAssignee === assignee;

                if (matchesQuery && matchesProj && matchesStatus && matchesPriority && matchesAssignee) {
                    card.style.display = 'block';
                    if (colCounts[cStatus] !== undefined) {
                        colCounts[cStatus]++;
                    }
                } else {
                    card.style.display = 'none';
                }
            });

            // Update column count badges
            Object.keys(colCounts).forEach(st => {
                const badge = document.getElementById(`global-kanban-cnt-${st}`);
                if (badge) badge.textContent = colCounts[st];
            });
        }

        // Global Drag & Drop Engine
        let globalDraggedTaskId = null;

        function handleGlobalDragStart(e, taskId) {
            globalDraggedTaskId = taskId;
            e.dataTransfer.setData('text/plain', taskId);
            e.dataTransfer.effectAllowed = 'move';
            const card = document.getElementById(`global-kanban-card-${taskId}`);
            if (card) card.classList.add('is-dragging');
        }

        function handleGlobalDragEnd(e) {
            document.querySelectorAll('.global-kanban-card').forEach(c => c.classList.remove('is-dragging'));
            document.querySelectorAll('.kanban-column').forEach(col => col.classList.remove('drag-over'));
        }

        function handleGlobalDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const col = e.currentTarget;
            if (col && !col.classList.contains('drag-over')) {
                col.classList.add('drag-over');
            }
        }

        function handleGlobalDragLeave(e) {
            const col = e.currentTarget;
            if (col) col.classList.remove('drag-over');
        }

        async function handleGlobalDrop(e, targetStatus) {
            e.preventDefault();
            const col = e.currentTarget;
            if (col) col.classList.remove('drag-over');

            const taskId = e.dataTransfer.getData('text/plain') || globalDraggedTaskId;
            if (!taskId) return;

            const card = document.getElementById(`global-kanban-card-${taskId}`);
            if (!card) return;

            const oldStatus = card.dataset.status;
            if (oldStatus === targetStatus) return;

            // Optimistic DOM relocation
            const targetContainer = document.getElementById(`global-kanban-col-${targetStatus}`);
            if (targetContainer) {
                const emptyHint = targetContainer.querySelector('.kanban-empty-hint');
                if (emptyHint) emptyHint.remove();
                targetContainer.appendChild(card);
            }

            card.dataset.status = targetStatus;
            const cardSelect = card.querySelector('select');
            if (cardSelect) cardSelect.value = targetStatus;

            // Update matching row in Table view
            const matchingRow = document.querySelector(`.alltask-row[data-id="${taskId}"]`);
            if (matchingRow) {
                matchingRow.dataset.status = targetStatus;
                const rowSelect = matchingRow.querySelector('select');
                if (rowSelect) rowSelect.value = targetStatus;
            }

            filterAllTasksTable();

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: targetStatus })
                });
                if (!res.ok) {
                    throw new Error('Failed to update task');
                }
                showToastNotification('✅ ' + "{{ __('Task status updated successfully!') }}");
            } catch (err) {
                console.error(err);
                alert('Failed to save task status on server.');
                window.location.reload();
            }
        }

        async function updateTaskStatusDirect(taskId, newStatus) {
            // 1. Optimistically update My Tasks Kanban card
            const myCard = document.getElementById(`mytasks-card-${taskId}`);
            if (myCard) {
                const targetMyCol = document.getElementById(`mytasks-kanban-col-${newStatus}`);
                if (targetMyCol) {
                    const emptyHint = targetMyCol.querySelector('.mytasks-empty-hint');
                    if (emptyHint) emptyHint.remove();
                    targetMyCol.appendChild(myCard);
                }
                myCard.dataset.status = newStatus;
                myCard.setAttribute('data-status', newStatus);
                const mySelect = myCard.querySelector('select');
                if (mySelect) mySelect.value = newStatus;

                // Update My Tasks column count badges
                ['backlog', 'ready', 'in_progress', 'review', 'done'].forEach(st => {
                    const col = document.getElementById(`mytasks-kanban-col-${st}`);
                    const badge = document.getElementById(`mytasks-kanban-cnt-${st}`);
                    if (col && badge) {
                        const cnt = col.querySelectorAll('.kanban-task-card').length;
                        badge.textContent = cnt;
                        if (cnt === 0 && !col.querySelector('.mytasks-empty-hint')) {
                            const hint = document.createElement('div');
                            hint.className = 'mytasks-empty-hint';
                            hint.id = `mytasks-empty-${st}`;
                            hint.style.cssText = 'text-align: center; padding: 18px 8px; color: var(--text-muted); font-size: 11px; border: 1px dashed var(--border-color); border-radius: var(--radius-md);';
                            hint.textContent = "{{ __('No tasks in this stage.') }}";
                            col.appendChild(hint);
                        }
                    }
                });

                // Update My Tasks Nav Badge in sidebar
                const myNonDoneCount = document.querySelectorAll('#tab-my-tasks .kanban-task-card:not([data-status="done"])').length;
                const myNavBadge = document.querySelector('#nav-btn-my-tasks .nav-badge-pill');
                if (myNavBadge) myNavBadge.textContent = myNonDoneCount;
            }

            // 2. Optimistically update All Tasks Global Kanban card
            const globalCard = document.getElementById(`global-kanban-card-${taskId}`);
            if (globalCard) {
                const targetContainer = document.getElementById(`global-kanban-col-${newStatus}`);
                if (targetContainer) {
                    const emptyHint = targetContainer.querySelector('.kanban-empty-hint');
                    if (emptyHint) emptyHint.remove();
                    targetContainer.appendChild(globalCard);
                }
                globalCard.dataset.status = newStatus;
                globalCard.setAttribute('data-status', newStatus);
                const cardSelect = globalCard.querySelector('select');
                if (cardSelect) cardSelect.value = newStatus;
            }

            // 3. Update Table View Row if present
            const matchingRow = document.querySelector(`.alltask-row[data-id="${taskId}"]`);
            if (matchingRow) {
                matchingRow.dataset.status = newStatus;
                const rowSelect = matchingRow.querySelector('select');
                if (rowSelect) rowSelect.value = newStatus;
            }

            filterAllTasksTable();

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
                showToastNotification('✅ ' + "{{ __('Task status updated successfully!') }}");
            } catch (e) {
                alert('Network error updating task.');
            }
        }

        // ── SORTABLEJS KANBAN INITIALIZER (MY TASKS & ALL TASKS) ──
        function initDashboardSortableKanban() {
            if (typeof Sortable === 'undefined') return;

            // 1. My Tasks Kanban Columns
            ['backlog', 'ready', 'in_progress', 'review', 'done'].forEach(st => {
                const col = document.getElementById(`mytasks-kanban-col-${st}`);
                if (col && !col._sortable) {
                    col._sortable = new Sortable(col, {
                        group: 'mytasks-kanban',
                        animation: 180,
                        ghostClass: 'kanban-card-ghost',
                        chosenClass: 'kanban-card-chosen',
                        dragClass: 'kanban-card-drag',
                        draggable: '.kanban-task-card',
                        onEnd: function(evt) {
                            const item = evt.item;
                            const toCol = evt.to;
                            const targetStatus = toCol.getAttribute('data-status');
                            const taskId = item.getAttribute('data-id');
                            const oldStatus = item.getAttribute('data-status');
                            if (taskId && targetStatus && targetStatus !== oldStatus) {
                                updateTaskStatusDirect(taskId, targetStatus);
                            }
                        }
                    });
                }
            });

            // 2. All Tasks Global Kanban Columns
            ['backlog', 'ready', 'in_progress', 'review', 'done'].forEach(st => {
                const col = document.getElementById(`global-kanban-col-${st}`);
                if (col && !col._sortable) {
                    col._sortable = new Sortable(col, {
                        group: 'global-kanban',
                        animation: 180,
                        ghostClass: 'kanban-card-ghost',
                        chosenClass: 'kanban-card-chosen',
                        dragClass: 'kanban-card-drag',
                        draggable: '.global-kanban-card',
                        onEnd: function(evt) {
                            const item = evt.item;
                            const toCol = evt.to;
                            const targetStatus = toCol.getAttribute('data-status') || toCol.id.replace('global-kanban-col-', '');
                            const taskId = item.getAttribute('data-id') || item.id.replace('global-kanban-card-', '');
                            const oldStatus = item.getAttribute('data-status');
                            if (taskId && targetStatus && targetStatus !== oldStatus) {
                                updateTaskStatusDirect(taskId, targetStatus);
                            }
                        }
                    });
                }
            });
        }

        // ── TASK CONTEXT MENU ENGINE (CLICKUP-PARITY) ──
        let activeCtxTaskId = null;
        let activeCtxProjectId = null;
        let activeCtxTaskTitle = '';

        function openTaskContextMenu(e, taskId, projectId, taskTitle) {
            activeCtxTaskId = taskId;
            activeCtxProjectId = projectId;
            activeCtxTaskTitle = taskTitle;

            const menu = document.getElementById('task-context-menu');
            if (!menu) return;

            menu.style.display = 'flex';

            // Calculate coordinate positioning
            let x = e.clientX || (e.target ? e.target.getBoundingClientRect().left : 200);
            let y = e.clientY || (e.target ? e.target.getBoundingClientRect().bottom : 200);

            const menuWidth = 250;
            const menuHeight = 330;

            if (x + menuWidth > window.innerWidth - 10) {
                x = window.innerWidth - menuWidth - 14;
            }
            if (y + menuHeight > window.innerHeight - 10) {
                y = window.innerHeight - menuHeight - 14;
            }
            if (x < 10) x = 10;
            if (y < 10) y = 10;

            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
        }

        function closeTaskContextMenu() {
            const menu = document.getElementById('task-context-menu');
            if (menu) menu.style.display = 'none';
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#task-context-menu')) {
                closeTaskContextMenu();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeTaskContextMenu();
        });

        function ctxActionCopyLink() {
            closeTaskContextMenu();
            const link = `${window.location.origin}/projects/hub/${activeCtxProjectId}?task=${activeCtxTaskId}`;
            executeClipboardCopy(link);
            showToastNotification('📋 ' + "{{ __('Task link copied to clipboard!') }}");
        }

        function ctxActionCopyId() {
            closeTaskContextMenu();
            executeClipboardCopy('#' + activeCtxTaskId);
            showToastNotification('📋 ' + "{{ __('Task ID copied to clipboard!') }}");
        }

        function ctxActionOpenNewTab() {
            closeTaskContextMenu();
            window.open(`/projects/hub/${activeCtxProjectId}?task=${activeCtxTaskId}`, '_blank');
        }

        function ctxActionInspect() {
            closeTaskContextMenu();
            openTaskDetails(activeCtxTaskId);
        }

        function ctxActionStartTimer() {
            closeTaskContextMenu();
            startTaskTimer(activeCtxProjectId, activeCtxTaskId, activeCtxTaskTitle, 'Project');
        }

        async function ctxActionDuplicate() {
            closeTaskContextMenu();
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeCtxTaskId}/duplicate`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error duplicating task.');
                    return;
                }
                showToastNotification('📋 ' + "{{ __('Task duplicated successfully!') }}");
                setTimeout(() => window.location.reload(), 600);
            } catch (err) {
                alert('Network error duplicating task.');
            }
        }

        function ctxActionOpenMoveModal() {
            closeTaskContextMenu();
            document.getElementById('move-task-id-input').value = activeCtxTaskId;
            document.getElementById('move-target-project-select').value = activeCtxProjectId;
            document.getElementById('move-task-modal').style.display = 'flex';
        }

        function closeMoveTaskModal() {
            document.getElementById('move-task-modal').style.display = 'none';
        }

        async function submitMoveTask(e) {
            e.preventDefault();
            const taskId = document.getElementById('move-task-id-input').value;
            const targetProjId = document.getElementById('move-target-project-select').value;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/move`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ project_id: targetProjId })
                });
                if (!res.ok) {
                    alert('Error moving task.');
                    return;
                }
                closeMoveTaskModal();
                showToastNotification('➡️ ' + "{{ __('Task moved successfully!') }}");
                setTimeout(() => window.location.reload(), 600);
            } catch (err) {
                alert('Network error moving task.');
            }
        }

        function ctxActionInspectCustomFields() {
            closeTaskContextMenu();
            openTaskDetails(activeCtxTaskId);
        }

        function ctxActionInspectDependencies() {
            closeTaskContextMenu();
            openTaskDetails(activeCtxTaskId);
        }

        async function ctxActionDelete() {
            closeTaskContextMenu();
            if (!confirm('{{ __('Are you sure you want to delete this task?') }}')) return;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeCtxTaskId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error deleting task.');
                    return;
                }
                const card = document.getElementById(`global-kanban-card-${activeCtxTaskId}`);
                if (card) card.remove();
                const row = document.querySelector(`.alltask-row[data-id="${activeCtxTaskId}"]`);
                if (row) row.remove();
                filterAllTasksTable();
                showToastNotification('🗑️ ' + "{{ __('Task deleted.') }}");
            } catch (err) {
                alert('Network error deleting task.');
            }
        }

        function ctxActionPermissions() {
            closeTaskContextMenu();
            showToastNotification('🔒 <strong>' + "{{ __('Sharing & Permissions') }}" + '</strong>: ' + "{{ __('Inherited from Project Role Settings') }}");
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
            ['details', 'checklist', 'attachments', 'comments', 'dependencies', 'timelog'].forEach(t => {
                const view = document.getElementById(`task-inspector-${t}`);
                const btn = document.getElementById(`task-tab-btn-${t}`);
                if (view) view.style.display = (t === tab) ? 'block' : 'none';
                if (btn) {
                    btn.className = (t === tab) ? 'tactile-btn btn-primary' : 'tactile-btn btn-secondary';
                    btn.style.background = (t === tab) ? '' : 'transparent';
                    btn.style.border = (t === tab) ? '' : 'none';
                    btn.style.boxShadow = (t === tab) ? '' : 'none';
                    btn.style.color = (t === tab) ? '' : 'var(--text-secondary)';
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
                const res = await fetch(`/tasks/${activeInspectorTaskId}/comments`, {
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

        function insertMentionHandle(name) {
            const input = document.getElementById('new-comment-body-input');
            if (!input) return;
            input.value += (input.value ? ' ' : '') + '@' + name + ' ';
            input.focus();
        }

        async function uploadTaskAttachmentSubmit(e) {
            e.preventDefault();
            const fileInput = document.getElementById('task-file-input');
            if (!fileInput || !fileInput.files.length || !activeInspectorTaskId) return;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            try {
                const res = await fetch(`/tasks/${activeInspectorTaskId}/attachments`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error uploading file.');
                    return;
                }
                fileInput.value = '';
                showToastNotification('📎 ' + "{{ __('Attachment uploaded successfully!') }}");
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error uploading attachment.');
            }
        }

        async function deleteTaskAttachmentAction(attachmentId) {
            if (!confirm('{{ __("Are you sure you want to delete this attachment?") }}')) return;
            try {
                const res = await fetch(`/tasks/${activeInspectorTaskId}/attachments/${attachmentId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error deleting attachment.');
                    return;
                }
                showToastNotification('🗑️ ' + "{{ __('Attachment removed.') }}");
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error deleting attachment.');
            }
        }

        async function quickApproveTask(taskId) {
            try {
                const res = await fetch(`/tasks/${taskId}/approve`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error approving task.');
                    return;
                }
                showToastNotification('🎉 ' + "{{ __('Task approved and marked as Completed!') }}");
                if (activeInspectorTaskId === taskId) {
                    openTaskDetails(taskId);
                } else {
                    window.location.reload();
                }
            } catch (err) {
                alert('Network error approving task.');
            }
        }

        async function quickRejectTask(taskId) {
            const reason = prompt('{{ __("Please enter a note / feedback on required changes:") }}');
            if (!reason) return;

            try {
                const res = await fetch(`/tasks/${taskId}/reject`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ rejection_reason: reason })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error rejecting task.');
                    return;
                }
                showToastNotification('⚠️ ' + "{{ __('Task returned to in-progress with feedback.') }}");
                if (activeInspectorTaskId === taskId) {
                    openTaskDetails(taskId);
                } else {
                    window.location.reload();
                }
            } catch (err) {
                alert('Network error requesting changes.');
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
            const statusLabels = {
                'backlog': '{{ __("Backlog") }}',
                'ready': '{{ __("Ready") }}',
                'in_progress': '{{ __("In Progress") }}',
                'review': '{{ __("In Review / QA") }}',
                'done': '{{ __("Done") }}'
            };
            const priorityLabels = {
                'low': '{{ __("Low") }}',
                'medium': '{{ __("Medium") }}',
                'high': '{{ __("High") }}',
                'urgent': '{{ __("Urgent") }}'
            };

            // Header
            document.getElementById('task-modal-code').textContent = `#${t.task_number || 1}`;
            document.getElementById('task-modal-title').textContent = t.title;
            document.getElementById('task-modal-status-badge').textContent = statusLabels[t.status] || (t.status || 'backlog');
            document.getElementById('task-modal-priority-badge').textContent = priorityLabels[t.priority] || (t.priority || 'medium');
            document.getElementById('task-modal-project').textContent = t.project ? t.project.name : '{{ __("General") }}';
            document.getElementById('task-modal-assignee').textContent = t.assignee ? t.assignee.name : '{{ __("Unassigned") }}';
            
            window.currentModalTaskAssigneeMemberId = null;
            if (t.assignee) {
                if (t.assignee.member_id) {
                    window.currentModalTaskAssigneeMemberId = t.assignee.member_id;
                } else if (typeof cachedChatMembers !== 'undefined' && cachedChatMembers.length) {
                    const matched = cachedChatMembers.find(m => m.user_id == t.assignee.id);
                    if (matched) window.currentModalTaskAssigneeMemberId = matched.id;
                }
            }

            document.getElementById('task-modal-due').textContent = t.due_date ? new Date(t.due_date).toLocaleDateString() : '—';
            document.getElementById('task-modal-status-select').value = t.status || 'backlog';
            document.getElementById('task-modal-description').textContent = t.description || '{{ __("No description provided.") }}';
            document.getElementById('task-modal-hours').textContent = `${t.estimated_hours || 0} {{ __("Estimated Hours") }} / ${t.actual_hours || 0} {{ __("Logged Hours") }}`;

            // Approval Banner logic
            const appBanner = document.getElementById('task-modal-approval-banner');
            const appText = document.getElementById('task-modal-approval-text');
            const appActions = document.getElementById('task-modal-approval-actions');
            if (appBanner && appText && appActions) {
                if (t.approval_status === 'pending_approval') {
                    appBanner.style.display = 'flex';
                    appBanner.style.background = 'rgba(214, 162, 58, 0.15)';
                    appBanner.style.border = '1px solid rgba(214, 162, 58, 0.35)';
                    appBanner.style.color = '#D6A23A';
                    appText.innerHTML = '<span>⏳</span> <span>{{ __("This task is submitted for completion and awaiting PM approval.") }}</span>';
                    appActions.innerHTML = `
                        <button type="button" onclick="quickApproveTask('${t.id}')" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: 11px;">✓ {{ __("Approve") }}</button>
                        <button type="button" onclick="quickRejectTask('${t.id}')" class="tactile-btn" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 6px 12px; font-size: 11px;">✕ {{ __("Request Changes") }}</button>
                    `;
                } else if (t.approval_status === 'rejected') {
                    appBanner.style.display = 'flex';
                    appBanner.style.background = 'rgba(217, 107, 95, 0.15)';
                    appBanner.style.border = '1px solid rgba(217, 107, 95, 0.35)';
                    appBanner.style.color = '#D96B5F';
                    appText.innerHTML = `<span>⚠️</span> <span><strong>{{ __("Changes Requested:") }}</strong> ${t.rejection_reason || '{{ __("Please review feedback.") }}'}</span>`;
                    appActions.innerHTML = '';
                } else if (t.approval_status === 'approved') {
                    appBanner.style.display = 'flex';
                    appBanner.style.background = 'rgba(79, 155, 95, 0.15)';
                    appBanner.style.border = '1px solid rgba(79, 155, 95, 0.35)';
                    appBanner.style.color = '#4F9B5F';
                    appText.innerHTML = '<span>✅</span> <span>{{ __("Task approved and marked Done by Project Manager.") }}</span>';
                    appActions.innerHTML = '';
                } else {
                    appBanner.style.display = 'none';
                }
            }

            // Timer Button
            const timerBtn = document.getElementById('task-modal-timer-btn');
            if (timerBtn) {
                const pId = t.project_id || '';
                const pName = t.project ? t.project.name : '{{ __("Project") }}';
                timerBtn.onclick = () => startTaskTimer(pId, t.id, t.title, pName);
            }

            // Checklist
            const items = t.checklist_items || [];
            document.getElementById('task-checklist-count').textContent = items.length;
            const checkContainer = document.getElementById('task-checklist-items-container');
            if (checkContainer) {
                checkContainer.innerHTML = '';
                if (items.length === 0) {
                    checkContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px;">{{ __("No checklist items yet. Add sub-items above.") }}</div>';
                } else {
                    items.forEach(item => {
                        const div = document.createElement('div');
                        div.style = 'display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color);';
                        div.innerHTML = `
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: var(--text-primary); text-decoration: ${item.is_completed ? 'line-through' : 'none'}; opacity: ${item.is_completed ? 0.6 : 1};">
                                <input type="checkbox" onchange="toggleTaskChecklistItem('${item.id}')" ${item.is_completed ? 'checked' : ''}>
                                <span>${item.title}</span>
                            </label>
                            <span class="badge ${item.is_completed ? 'badge-green' : 'badge-gray'}" style="font-size: 10px;">${item.is_completed ? '{{ __("Done") }}' : '{{ __("Pending") }}'}</span>
                        `;
                        checkContainer.appendChild(div);
                    });
                }
            }

            // Attachments
            const attachments = t.attachments || [];
            const attCountEl = document.getElementById('task-attachments-count');
            if (attCountEl) attCountEl.textContent = attachments.length;
            const attContainer = document.getElementById('task-attachments-list-container');
            if (attContainer) {
                attContainer.innerHTML = '';
                if (attachments.length === 0) {
                    attContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px; grid-column: 1 / -1;">{{ __("No files attached to this task.") }}</div>';
                } else {
                    attachments.forEach(att => {
                        const card = document.createElement('div');
                        card.style = 'background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; display: flex; flex-direction: column; justify-content: space-between; gap: 6px;';
                        const uploader = att.user ? att.user.name : '{{ __("Member") }}';
                        card.innerHTML = `
                            <div style="font-weight: 800; font-size: 12px; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">📄 ${att.file_name}</div>
                            <div style="font-size: 10px; color: var(--text-muted);">👤 ${uploader} • ${(att.file_size / 1024).toFixed(1)} KB</div>
                            <div style="display: flex; gap: 6px; margin-top: 4px;">
                                <a href="${att.file_url || ('/uploads/tasks/' + t.id + '/' + att.file_name)}" target="_blank" download class="tactile-btn btn-secondary" style="flex: 1; padding: 4px 8px; font-size: 10px; text-align: center; text-decoration: none;">⬇ {{ __("Download") }}</a>
                                <button type="button" onclick="deleteTaskAttachmentAction('${att.id}')" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 4px 8px; font-size: 10px;">🗑️</button>
                            </div>
                        `;
                        attContainer.appendChild(card);
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
                    commContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px;">{{ __("No discussions or comments yet.") }}</div>';
                } else {
                    comments.forEach(c => {
                        const box = document.createElement('div');
                        box.style = 'background: var(--bg-surface-subtle); padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px;';
                        const author = c.user ? c.user.name : '{{ __("Member") }}';
                        const time = new Date(c.created_at).toLocaleString();
                        box.innerHTML = `
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 11px;">
                                <strong style="color: var(--brand-forest);">👤 ${author}</strong>
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
                    depContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px;">{{ __("No blocker dependencies. This task can be started immediately.") }}</div>';
                } else {
                    deps.forEach(d => {
                        const item = document.createElement('div');
                        item.style = 'background: var(--bg-surface-subtle); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; display: flex; justify-content: space-between; align-items: center;';
                        const depTask = d.depends_on_task || {};
                        item.innerHTML = `
                            <span>🔒 <strong>{{ __("Depends On:") }}</strong> #${depTask.task_number || ''} ${depTask.title || '{{ __("Predecessor Task") }}'}</span>
                            <span class="badge ${depTask.status === 'done' ? 'badge-green' : 'badge-crimson'}">${statusLabels[depTask.status] || (depTask.status || 'pending')}</span>
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
                    timeBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 14px; color: var(--text-muted);">{{ __("No time tracked on this task yet.") }}</td></tr>';
                } else {
                    entries.forEach(e => {
                        const tr = document.createElement('tr');
                        const hrs = (e.duration_seconds / 3600).toFixed(2);
                        tr.innerHTML = `
                            <td>${new Date(e.started_at).toLocaleDateString()}</td>
                            <td style="font-weight: 700;">${e.user ? e.user.name : '{{ __("Member") }}'}</td>
                            <td style="font-weight: 800; color: var(--brand-forest); font-family: monospace;">${hrs} {{ __("h") }}</td>
                            <td style="font-size: 11px;">${e.description || '{{ __("Work session") }}'}</td>
                            <td><span class="badge ${e.status === 'approved' ? 'badge-green' : 'badge-gray'}">${e.status === 'approved' ? '{{ __("Approved") }}' : '{{ __("Pending") }}'}</span></td>
                        `;
                        timeBody.appendChild(tr);
                    });
                }
            }
        }

        // ==========================================
        // SCHEDULED MEETINGS & SOUND ALERT ENGINE
        // ==========================================
        const upcomingMeetingsList = {!! json_encode($upcomingMeetingsJson ?? []) !!};

        const projectMembersMap = @json($projectMembersMap ?? []);

        function openScheduleMeetingModal(scope = 'general', projectId = null) {
            const modal = document.getElementById('schedule-meeting-modal');
            if (!modal) return;

            // Set default date-time to now + 30 mins
            const now = new Date();
            now.setMinutes(now.getMinutes() + 30);
            const isoLocal = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            const dtInput = document.getElementById('meeting-scheduled-at-input');
            if (dtInput) dtInput.value = isoLocal;

            toggleMeetingScope(scope);

            if (projectId) {
                const projSelect = document.getElementById('meeting-project-select');
                if (projSelect) {
                    projSelect.value = projectId;
                    renderProjectAttendeesList(projectId);
                }
            }

            modal.style.display = 'flex';
        }

        function closeScheduleMeetingModal() {
            const modal = document.getElementById('schedule-meeting-modal');
            if (modal) modal.style.display = 'none';
        }

        function toggleMeetingScope(scope) {
            const isProject = scope === 'project';
            const projField = document.getElementById('meeting-project-field');
            const genField = document.getElementById('meeting-general-attendees-field');
            const lblGeneral = document.getElementById('lbl-scope-general');
            const lblProject = document.getElementById('lbl-scope-project');
            const radioGen = document.querySelector('input[name="scope"][value="general"]');
            const radioProj = document.querySelector('input[name="scope"][value="project"]');

            if (radioGen) radioGen.checked = !isProject;
            if (radioProj) radioProj.checked = isProject;

            if (projField) projField.style.display = isProject ? 'block' : 'none';
            if (genField) genField.style.display = isProject ? 'none' : 'block';

            if (isProject) {
                const projSelect = document.getElementById('meeting-project-select');
                if (projSelect && projSelect.value) {
                    renderProjectAttendeesList(projSelect.value);
                }
            }

            if (lblGeneral && lblProject) {
                if (isProject) {
                    lblProject.style.background = 'var(--bg-surface)';
                    lblProject.style.color = 'var(--brand-forest)';
                    lblProject.style.boxShadow = 'var(--shadow-soft-3d)';
                    lblGeneral.style.background = 'transparent';
                    lblGeneral.style.color = 'var(--text-secondary)';
                    lblGeneral.style.boxShadow = 'none';
                } else {
                    lblGeneral.style.background = 'var(--bg-surface)';
                    lblGeneral.style.color = 'var(--brand-forest)';
                    lblGeneral.style.boxShadow = 'var(--shadow-soft-3d)';
                    lblProject.style.background = 'transparent';
                    lblProject.style.color = 'var(--text-secondary)';
                    lblProject.style.boxShadow = 'none';
                }
            }
        }

        function renderProjectAttendeesList(projectId) {
            const container = document.getElementById('project-attendees-list');
            const box = document.getElementById('project-attendees-selection-box');
            if (!container || !box) return;

            if (!projectId || !projectMembersMap[projectId] || projectMembersMap[projectId].length === 0) {
                box.style.display = 'block';
                container.innerHTML = '<div style="font-size: 11px; color: var(--text-muted); padding: 8px;">{{ __("No assigned members in this project yet. All project roles will be notified automatically.") }}</div>';
                return;
            }

            box.style.display = 'block';
            container.innerHTML = '';

            projectMembersMap[projectId].forEach(m => {
                if (m.id === '{{ $user->id }}') return;
                const label = document.createElement('label');
                label.style = "display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--text-primary); cursor: pointer; padding: 4px 6px; border-radius: 6px; transition: background 0.2s;";
                label.innerHTML = `
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="attendee_ids[]" value="${m.id}" checked class="proj-attendee-chk" style="accent-color: var(--brand-forest);">
                        <strong>${m.name}</strong>
                        <span style="font-size: 11px; color: var(--text-muted);">(${m.email})</span>
                    </span>
                    <span class="nav-badge-pill" style="font-size: 10px;">{{ __("Project Team") }}</span>
                `;
                container.appendChild(label);
            });
        }

        function toggleAllProjectAttendees() {
            const chks = document.querySelectorAll('.proj-attendee-chk');
            if (!chks.length) return;
            const anyUnchecked = Array.from(chks).some(c => !c.checked);
            chks.forEach(c => c.checked = anyUnchecked);
        }

        async function scheduleMeetingSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ {{ __("Scheduling Meeting...") }}';
            }

            const formData = new FormData(form);

            try {
                const res = await fetch('{{ route("meetings.schedule") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: formData
                });

                if (res.status === 419) {
                    alert('{{ __("Session expired. The page will reload now.") }}');
                    window.location.reload();
                    return;
                }

                const data = await res.json();
                if (res.ok && data.success) {
                    showToastNotification('📅 ' + data.message);
                    closeScheduleMeetingModal();
                    setTimeout(() => {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                            window.location.reload();
                        } else {
                            window.location.reload();
                        }
                    }, 500);
                } else {
                    alert(data.message || 'Error scheduling meeting.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '🚀 {{ __("Schedule Meeting & Dispatch Invitations") }}';
                    }
                }
            } catch (err) {
                form.submit();
            }
        }

        function scheduleMeetingForCurrentProject() {
            if (typeof currentHubProjectId !== 'undefined' && currentHubProjectId) {
                openScheduleMeetingModal('project', currentHubProjectId);
            } else {
                openScheduleMeetingModal('project');
            }
        }

        // SMTP Connection Test AJAX
        function testSmtpConnectionAction() {
            const btn = document.getElementById('btn-test-smtp');
            const resultBox = document.getElementById('smtp-test-result-box');
            if (!btn || !resultBox) return;

            const host = document.getElementById('smtp-host-input')?.value;
            const port = document.getElementById('smtp-port-input')?.value;
            const username = document.getElementById('smtp-username-input')?.value;
            const password = document.getElementById('smtp-password-input')?.value;
            const encryption = document.getElementById('smtp-encryption-input')?.value;
            const fromAddr = document.getElementById('smtp-from-email-input')?.value;
            const fromName = document.getElementById('smtp-from-name-input')?.value;

            if (!host || !fromAddr) {
                resultBox.style.display = 'block';
                resultBox.style.background = 'rgba(217, 107, 95, 0.15)';
                resultBox.style.color = '#D96B5F';
                resultBox.style.border = '1px solid rgba(217, 107, 95, 0.3)';
                resultBox.innerHTML = '⚠️ {{ __('Please enter SMTP Host and Sender From Email address.') }}';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '⏳ {{ __('Testing Connection...') }}';
            resultBox.style.display = 'block';
            resultBox.style.background = 'var(--bg-surface-subtle)';
            resultBox.style.color = 'var(--text-secondary)';
            resultBox.style.border = '1px solid var(--border-color)';
            resultBox.innerHTML = '🔄 {{ __('Connecting to mail server and sending test packet...') }}';

            fetch("{{ route('organization.smtp.test') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    mail_host: host,
                    mail_port: port,
                    mail_username: username,
                    mail_password: password,
                    mail_encryption: encryption,
                    mail_from_address: fromAddr,
                    mail_from_name: fromName,
                }),
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                btn.disabled = false;
                btn.innerHTML = '🧪 {{ __('Test SMTP Connection') }}';
                if (status === 200 && body.success) {
                    resultBox.style.background = 'rgba(79, 155, 95, 0.15)';
                    resultBox.style.color = '#4F9B5F';
                    resultBox.style.border = '1px solid rgba(79, 155, 95, 0.35)';
                    resultBox.innerHTML = `✅ <strong>${body.message}</strong>`;
                } else {
                    resultBox.style.background = 'rgba(217, 107, 95, 0.15)';
                    resultBox.style.color = '#D96B5F';
                    resultBox.style.border = '1px solid rgba(217, 107, 95, 0.35)';
                    resultBox.innerHTML = `❌ <strong>${body.message || 'SMTP Connection Error'}</strong>`;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '🧪 {{ __('Test SMTP Connection') }}';
                resultBox.style.background = 'rgba(217, 107, 95, 0.15)';
                resultBox.style.color = '#D96B5F';
                resultBox.style.border = '1px solid rgba(217, 107, 95, 0.35)';
                resultBox.innerHTML = `❌ <strong>{{ __('Network error during SMTP test:') }} ${err.message}</strong>`;
            });
        }

        // Harmonic Sound Synthesizer via Web Audio API
        function playMeetingChime() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6 bell chord
                notes.forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime + idx * 0.09);
                    gain.gain.setValueAtTime(0.0001, ctx.currentTime + idx * 0.09);
                    gain.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + idx * 0.09 + 0.03);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + idx * 0.09 + 1.2);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + idx * 0.09);
                    osc.stop(ctx.currentTime + idx * 0.09 + 1.3);
                });
            } catch (e) {
                console.log('Audio chime auto-play notification', e);
            }
        }

        // Meeting Alarm Checker (Every 20s)
        const alertedMeetings = new Set();

        function checkMeetingAlarms() {
            if (!upcomingMeetingsList || !upcomingMeetingsList.length) return;
            const now = new Date();

            upcomingMeetingsList.forEach(m => {
                if (!m.scheduled_at) return;
                const sched = new Date(m.scheduled_at);
                const diffMins = (sched - now) / 60000;

                // Trigger chime if within 5 minutes of start time or up to 2 mins after start time
                if (diffMins <= 5 && diffMins >= -2 && !alertedMeetings.has(m.id)) {
                    alertedMeetings.add(m.id);
                    playMeetingChime();

                    const timeLabel = diffMins > 0 ? (Math.ceil(diffMins) + 'm') : '{{ __('is starting now!') }}';
                    const msg = `
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 24px;">🔔</span>
                            <div style="flex: 1;">
                                <div style="font-size: 13px; font-weight: 900; color: var(--brand-forest);">${m.title}</div>
                                <div style="font-size: 11px; color: var(--text-secondary);">${m.project_name ? '📁 ' + m.project_name + ' • ' : ''}🚪 ${m.room_name} (${timeLabel})</div>
                            </div>
                            <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 5px 12px; font-size: 11px; text-decoration: none;">🚀 {{ __('Join') }}</a>
                        </div>
                    `;
                    showToastNotification(msg, 12000);
                }
            });
        }

        setInterval(checkMeetingAlarms, 20000);
        setTimeout(checkMeetingAlarms, 2500);

        // ═════════════════════════════════════════════════════════════════════
        // 💬 REALTIME TEAM CHAT & DIRECT MESSAGES SYSTEM
        // ═════════════════════════════════════════════════════════════════════
        let activeChatChannelId = null;
        let activeChatTargetUserId = null;
        let activeChatMemberId = null;
        let chatPollingInterval = null;
        let cachedChatMembers = [];
        let cachedChatChannels = [];
        let currentUserId = "{{ Auth::id() }}";

        async function loadChatConversations(isManual = false) {
            try {
                const res = await fetch("{{ route('chat.conversations') }}", {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                cachedChatChannels = data.channels || [];
                cachedChatMembers = data.members || [];
                currentUserId = data.current_user_id || currentUserId;

                renderChatRoster(cachedChatChannels, cachedChatMembers);

                if (isManual) {
                    showToastNotification('💬 {{ __('Messages and channels updated.') }}');
                }

                // If currently viewing a chat, refresh its messages
                if (activeChatChannelId) {
                    fetchChatMessages(activeChatChannelId, false);
                }
            } catch (err) {
                console.error('Failed to load chat conversations:', err);
            }
        }

        function renderChatRoster(channels, members) {
            const channelsContainer = document.getElementById('chat-channels-list');
            const membersContainer = document.getElementById('chat-members-list');
            const rosterCount = document.getElementById('chat-roster-count');

            if (rosterCount) rosterCount.textContent = members.length;

            // Render Channels
            if (channelsContainer) {
                if (!channels.length) {
                    channelsContainer.innerHTML = `<div style="padding: 10px 12px; font-size: 11px; color: var(--text-muted); text-align: center;">{{ __('No channels found') }}</div>`;
                } else {
                    channelsContainer.innerHTML = channels.map(c => {
                        const isActive = activeChatChannelId === c.id;
                        const icon = c.type === 'announcement' ? '📢' : (c.type === 'room' ? '🚪' : '#');
                        return `
                            <div onclick="selectChatChannel('${c.id}', '${escapeHtml(c.name)}', '${c.type}', null, null)"
                                 class="chat-roster-item"
                                 data-name="${escapeHtml(c.name).toLowerCase()}"
                                 style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-radius: 10px; cursor: pointer; transition: all 0.2s; background: ${isActive ? 'rgba(79, 155, 95, 0.15)' : 'transparent'}; border: 1px solid ${isActive ? 'rgba(79, 155, 95, 0.35)' : 'transparent'};"
                                 onmouseover="if(!${isActive}) this.style.background='var(--bg-surface)'"
                                 onmouseout="if(!${isActive}) this.style.background='transparent'">
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                                    <span style="font-weight: 900; color: var(--brand-forest); font-size: 13px;">${icon}</span>
                                    <span style="font-size: 12px; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${c.name}</span>
                                </div>
                                ${c.last_message ? `<span style="font-size: 10px; color: var(--text-muted);">${c.last_message.created_at}</span>` : ''}
                            </div>
                        `;
                    }).join('');
                }
            }

            // Render Direct Messages Roster
            if (membersContainer) {
                if (!members.length) {
                    membersContainer.innerHTML = `<div style="padding: 10px 12px; font-size: 11px; color: var(--text-muted); text-align: center;">{{ __('No colleagues found') }}</div>`;
                } else {
                    membersContainer.innerHTML = members.map(m => {
                        const isSelected = activeChatTargetUserId === m.user_id;
                        const initials = (m.name || 'User').substring(0, 2).toUpperCase();
                        const lastMsgPreview = m.last_message ? (m.last_message.is_mine ? `{{ __('You') }}: ` : '') + m.last_message.body : m.job_title;

                        return `
                            <div onclick="openChatWithUser('${m.user_id}')"
                                 class="chat-roster-item"
                                 data-name="${escapeHtml(m.name).toLowerCase()} ${escapeHtml(m.nickname || '').toLowerCase()} ${escapeHtml(m.job_title || '').toLowerCase()}"
                                 style="display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 10px; cursor: pointer; transition: all 0.2s; background: ${isSelected ? 'rgba(79, 155, 95, 0.15)' : 'transparent'}; border: 1px solid ${isSelected ? 'rgba(79, 155, 95, 0.35)' : 'transparent'};"
                                 onmouseover="if(!${isSelected}) this.style.background='var(--bg-surface)'"
                                 onmouseout="if(!${isSelected}) this.style.background='transparent'">
                                <div style="position: relative; width: 34px; height: 34px; border-radius: 10px; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; color: white; flex-shrink: 0; box-shadow: var(--shadow-soft-3d); overflow: hidden;">
                                    ${m.avatar_url ? `<img src="${m.avatar_url}" style="width:100%;height:100%;object-fit:cover;">` : initials}
                                    <div style="position: absolute; bottom: -1px; inset-inline-end: -1px; width: 10px; height: 10px; border-radius: 50%; background: #4F9B5F; border: 2px solid var(--bg-surface-subtle);" title="Online"></div>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 12px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            ${escapeHtml(m.name)} ${m.is_self ? '<span style="font-size: 10px; color: var(--text-muted);">({{ __('You') }})</span>' : ''}
                                        </span>
                                        ${m.last_message ? `<span style="font-size: 9px; color: var(--text-muted); margin-inline-start: 4px;">${m.last_message.created_at}</span>` : ''}
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;">
                                        ${escapeHtml(lastMsgPreview)}
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }
        }

        function filterChatRoster() {
            const q = (document.getElementById('chat-search-input').value || '').toLowerCase().trim();
            document.querySelectorAll('.chat-roster-item').forEach(item => {
                const name = item.getAttribute('data-name') || '';
                item.style.display = (!q || name.includes(q)) ? 'flex' : 'none';
            });
        }

        async function openChatWithUser(targetUserId) {
            switchAdminTab('chat');
            try {
                const res = await fetch(`{{ url('/chat/dm') }}/${targetUserId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Failed to initiate direct message.');
                const data = await res.json();
                const channel = data.channel;
                const targetUser = data.target_user;

                // Find corresponding memberId
                const memberObj = cachedChatMembers.find(m => m.user_id == targetUserId);
                activeChatMemberId = memberObj ? memberObj.id : null;

                selectChatChannel(channel.id, targetUser.name, 'dm', targetUserId, activeChatMemberId);
            } catch (err) {
                console.error(err);
                showToastNotification('❌ ' + err.message);
            }
        }

        function selectFirstColleagueChat() {
            if (cachedChatMembers && cachedChatMembers.length) {
                const firstOther = cachedChatMembers.find(m => !m.is_self) || cachedChatMembers[0];
                openChatWithUser(firstOther.user_id);
            } else if (cachedChatChannels && cachedChatChannels.length) {
                const firstCh = cachedChatChannels[0];
                selectChatChannel(firstCh.id, firstCh.name, firstCh.type, null, null);
            }
        }

        function selectChatChannel(channelId, channelName, channelType, targetUserId = null, memberId = null) {
            activeChatChannelId = channelId;
            activeChatTargetUserId = targetUserId;
            activeChatMemberId = memberId;

            // Hide empty state, show active state
            const emptyState = document.getElementById('chat-empty-state');
            const activeState = document.getElementById('chat-active-state');
            if (emptyState) emptyState.style.display = 'none';
            if (activeState) activeState.style.display = 'flex';

            // Update Header Info
            const titleEl = document.getElementById('chat-active-title');
            const subtitleEl = document.getElementById('chat-active-subtitle');
            const badgeEl = document.getElementById('chat-active-badge');
            const avatarBox = document.getElementById('chat-active-avatar-box');
            const avatarInitials = document.getElementById('chat-active-avatar-initials');
            const profileBtn = document.getElementById('chat-view-profile-btn');

            if (titleEl) titleEl.textContent = channelType === 'dm' ? channelName : '#' + channelName;
            if (badgeEl) badgeEl.textContent = channelType === 'dm' ? 'Direct Message' : 'Channel';

            if (channelType === 'dm') {
                const memberObj = cachedChatMembers.find(m => m.user_id == targetUserId);
                if (memberObj) {
                    activeChatMemberId = memberObj.id;
                    if (subtitleEl) subtitleEl.textContent = `${memberObj.job_title} • ${memberObj.role}`;
                }
                if (avatarInitials) avatarInitials.textContent = (channelName || 'U').substring(0, 2).toUpperCase();
                if (profileBtn) profileBtn.style.display = 'inline-flex';
            } else {
                if (subtitleEl) subtitleEl.textContent = `Company Channel • All Members`;
                if (avatarInitials) avatarInitials.textContent = '#';
                if (profileBtn) profileBtn.style.display = 'none';
            }

            renderChatRoster(cachedChatChannels, cachedChatMembers);
            fetchChatMessages(channelId, true);

            // Focus input
            setTimeout(() => {
                const input = document.getElementById('chat-message-input');
                if (input) input.focus();
            }, 100);

            // Start auto polling
            if (chatPollingInterval) clearInterval(chatPollingInterval);
            chatPollingInterval = setInterval(() => {
                if (activeChatChannelId && document.getElementById('tab-chat')?.classList.contains('active')) {
                    fetchChatMessages(activeChatChannelId, false);
                }
            }, 3500);
        }

        async function fetchChatMessages(channelId, scrollToBottom = true) {
            try {
                const res = await fetch(`{{ url('/chat/channels') }}/${channelId}/messages`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const container = document.getElementById('chat-messages-container');
                if (!container) return;

                if (!data.messages || !data.messages.length) {
                    container.innerHTML = `
                        <div style="text-align: center; color: var(--text-muted); font-size: 13px; padding: 40px;">
                            <div style="font-size: 28px; margin-bottom: 8px;">👋</div>
                            <div style="font-weight: 700;">{{ __('No messages in this conversation yet.') }}</div>
                            <div style="font-size: 11px; margin-top: 4px;">{{ __('Send a message below to start the discussion!') }}</div>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = data.messages.map(msg => {
                    const isMine = msg.is_mine;
                    const initials = (msg.sender.name || 'U').substring(0, 2).toUpperCase();

                    return `
                        <div style="display: flex; gap: 10px; align-items: flex-end; justify-content: ${isMine ? 'flex-end' : 'flex-start'};">
                            ${!isMine ? `
                                <div style="width: 30px; height: 30px; border-radius: 8px; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; color: white; flex-shrink: 0; box-shadow: var(--shadow-soft-3d); overflow: hidden;">
                                    ${msg.sender.avatar_url ? `<img src="${msg.sender.avatar_url}" style="width:100%;height:100%;object-fit:cover;">` : initials}
                                </div>
                            ` : ''}

                            <div style="max-width: 70%; display: flex; flex-direction: column; align-items: ${isMine ? 'flex-end' : 'flex-start'};">
                                ${!isMine ? `<span style="font-size: 10px; font-weight: 800; color: var(--text-secondary); margin-bottom: 2px; margin-inline-start: 4px;">${escapeHtml(msg.sender.name)}</span>` : ''}
                                
                                <div style="padding: 10px 14px; border-radius: ${isMine ? '14px 14px 2px 14px' : '14px 14px 14px 2px'}; background: ${isMine ? 'var(--accent-gradient)' : 'var(--bg-surface)'}; color: ${isMine ? '#FFFDF6' : 'var(--text-primary)'}; border: 1px solid ${isMine ? 'transparent' : 'var(--border-color)'}; box-shadow: var(--shadow-soft-3d); font-size: 13px; line-height: 1.5; word-break: break-word;">
                                    ${escapeHtml(msg.body).replace(/\\n/g, '<br>')}
                                </div>
                                
                                <span style="font-size: 9px; color: var(--text-muted); margin-top: 3px; margin-inline-start: 4px; margin-inline-end: 4px;">
                                    ${msg.created_at}
                                </span>
                            </div>
                        </div>
                    `;
                }).join('');

                if (scrollToBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            } catch (err) {
                console.error('Failed to fetch messages:', err);
            }
        }

        async function handleSendChatMessage(event) {
            if (event) event.preventDefault();
            if (!activeChatChannelId) return;

            const input = document.getElementById('chat-message-input');
            const body = (input?.value || '').trim();
            if (!body) return;

            input.value = '';

            try {
                const res = await fetch(`{{ url('/chat/channels') }}/${activeChatChannelId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ body })
                });

                if (!res.ok) throw new Error('Failed to send message.');

                fetchChatMessages(activeChatChannelId, true);
                loadChatConversations(false);
            } catch (err) {
                showToastNotification('❌ ' + err.message);
            }
        }

        function handleChatInputKeydown(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                handleSendChatMessage();
            }
        }

        function viewActiveChatUserProfile() {
            if (activeChatMemberId) {
                openMemberProfileModal(activeChatMemberId);
            } else if (activeChatTargetUserId) {
                const memberObj = cachedChatMembers.find(m => m.user_id == activeChatTargetUserId);
                if (memberObj) openMemberProfileModal(memberObj.id);
            }
        }

        // ═════════════════════════════════════════════════════════════════════
        // 👤 COMPREHENSIVE TEAM MEMBER PROFILE MODAL SYSTEM
        // ═════════════════════════════════════════════════════════════════════
        let currentModalMemberData = null;

        async function openMemberProfileModal(memberId) {
            const modal = document.getElementById('member-details-modal');
            if (!modal) return;

            modal.style.display = 'flex';
            switchMemberProfileTab('about');

            // Reset placeholders
            document.getElementById('mp-user-name').textContent = '{{ __('Loading...') }}';
            document.getElementById('mp-info-email').textContent = '—';
            document.getElementById('mp-info-bio').textContent = '{{ __('Fetching profile details from workspace database...') }}';

            try {
                const res = await fetch(`{{ url('/organization/members') }}/${memberId}/details`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Failed to load member profile.');
                const data = await res.json();
                currentModalMemberData = data;

                const m = data.member;
                const p = data.profile;
                const s = data.stats;

                // Hero Details
                document.getElementById('mp-user-name').textContent = m.name;
                document.getElementById('mp-user-nickname').textContent = m.nickname ? `@${m.nickname}` : `@${m.name.toLowerCase().replace(/\\s+/g, '')}`;
                document.getElementById('mp-user-role').textContent = m.role_name;
                document.getElementById('mp-job-title').textContent = p.job_title || m.role_name;
                document.getElementById('mp-dept-team').textContent = `${p.department_name || '{{ __('General') }}'} • ${p.team_name || '{{ __('Core Team') }}'}`;
                
                const workModePill = document.getElementById('mp-work-mode');
                if (workModePill) {
                    const modeLabels = { 'remote': '🏠 {{ __('Remote') }}', 'hybrid': '🔄 {{ __('Hybrid') }}', 'onsite': '🏢 {{ __('On-site') }}' };
                    workModePill.textContent = modeLabels[p.work_mode] || '🏠 {{ __('Remote') }}';
                }

                // Avatar
                const imgEl = document.getElementById('mp-avatar-img');
                const fallbackEl = document.getElementById('mp-avatar-fallback');
                if (m.avatar_url) {
                    imgEl.src = m.avatar_url;
                    imgEl.style.display = 'block';
                    fallbackEl.style.display = 'none';
                } else {
                    imgEl.style.display = 'none';
                    fallbackEl.style.display = 'block';
                    fallbackEl.textContent = (m.name || 'U').substring(0, 2).toUpperCase();
                }

                // Tab 1: About & Info
                document.getElementById('mp-info-email').textContent = m.email;
                document.getElementById('mp-info-phone').textContent = p.phone || '—';
                document.getElementById('mp-info-dob').textContent = p.date_of_birth || '—';
                document.getElementById('mp-info-joined').textContent = m.joined_at;
                document.getElementById('mp-info-bio').textContent = p.bio || '{{ __('No bio or summary added yet.') }}';

                // Skills
                const skillsContainer = document.getElementById('mp-info-skills');
                if (skillsContainer) {
                    if (p.skills && p.skills.length) {
                        skillsContainer.innerHTML = p.skills.map(sk => `<span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-size: 11px; font-weight: 700;">⚡ ${escapeHtml(sk)}</span>`).join('');
                    } else {
                        skillsContainer.innerHTML = `<span style="font-size: 11px; color: var(--text-muted); font-style: italic;">— {{ __('No skills listed') }} —</span>`;
                    }
                }

                // Hobbies
                const hobbiesContainer = document.getElementById('mp-info-hobbies');
                if (hobbiesContainer) {
                    if (p.hobbies && p.hobbies.length) {
                        hobbiesContainer.innerHTML = p.hobbies.map(hb => `<span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; font-size: 11px; font-weight: 700;">🎯 ${escapeHtml(hb)}</span>`).join('');
                    } else {
                        hobbiesContainer.innerHTML = `<span style="font-size: 11px; color: var(--text-muted); font-style: italic;">— {{ __('No hobbies listed') }} —</span>`;
                    }
                }

                // Social Links
                const socialsContainer = document.getElementById('mp-info-socials');
                if (socialsContainer) {
                    const links = p.social_links || {};
                    const socialHtml = [];
                    if (links.linkedin) socialHtml.push(`<a href="${links.linkedin}" target="_blank" class="tactile-btn btn-secondary" style="font-size: 11px; padding: 4px 10px; text-decoration: none;">💼 LinkedIn</a>`);
                    if (links.github) socialHtml.push(`<a href="${links.github}" target="_blank" class="tactile-btn btn-secondary" style="font-size: 11px; padding: 4px 10px; text-decoration: none;">🐙 GitHub</a>`);
                    if (links.twitter) socialHtml.push(`<a href="${links.twitter}" target="_blank" class="tactile-btn btn-secondary" style="font-size: 11px; padding: 4px 10px; text-decoration: none;">🐦 X (Twitter)</a>`);
                    if (links.website) socialHtml.push(`<a href="${links.website}" target="_blank" class="tactile-btn btn-secondary" style="font-size: 11px; padding: 4px 10px; text-decoration: none;">🌐 Website</a>`);

                    socialsContainer.innerHTML = socialHtml.length ? socialHtml.join('') : `<span style="font-size: 11px; color: var(--text-muted); font-style: italic;">— {{ __('No social links attached') }} —</span>`;
                }

                // Notes
                const notesBox = document.getElementById('mp-notes-container');
                const notesText = document.getElementById('mp-info-notes');
                if (p.notes) {
                    notesBox.style.display = 'block';
                    notesText.textContent = p.notes;
                } else {
                    notesBox.style.display = 'none';
                }

                // Tab 2: Assigned Tasks
                document.getElementById('mp-tasks-count-pill').textContent = s.total_tasks;
                document.getElementById('mp-task-stat-total').textContent = s.total_tasks;
                document.getElementById('mp-task-stat-progress').textContent = s.in_progress_tasks;
                document.getElementById('mp-task-stat-pending').textContent = s.pending_tasks;
                document.getElementById('mp-task-stat-done').textContent = s.completed_tasks;

                const tasksContainer = document.getElementById('mp-tasks-list-container');
                if (tasksContainer) {
                    if (!data.tasks || !data.tasks.length) {
                        tasksContainer.innerHTML = `<div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 30px;">{{ __('No tasks assigned to this member.') }}</div>`;
                    } else {
                        tasksContainer.innerHTML = data.tasks.map(t => {
                            const priorityColors = {
                                'urgent': 'background: rgba(217, 107, 95, 0.15); color: #D96B5F;',
                                'high': 'background: rgba(214, 162, 58, 0.15); color: #D6A23A;',
                                'normal': 'background: rgba(79, 155, 95, 0.15); color: #4F9B5F;',
                                'low': 'background: rgba(148, 163, 184, 0.15); color: #64748B;'
                            };
                            return `
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; border-radius: 12px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); box-shadow: var(--shadow-soft-3d); gap: 12px; flex-wrap: wrap;">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <span class="nav-badge-pill" style="font-family: monospace; font-size: 10px; font-weight: 800;">#${t.task_number}</span>
                                        <div>
                                            <div style="font-weight: 800; font-size: 13px; color: var(--text-primary);">${escapeHtml(t.title)}</div>
                                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 2px; font-size: 11px; color: var(--text-secondary);">
                                                <span>📁 ${escapeHtml(t.project ? t.project.name : 'General')}</span>
                                                ${t.due_date ? `<span>• 📅 ${t.due_date} ${t.is_overdue ? '<span style="color:#D96B5F;font-weight:800;">({{ __('Overdue') }})</span>' : ''}</span>` : ''}
                                                ${t.checklist_count ? `<span>• ☑️ ${t.checklist_done}/${t.checklist_count}</span>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="nav-badge-pill" style="${priorityColors[t.priority] || ''}; font-size: 10px; text-transform: uppercase;">${t.priority}</span>
                                        <span class="nav-badge-pill" style="font-size: 10px;">${t.status.replace('_', ' ')}</span>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                }

                // Tab 3: Work Time & Logs
                document.getElementById('mp-hours-count-pill').textContent = `${s.total_hours_logged}h`;
                document.getElementById('mp-time-total-hours').textContent = `${s.total_hours_logged}h`;

                const timerText = document.getElementById('mp-active-timer-text');
                if (s.active_timer) {
                    timerText.innerHTML = `<strong>⏱️ ${s.active_timer.project_name || 'Project'}</strong>: ${s.active_timer.task_title || 'Work Session'}`;
                } else {
                    timerText.textContent = '{{ __('No active timer running') }}';
                }

                const tbody = document.getElementById('mp-time-entries-tbody');
                if (tbody) {
                    if (!data.time_entries || !data.time_entries.length) {
                        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 20px;">{{ __('No work logs recorded yet.') }}</td></tr>`;
                    } else {
                        tbody.innerHTML = data.time_entries.map(te => `
                            <tr>
                                <td style="font-size: 12px; font-weight: 700; color: var(--text-primary);">${te.date}</td>
                                <td style="font-size: 12px; font-weight: 700; color: var(--brand-forest);">📁 ${escapeHtml(te.project_name)}</td>
                                <td style="font-size: 12px; color: var(--text-secondary);">${escapeHtml(te.task_title)}</td>
                                <td><span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-weight: 800;">${te.duration_hours}h</span></td>
                                <td style="font-size: 11px; color: var(--text-muted);">${escapeHtml(te.description)}</td>
                            </tr>
                        `).join('');
                    }
                }

            } catch (err) {
                console.error(err);
                showToastNotification('❌ ' + err.message);
            }
        }

        function switchMemberProfileTab(tabName) {
            document.querySelectorAll('.member-profile-tab-btn').forEach(btn => {
                btn.style.color = 'var(--text-secondary)';
                btn.style.borderBottomColor = 'transparent';
                btn.classList.remove('active');
            });
            const activeBtn = document.getElementById(`mp-tab-btn-${tabName}`);
            if (activeBtn) {
                activeBtn.style.color = 'var(--brand-forest)';
                activeBtn.style.borderBottomColor = 'var(--brand-forest)';
                activeBtn.classList.add('active');
            }

            document.getElementById('mp-tab-content-about').style.display = tabName === 'about' ? 'flex' : 'none';
            document.getElementById('mp-tab-content-tasks').style.display = tabName === 'tasks' ? 'flex' : 'none';
            document.getElementById('mp-tab-content-time').style.display = tabName === 'time' ? 'flex' : 'none';
        }

        function closeMemberProfileModal() {
            const modal = document.getElementById('member-details-modal');
            if (modal) modal.style.display = 'none';
        }

        function openChatFromProfileModal() {
            if (currentModalMemberData && currentModalMemberData.member) {
                const targetUserId = currentModalMemberData.member.user_id;
                closeMemberProfileModal();
                openChatWithUser(targetUserId);
            }
        }

        async function testOrgAiConnectionAction() {
            const keyInput = document.getElementById('org-openai-key-input');
            const resultBox = document.getElementById('org-ai-test-result-box');
            const btn = document.getElementById('btn-test-org-ai');
            const apiKey = keyInput ? keyInput.value.trim() : '';

            if (!apiKey && (!keyInput.placeholder || keyInput.placeholder.includes('sk-'))) {
                resultBox.style.display = 'block';
                resultBox.style.background = 'rgba(239, 68, 68, 0.15)';
                resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                resultBox.style.color = '#EF4444';
                resultBox.innerText = '{{ __("Please enter an OpenAI API key first.") }}';
                return;
            }

            resultBox.style.display = 'block';
            resultBox.style.background = 'rgba(59, 130, 246, 0.15)';
            resultBox.style.border = '1px solid rgba(59, 130, 246, 0.3)';
            resultBox.style.color = '#3B82F6';
            resultBox.innerText = '⚡ {{ __("Testing OpenAI API key connectivity...") }}';
            if (btn) btn.disabled = true;

            try {
                const res = await fetch('{{ route("organization.ai.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ api_key: apiKey })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    resultBox.style.background = 'rgba(16, 185, 129, 0.15)';
                    resultBox.style.border = '1px solid rgba(16, 185, 129, 0.3)';
                    resultBox.style.color = '#10B981';
                    resultBox.innerText = data.message || '{{ __("✅ Key is valid and active!") }}';
                } else {
                    resultBox.style.background = 'rgba(239, 68, 68, 0.15)';
                    resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                    resultBox.style.color = '#EF4444';
                    resultBox.innerText = '❌ ' + (data.message || '{{ __("Connection failed.") }}');
                }
            } catch (e) {
                resultBox.style.background = 'rgba(239, 68, 68, 0.15)';
                resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                resultBox.style.color = '#EF4444';
                resultBox.innerText = '❌ Network error: ' + e.message;
            } finally {
                if (btn) btn.disabled = false;
            }
        }

        function switchOrgSettingsTab(subtabKey, btnElement) {
            document.querySelectorAll('.org-subtab-pane').forEach(p => p.style.display = 'none');
            document.querySelectorAll('.org-subtab-btn').forEach(b => b.classList.remove('active'));

            const targetPane = document.getElementById('org-subtab-content-' + subtabKey);
            if (targetPane) targetPane.style.display = 'block';

            if (btnElement) {
                btnElement.classList.add('active');
            } else {
                const defaultBtn = document.getElementById('org-subtab-btn-' + subtabKey);
                if (defaultBtn) defaultBtn.classList.add('active');
            }
        }

        // ── DUAL-SECTION DAILY TIMESHEETS & ATTENDANCE ENGINE ──
        let currentTimesheetDate = document.getElementById('ts-filter-date')?.value || new Date().toISOString().split('T')[0];
        let currentTimesheetUserId = document.getElementById('ts-filter-user')?.value || '{{ $user->id }}';

        function handleTimesheetDateChange(val) {
            currentTimesheetDate = val;
            loadDailyTimesheetsData(currentTimesheetDate, currentTimesheetUserId);
        }

        function shiftTimesheetDate(offset) {
            const cur = currentTimesheetDate ? new Date(currentTimesheetDate + 'T12:00:00') : new Date();
            cur.setDate(cur.getDate() + offset);
            const yyyy = cur.getFullYear();
            const mm = String(cur.getMonth() + 1).padStart(2, '0');
            const dd = String(cur.getDate()).padStart(2, '0');
            const dateStr = `${yyyy}-${mm}-${dd}`;
            const input = document.getElementById('ts-filter-date');
            if (input) input.value = dateStr;
            currentTimesheetDate = dateStr;
            loadDailyTimesheetsData(currentTimesheetDate, currentTimesheetUserId);
        }

        function setTimesheetToday() {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const todayStr = `${yyyy}-${mm}-${dd}`;
            const input = document.getElementById('ts-filter-date');
            if (input) input.value = todayStr;
            currentTimesheetDate = todayStr;
            loadDailyTimesheetsData(currentTimesheetDate, currentTimesheetUserId);
        }

        function handleTimesheetUserChange(val) {
            currentTimesheetUserId = val;
            loadDailyTimesheetsData(currentTimesheetDate, currentTimesheetUserId);
        }

        function refreshDailyTimesheet() {
            const inputDate = document.getElementById('ts-filter-date')?.value || currentTimesheetDate;
            const inputUser = document.getElementById('ts-filter-user')?.value || currentTimesheetUserId;
            loadDailyTimesheetsData(inputDate, inputUser);
        }

        async function loadDailyTimesheetsData(date, userId) {
            if (!date) date = new Date().toISOString().split('T')[0];
            if (!userId) userId = '{{ $user->id }}';

            try {
                const res = await fetch(`/api/timesheets/daily-summary?date=${encodeURIComponent(date)}&user_id=${encodeURIComponent(userId)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    credentials: 'same-origin'
                });

                if (!res.ok) {
                    console.error('Error fetching timesheet daily summary');
                    return;
                }

                const data = await res.json();

                // 1. Update KPI Summary Cards
                const officeEl = document.getElementById('ts-kpi-office-time');
                const taskEl = document.getElementById('ts-kpi-task-time');
                const idleEl = document.getElementById('ts-kpi-idle-time');
                const ratioEl = document.getElementById('ts-kpi-ratio');

                const officeSec = data.total_office_seconds || 0;
                const taskSec = data.total_task_seconds || 0;
                const idleSec = Math.max(0, officeSec - taskSec);

                if (officeEl) officeEl.textContent = data.total_office_formatted || '00:00:00';
                if (taskEl) taskEl.textContent = data.total_task_formatted || '00:00:00';

                const idleH = Math.floor(idleSec / 3600);
                const idleM = Math.floor((idleSec % 3600) / 60);
                const idleS = idleSec % 60;
                const idleFormatted = String(idleH).padStart(2, '0') + ':' + String(idleM).padStart(2, '0') + ':' + String(idleS).padStart(2, '0');
                if (idleEl) idleEl.textContent = idleFormatted;

                const ratio = officeSec > 0 ? Math.min(100, Math.round((taskSec / officeSec) * 100)) : (taskSec > 0 ? 100 : 0);
                if (ratioEl) ratioEl.textContent = `${ratio}%`;

                // 2. Active timer banner
                const timerBanner = document.getElementById('ts-live-timer-banner');
                if (timerBanner) {
                    if (data.active_timer && userId === '{{ $user->id }}') {
                        timerBanner.style.display = 'block';
                        const projectPill = document.getElementById('ts-banner-project-pill');
                        const taskTitle = document.getElementById('ts-banner-task-title');
                        const clockEl = document.getElementById('ts-banner-clock');
                        if (projectPill) projectPill.textContent = data.active_timer.project_name || 'Project';
                        if (taskTitle) taskTitle.textContent = data.active_timer.task_title || 'Work session';
                        if (clockEl) clockEl.textContent = formatTimerClock(data.active_timer.elapsed_seconds || 0);
                    } else {
                        timerBanner.style.display = 'none';
                    }
                }

                // 3. Section 1: Tasks Table
                const tasksTbody = document.getElementById('ts-tasks-tbody');
                const tasksCountPill = document.getElementById('ts-tasks-count-pill');
                const taskEntries = data.task_entries || [];

                if (tasksCountPill) {
                    tasksCountPill.textContent = `${taskEntries.length} {{ __('Tasks') }}`;
                }

                if (tasksTbody) {
                    if (!taskEntries.length) {
                        tasksTbody.innerHTML = `
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                    <div style="font-size: 28px; margin-bottom: 6px;">📋</div>
                                    {{ __('No task work sessions recorded on this date.') }}
                                </td>
                            </tr>
                        `;
                    } else {
                        tasksTbody.innerHTML = taskEntries.map(te => {
                            let statusBadge = '';
                            if (te.status === 'approved') {
                                statusBadge = '<span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">🔒 {{ __('Approved') }}</span>';
                            } else if (te.status === 'in_progress') {
                                statusBadge = '<span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F; font-weight: 800;">⚡ {{ __('In Progress') }}</span>';
                            } else {
                                statusBadge = '<span class="nav-badge-pill" style="background: var(--bg-surface-subtle); color: var(--text-secondary);">✓ {{ __('Completed') }}</span>';
                            }

                            const billableBadge = te.is_billable
                                ? '<span class="nav-badge-pill" style="background: rgba(36, 92, 58, 0.15); color: var(--brand-forest); font-weight: 800;">💎 {{ __('Billable') }}</span>'
                                : '<span class="nav-badge-pill" style="color: var(--text-muted);">{{ __('Standard') }}</span>';

                            return `
                                <tr>
                                    <td>
                                        <div style="font-weight: 800; color: var(--text-primary);">${escapeHtml(te.task_title || 'Work Session')}</div>
                                        ${te.description ? `<div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">${escapeHtml(te.description)}</div>` : ''}
                                    </td>
                                    <td>
                                        <span class="nav-badge-pill" style="font-weight: 700; color: var(--brand-forest);">📁 ${escapeHtml(te.project_name || 'General')}</span>
                                    </td>
                                    <td style="font-family: monospace; font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                                        ${te.started_at || '—'} ➔ ${te.ended_at || '—'}
                                    </td>
                                    <td style="font-family: monospace; font-weight: 900; font-size: 13px; color: var(--brand-forest);">
                                        ${te.duration_formatted || '00m'}
                                    </td>
                                    <td>${billableBadge}</td>
                                    <td>${statusBadge}</td>
                                </tr>
                            `;
                        }).join('');
                    }
                }

                // 4. Section 2: Virtual Office Attendance Sessions
                const attTbody = document.getElementById('ts-attendance-tbody');
                const attCountPill = document.getElementById('ts-attendance-count-pill');
                const attSessions = data.attendance_sessions || [];

                if (attCountPill) {
                    attCountPill.textContent = `${attSessions.length} {{ __('Sessions') }}`;
                }

                if (attTbody) {
                    if (!attSessions.length) {
                        attTbody.innerHTML = `
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                    <div style="font-size: 28px; margin-bottom: 6px;">🏢</div>
                                    {{ __('No virtual office presence recorded on this date.') }}
                                </td>
                            </tr>
                        `;
                    } else {
                        attTbody.innerHTML = attSessions.map(s => {
                            let statusPill = '';
                            if (s.status === 'active') {
                                statusPill = '<span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F; font-weight: 800;">🟢 {{ __('In Office (Live)') }}</span>';
                            } else if (s.status === 'idle_paused') {
                                statusPill = '<span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.2); color: #D6A23A; font-weight: 800;">⏸️ {{ __('Idle Paused') }}</span>';
                            } else {
                                statusPill = '<span class="nav-badge-pill" style="background: var(--bg-surface-subtle); color: var(--text-muted);">⚪ {{ __('Completed') }}</span>';
                            }

                            return `
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 16px;">📍</span>
                                            <div>
                                                <div style="font-weight: 800; color: var(--text-primary);">${escapeHtml(s.branch_name || 'Main Office')}</div>
                                                <div style="font-size: 11px; color: var(--text-muted);">🚪 ${escapeHtml(s.room_name || 'General Space')}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-family: monospace; font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                                        🟢 ${s.check_in || '—'}
                                    </td>
                                    <td style="font-family: monospace; font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                                        🔴 ${s.check_out || '{{ __('Still in Office') }}'}
                                    </td>
                                    <td style="font-family: monospace; font-weight: 900; font-size: 13px; color: var(--brand-forest);">
                                        ${s.duration_formatted || '00m'}
                                    </td>
                                    <td>${statusPill}</td>
                                </tr>
                            `;
                        }).join('');
                    }
                }

            } catch (e) {
                console.error('Error loading timesheets data:', e);
            }
        }

        // Auto-load timesheet on DOM load if timesheets tab is active
        document.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash === '#timesheets' || document.getElementById('tab-timesheets')?.classList.contains('active')) {
                refreshDailyTimesheet();
            }
        });

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
    </script>
