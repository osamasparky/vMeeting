/**
 * Office presence: the attendance clock, the heartbeat, and the idle detector
 * ("Are you still online?"). Moved verbatim from office.blade.php (refactor phase 8).
 *
 * A classic script loaded right after the page's inline script, so it shares its
 * global scope: it reads CONFIG, isGuest, localAvatar, getCurrentRoom, showToast,
 * isOfficePresencePaused, hasActiveTaskRunning and attendanceText from the page,
 * and its functions stay global for the modal buttons (confirmUserPresence,
 * resumeUserPresenceFromPaused).
 */

// Idle Detector Configuration
const idlePolicy = CONFIG.attendancePolicy || {};
const idlePromptMinutes = Number(idlePolicy.idle_prompt_minutes || 15);
const idleGraceSeconds = Number(idlePolicy.idle_response_grace_seconds || 180);
let lastUserActivityTimestamp = Date.now();
let idleCountdownSeconds = idleGraceSeconds;
let idleCountdownInterval = null;
let isIdleCheckModalOpen = false;

// 1. Attendance Heartbeat & Presence Logger & Live Office Clock
// The clock shows the server's figure for today plus wall-clock time since it was fetched.
// Counting +1 per tick drifts: background tabs throttle timers to once a minute.
let officeAttendanceTotalSeconds = 0;
let officeAttendanceBaseAt = Date.now();
let officeAttendanceTimerInterval = null;

async function fetchInitialAttendanceSummary() {
    if (isGuest) return;
    try {
        const res = await fetch(`/api/office/attendance/summary?period=today&organization_id=${CONFIG.org?.id}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrf }
        });
        if (res.ok) {
            const data = await res.json();
            if (data && typeof data.today_total_seconds !== 'undefined') {
                officeAttendanceTotalSeconds = Math.floor(Number(data.today_total_seconds) || 0);
                officeAttendanceBaseAt = Date.now();
            }
        }
    } catch(e) {}
    startOfficeAttendanceTicker();
}

function startOfficeAttendanceTicker() {
    if (officeAttendanceTimerInterval) clearInterval(officeAttendanceTimerInterval);
    const clockEl = document.getElementById('office-attendance-clock');
    
    function tick() {
        if (!isOfficePresencePaused) {
            if (clockEl) {
                const totalSecs = officeAttendanceTotalSeconds + Math.max(0, Math.floor((Date.now() - officeAttendanceBaseAt) / 1000));
                const hrs = String(Math.floor(totalSecs / 3600)).padStart(2, '0');
                const mins = String(Math.floor((totalSecs % 3600) / 60)).padStart(2, '0');
                const secs = String(totalSecs % 60).padStart(2, '0');
                clockEl.textContent = `${hrs}:${mins}:${secs}`;
            }
        }
    }
    tick();
    officeAttendanceTimerInterval = setInterval(tick, 1000);
}

async function logAttendance(action, duration = null, roomId = null) {
    if (isGuest) return; // Only log for registered members
    try {
        const here = (typeof localAvatar !== 'undefined') ? getCurrentRoom(localAvatar.x, localAvatar.y) : null;
        const targetRoom = roomId || (here ? here.id : null);
        await fetch('/api/office/attendance/log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                room_id: targetRoom,
                floor_id: CONFIG.map?.floor_id || null,
                duration_seconds: duration
            })
        });
    } catch (err) {
        console.error('[Attendance] Log failed:', err);
    }
}

// Initialize Attendance on Office Join
if (!isGuest && (idlePolicy.auto_attendance_enabled !== false)) {
    logAttendance('enter');
    fetchInitialAttendanceSummary();

    // Periodic heartbeat every 45 seconds
    setInterval(() => {
        if (!isOfficePresencePaused) {
            logAttendance('heartbeat', 45);
        }
    }, 45000);

    // After sleep or a long-hidden tab the server closed the session at its last heartbeat
    // and starts a new one; resync so the clock does not count the gap.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && !isOfficePresencePaused) {
            logAttendance('heartbeat').then(fetchInitialAttendanceSummary);
        }
    });

    // Log exit on page unload
    window.addEventListener('beforeunload', () => {
        if (!isOfficePresencePaused) {
            navigator.sendBeacon('/api/office/attendance/log', new Blob([JSON.stringify({
                action: 'leave',
                _token: CONFIG.csrf
            })], { type: 'application/json' }));
        }
    });
}

// 3. Smart Idle & Inactivity Detector ("Are you still online?")
function registerUserActivity() {
    lastUserActivityTimestamp = Date.now();
}

['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'].forEach(evt => {
    window.addEventListener(evt, registerUserActivity, { passive: true });
});

// Inactivity Check Loop (Runs every 10 seconds)
setInterval(() => {
    if (isGuest || isOfficePresencePaused || isIdleCheckModalOpen) return;

    // ⚠️ CRITICAL RULE: If user has an active running task, DO NOT log him out or pause!
    if (hasActiveTaskRunning) {
        return;
    }

    const elapsedIdleMs = Date.now() - lastUserActivityTimestamp;
    const thresholdMs = idlePromptMinutes * 60 * 1000;

    if (elapsedIdleMs >= thresholdMs) {
        showIdleCheckModal();
    }
}, 10000);

function showIdleCheckModal() {
    isIdleCheckModalOpen = true;
    idleCountdownSeconds = idleGraceSeconds;
    const modal = document.getElementById('office-idle-check-modal');
    const clock = document.getElementById('idle-countdown-clock');
    const bar = document.getElementById('idle-countdown-bar');

    if (modal) modal.style.display = 'flex';
    updateIdleCountdownUI();

    if (idleCountdownInterval) clearInterval(idleCountdownInterval);

    idleCountdownInterval = setInterval(() => {
        idleCountdownSeconds--;
        updateIdleCountdownUI();

        if (idleCountdownSeconds <= 0) {
            clearInterval(idleCountdownInterval);
            idleCountdownInterval = null;
            handleIdleTimeoutExpiration();
        }
    }, 1000);
}

function updateIdleCountdownUI() {
    const clock = document.getElementById('idle-countdown-clock');
    const bar = document.getElementById('idle-countdown-bar');
    const mins = String(Math.floor(idleCountdownSeconds / 60)).padStart(2, '0');
    const secs = String(idleCountdownSeconds % 60).padStart(2, '0');
    if (clock) clock.textContent = `${mins}:${secs}`;
    if (bar) {
        const pct = Math.max(0, Math.min(100, (idleCountdownSeconds / idleGraceSeconds) * 100));
        bar.style.width = `${pct}%`;
    }
}

function confirmUserPresence() {
    isIdleCheckModalOpen = false;
    if (idleCountdownInterval) {
        clearInterval(idleCountdownInterval);
        idleCountdownInterval = null;
    }
    const modal = document.getElementById('office-idle-check-modal');
    if (modal) modal.style.display = 'none';
    lastUserActivityTimestamp = Date.now();
    logAttendance('heartbeat');
    showToast('🟢 ' + attendanceText.presenceConfirmed);
}

function handleIdleTimeoutExpiration() {
    isIdleCheckModalOpen = false;
    const checkModal = document.getElementById('office-idle-check-modal');
    if (checkModal) checkModal.style.display = 'none';

    // Show Paused Overlay and Pause Server Attendance Session
    const pausedOverlay = document.getElementById('office-idle-paused-overlay');
    if (pausedOverlay) pausedOverlay.style.display = 'flex';
    isOfficePresencePaused = true;
    // The server ends the session at the last activity, so the idle stretch is not counted.
    logAttendance('idle_pause', Math.round((Date.now() - lastUserActivityTimestamp) / 1000));
}

function resumeUserPresenceFromPaused() {
    const pausedOverlay = document.getElementById('office-idle-paused-overlay');
    if (pausedOverlay) pausedOverlay.style.display = 'none';
    isOfficePresencePaused = false;
    lastUserActivityTimestamp = Date.now();
    logAttendance('idle_resume').then(fetchInitialAttendanceSummary);
    showToast('▶️ ' + attendanceText.presenceResumed);
}
