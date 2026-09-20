// Theme Manager (Light / Dark / System)
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
    const i18n = window.ThemeI18N || {};
    showToastNotification(next === 'dark'
        ? `<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">dark_mode</span> <strong>${i18n.darkActivatedTitle || 'Dark Spatial Workspace'}</strong><br>${i18n.darkActivatedBody || 'Deep calm green mode activated.'}`
        : `<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">light_mode</span> <strong>${i18n.lightActivatedTitle || 'Light Natural Mode'}</strong><br>${i18n.lightActivatedBody || 'Warm ivory workspace activated.'}`);
}

// Initialize saved theme on load
(function () {
    const savedTheme = localStorage.getItem('vw_theme') || 'light';
    applyTheme(savedTheme);
})();

function toggleSidebarCollapse() {
    const sidebar = document.getElementById('dashboardSidebar');
    const mainContent = document.querySelector('.main-content');
    const toggleBtn = document.querySelector('.sidebar-toggle-btn');
    const isRtl = document.documentElement.dir === 'rtl' || document.documentElement.lang === 'ar';

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
        const isRtl = document.documentElement.dir === 'rtl' || document.documentElement.lang === 'ar';

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
    const targetSec = document.getElementById(sectionId);
    if (!targetSec) return;

    const willOpen = targetSec.classList.contains('collapsed');

    // Close all other accordions (Single active accordion)
    document.querySelectorAll('.sidebar-accordion').forEach(sec => {
        sec.classList.add('collapsed');
    });

    // If it was collapsed, now open it
    if (willOpen) {
        targetSec.classList.remove('collapsed');
    }
}

function previewCompanyLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
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
        reader.onload = function (e) {
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
