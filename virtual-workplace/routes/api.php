<?php

use App\Domains\Administration\Controllers\AdminController;
use App\Domains\Chat\Controllers\ChatController;
use App\Domains\Collaboration\Controllers\RecordingController;
use App\Domains\Guests\Controllers\GuestController;
use App\Domains\Identity\Controllers\AuthController;
use App\Domains\Identity\Services\RealtimeTokenService;
use App\Domains\Meetings\Controllers\MeetingController;
use App\Domains\People\Controllers\AttendanceApiController;
use App\Domains\People\Controllers\PeopleController;
use App\Domains\People\Controllers\ProfileController;
use App\Domains\Projects\Controllers\ProjectController;
use App\Domains\Projects\Controllers\TaskController;
use App\Domains\Projects\Controllers\TimesheetController;
use App\Domains\Projects\Controllers\TimeTrackingController;
use App\Domains\Tenancy\Controllers\BillingApiController;
use App\Domains\Tenancy\Controllers\OrganizationController;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\Plan;
use App\Domains\Workspace\Controllers\SpatialInteractionsApiController;
use App\Domains\Workspace\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Virtual Workplace MVP
|--------------------------------------------------------------------------
|
| All routes are versioned under /api/v1.
| Convention: Controller (thin) → FormRequest (validate) → Action (logic) → Policy (authorize)
|
*/

Route::prefix('v1')->group(function () {

    // ══════════════════════════════════════════════════════════════
    // AUTH (public)
    // ══════════════════════════════════════════════════════════════
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
    });

    // ══════════════════════════════════════════════════════════════
    // AUTHENTICATED ROUTES
    // ══════════════════════════════════════════════════════════════
    Route::middleware('auth:sanctum')->group(function () {

        // ── Auth ──
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // ── User Profile ──
        Route::get('/users/me', [ProfileController::class, 'show']);
        Route::patch('/users/me', [ProfileController::class, 'update']);
        Route::patch('/users/me/avatar', [ProfileController::class, 'updateAvatar']);

        // ── Organizations ──
        Route::post('/organizations', [OrganizationController::class, 'store']);

        // ── Organization-scoped routes (require membership) ──
        Route::prefix('organizations/{organization}')
            ->middleware('org.member')
            ->group(function () {

                // Org details
                Route::get('/', [OrganizationController::class, 'show']);
                Route::patch('/', [OrganizationController::class, 'update'])
                    ->middleware('permission:organizations.manage');

                // Realtime WebSocket Auth Token
                Route::post('/realtime-token', function (Organization $organization, RealtimeTokenService $service) {
                    return response()->json([
                        'token' => $service->generateToken(Auth::user(), $organization),
                        'ws_url' => env('REALTIME_WS_URL', 'ws://127.0.0.1:8080'),
                    ]);
                });

                // Settings
                Route::get('/settings', [OrganizationController::class, 'showSettings']);
                Route::patch('/settings', [OrganizationController::class, 'updateSettings'])
                    ->middleware('permission:organizations.manage');

                // Members
                Route::get('/members', [OrganizationController::class, 'listMembers'])
                    ->middleware('permission:members.view');
                Route::post('/members/invite', [OrganizationController::class, 'inviteMember'])
                    ->middleware('permission:members.invite');
                Route::patch('/members/{member}', [OrganizationController::class, 'updateMember'])
                    ->middleware('permission:members.manage');
                Route::delete('/members/{member}', [OrganizationController::class, 'removeMember'])
                    ->middleware('permission:members.manage');

                // Departments
                Route::get('/departments', [PeopleController::class, 'listDepartments']);
                Route::post('/departments', [PeopleController::class, 'createDepartment'])
                    ->middleware('permission:departments.manage');

                // Teams
                Route::get('/teams', [PeopleController::class, 'listTeams']);
                Route::post('/teams', [PeopleController::class, 'createTeam'])
                    ->middleware('permission:teams.manage');

                // Roles
                Route::get('/roles', [AdminController::class, 'listRoles']);
                Route::patch('/members/{member}/role', [AdminController::class, 'updateMemberRole'])
                    ->middleware('permission:members.manage');

                // Audit Logs
                Route::get('/audit-logs', [AdminController::class, 'listAuditLogs'])
                    ->middleware('permission:audit.view');

                // Workspace: Floors
                Route::get('/floors', [WorkspaceController::class, 'listFloors']);
                Route::post('/floors', [WorkspaceController::class, 'createFloor'])
                    ->middleware('permission:organizations.manage');

                // Workspace: Maps
                Route::get('/maps', [WorkspaceController::class, 'listMaps']);
                Route::post('/maps', [WorkspaceController::class, 'createMap'])
                    ->middleware('permission:organizations.manage');
                Route::get('/maps/{map}', [WorkspaceController::class, 'showMap']);
                Route::patch('/maps/{map}', [WorkspaceController::class, 'updateMap'])
                    ->middleware('permission:organizations.manage');
                Route::post('/maps/{map}/publish', [WorkspaceController::class, 'publishMap'])
                    ->middleware('permission:organizations.manage');
                Route::post('/maps/{map}/background', [WorkspaceController::class, 'uploadBackground'])
                    ->middleware(['permission:organizations.manage', 'throttle:uploads']);
                Route::get('/maps/{map}/versions', [WorkspaceController::class, 'getMapVersions']);

                // Workspace: Rooms & Zones & Objects
                Route::post('/rooms', [WorkspaceController::class, 'createRoom'])
                    ->middleware('permission:organizations.manage');
                Route::patch('/rooms/{room}', [WorkspaceController::class, 'updateRoom'])
                    ->middleware('permission:organizations.manage');
                Route::delete('/rooms/{room}', [WorkspaceController::class, 'deleteRoom'])
                    ->middleware('permission:organizations.manage');

                Route::post('/zones', [WorkspaceController::class, 'createZone'])
                    ->middleware('permission:organizations.manage');
                Route::delete('/zones/{zone}', [WorkspaceController::class, 'deleteZone'])
                    ->middleware('permission:organizations.manage');

                Route::post('/maps/{map}/objects/sync', [WorkspaceController::class, 'syncObjects'])
                    ->middleware('permission:organizations.manage');

                // ── Chat Domain ──
                Route::get('/channels', [ChatController::class, 'listChannels']);
                Route::get('/users/{targetUser}/dm', [ChatController::class, 'getOrCreateDm']);
                Route::get('/channels/{channel}/messages', [ChatController::class, 'listMessages']);
                Route::post('/channels/{channel}/messages', [ChatController::class, 'sendMessage'])->middleware('throttle:chat');

                // ── Meetings & LiveKit Domain ──
                Route::get('/meetings', [MeetingController::class, 'listMeetings']);
                Route::post('/meetings', [MeetingController::class, 'createMeeting']);
                Route::post('/meetings/{meeting}/end', [MeetingController::class, 'endMeeting']);
                Route::post('/rooms/{room}/livekit-token', [MeetingController::class, 'getLiveKitToken']);

                // ── Guest Invitations Domain ──
                Route::post('/rooms/{room}/guest-invitations', [GuestController::class, 'createInvitation']);

                // ── Session & Meeting Recordings Gallery ──
                Route::get('/recordings', [RecordingController::class, 'index']);
                Route::post('/recordings', [RecordingController::class, 'store']);
                Route::delete('/recordings/{recording}', [RecordingController::class, 'destroy']);

                // ── Projects Domain ──
                Route::get('/projects', [ProjectController::class, 'index'])
                    ->middleware('permission:projects.view');
                Route::post('/projects', [ProjectController::class, 'store'])
                    ->middleware('permission:projects.create');
                Route::get('/projects/{project}', [ProjectController::class, 'show'])
                    ->middleware('permission:projects.view');
                Route::patch('/projects/{project}', [ProjectController::class, 'update'])
                    ->middleware('permission:projects.edit');
                Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])
                    ->middleware('permission:projects.delete');

                // ClickUp Multi-Views & Advance Modules
                Route::get('/projects/{project}/gantt', [ProjectController::class, 'gantt'])
                    ->middleware('permission:projects.view');
                Route::get('/projects/{project}/workload', [ProjectController::class, 'workload'])
                    ->middleware('permission:projects.view');

                // ClickUp Custom Fields
                Route::get('/projects/{project}/custom-fields', [ProjectController::class, 'customFields'])
                    ->middleware('permission:projects.view');
                Route::post('/projects/{project}/custom-fields', [ProjectController::class, 'storeCustomField'])
                    ->middleware('permission:projects.edit');

                // ClickUp Docs / Wiki
                Route::get('/projects/{project}/docs', [ProjectController::class, 'documents'])
                    ->middleware('permission:projects.view');
                Route::post('/projects/{project}/docs', [ProjectController::class, 'storeDocument'])
                    ->middleware('permission:projects.edit');
                Route::put('/projects/{project}/docs/{document}', [ProjectController::class, 'updateDocument'])
                    ->middleware('permission:projects.edit');
                Route::delete('/projects/{project}/docs/{document}', [ProjectController::class, 'destroyDocument'])
                    ->middleware('permission:projects.delete');

                // ClickUp Goals & Targets
                Route::get('/projects/{project}/goals', [ProjectController::class, 'goals'])
                    ->middleware('permission:projects.view');
                Route::post('/projects/{project}/goals', [ProjectController::class, 'storeGoal'])
                    ->middleware('permission:projects.edit');
                Route::post('/projects/{project}/goals/{goal}/targets', [ProjectController::class, 'storeGoalTarget'])
                    ->middleware('permission:projects.edit');
                Route::patch('/projects/{project}/goals/{goal}/targets/{target}', [ProjectController::class, 'updateGoalTarget'])
                    ->middleware('permission:projects.edit');

                // ClickUp Sprints
                Route::get('/projects/{project}/sprints', [ProjectController::class, 'sprints'])
                    ->middleware('permission:projects.view');
                Route::post('/projects/{project}/sprints', [ProjectController::class, 'storeSprint'])
                    ->middleware('permission:projects.edit');

                // Milestones & Roadmap
                Route::get('/projects/{project}/milestones', [ProjectController::class, 'milestones'])
                    ->middleware('permission:projects.view');
                Route::post('/projects/{project}/milestones', [ProjectController::class, 'storeMilestone'])
                    ->middleware('permission:projects.edit');
                Route::patch('/projects/{project}/milestones/{milestone}', [ProjectController::class, 'updateMilestone'])
                    ->middleware('permission:projects.edit');
                Route::delete('/projects/{project}/milestones/{milestone}', [ProjectController::class, 'destroyMilestone'])
                    ->middleware('permission:projects.edit');

                // ── Tasks Domain ──
                Route::get('/tasks', [TaskController::class, 'index'])
                    ->middleware('permission:tasks.view');
                Route::get('/tasks/my-tasks', [TaskController::class, 'myTasks'])
                    ->middleware('permission:tasks.view');
                Route::post('/tasks', [TaskController::class, 'store'])
                    ->middleware('permission:tasks.create');
                Route::get('/tasks/{task}', [TaskController::class, 'show'])
                    ->middleware('permission:tasks.view');
                Route::get('/tasks/{task}/activity', [TaskController::class, 'activity'])
                    ->middleware('permission:tasks.view');
                Route::patch('/tasks/{task}', [TaskController::class, 'update'])
                    ->middleware('permission:tasks.edit');
                Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])
                    ->middleware('permission:tasks.edit');
                Route::patch('/tasks/{task}/assign', [TaskController::class, 'assign'])
                    ->middleware('permission:tasks.assign');
                Route::patch('/tasks/{task}/milestone', [TaskController::class, 'setMilestone'])
                    ->middleware('permission:tasks.edit');
                Route::post('/tasks/bulk', [TaskController::class, 'bulkUpdate'])
                    ->middleware('permission:tasks.edit');
                Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
                    ->middleware('permission:tasks.delete');
                Route::post('/tasks/{task}/checklist', [TaskController::class, 'addChecklistItem'])
                    ->middleware('permission:tasks.edit');
                Route::patch('/tasks/{task}/checklist/{item}', [TaskController::class, 'toggleChecklistItem'])
                    ->middleware('permission:tasks.edit');
                Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment'])
                    ->middleware('permission:tasks.view');
                Route::post('/tasks/{task}/dependencies', [TaskController::class, 'addDependency'])
                    ->middleware('permission:tasks.edit');
                Route::delete('/tasks/{task}/dependencies/{dependency}', [TaskController::class, 'removeDependency'])
                    ->middleware('permission:tasks.edit');
                Route::post('/tasks/{task}/duplicate', [TaskController::class, 'duplicate'])
                    ->middleware('permission:tasks.create');
                Route::post('/tasks/{task}/move', [TaskController::class, 'move'])
                    ->middleware('permission:tasks.edit');
                Route::post('/tasks/{task}/custom-fields', [TaskController::class, 'setCustomFieldValue'])
                    ->middleware('permission:tasks.edit');
                Route::patch('/tasks/{task}/sprint', [TaskController::class, 'setSprint'])
                    ->middleware('permission:tasks.edit');

                // ── Time Tracking & Live Timers ──
                Route::get('/time/active-timer', [TimeTrackingController::class, 'getActiveTimer'])
                    ->middleware('permission:time.view');
                Route::post('/time/timer/start', [TimeTrackingController::class, 'startTimer'])
                    ->middleware('permission:time.create');
                Route::post('/time/timer/stop', [TimeTrackingController::class, 'stopTimer'])
                    ->middleware('permission:time.create');
                Route::post('/time/entries/manual', [TimeTrackingController::class, 'logManual'])
                    ->middleware('permission:time.create');
                Route::get('/time/entries', [TimeTrackingController::class, 'index'])
                    ->middleware('permission:time.view');
                Route::patch('/time/entries/{timeEntry}', [TimeTrackingController::class, 'update'])
                    ->middleware('permission:time.edit');
                Route::delete('/time/entries/{timeEntry}', [TimeTrackingController::class, 'destroy'])
                    ->middleware('permission:time.delete');

                // ── Time Entries Route Aliases ──
                Route::post('/time-entries/timer/start', [TimeTrackingController::class, 'startTimer']);
                Route::post('/time-entries/timer/stop', [TimeTrackingController::class, 'stopTimer']);
                Route::post('/time-entries/manual', [TimeTrackingController::class, 'logManual']);
                Route::get('/time-entries', [TimeTrackingController::class, 'index']);

                // ── Timesheets Domain ──
                Route::get('/timesheets', [TimesheetController::class, 'index'])
                    ->middleware('permission:timesheets.view');
                Route::get('/timesheets/my-current', [TimesheetController::class, 'myCurrent'])
                    ->middleware('permission:timesheets.view');
                Route::get('/timesheets/{timesheet}', [TimesheetController::class, 'show'])
                    ->middleware('permission:timesheets.view');
                Route::post('/timesheets/submit', [TimesheetController::class, 'submit'])
                    ->middleware('permission:timesheets.submit');
                Route::post('/timesheets/{timesheet}/approve', [TimesheetController::class, 'approve'])
                    ->middleware('permission:timesheets.approve');
                Route::post('/timesheets/{timesheet}/reject', [TimesheetController::class, 'reject'])
                    ->middleware('permission:timesheets.approve');

                // ── Attendance & Clock In/Out Domain ──
                Route::get('/attendance/summary', [AttendanceApiController::class, 'summary']);
                Route::post('/attendance/clock-in', [AttendanceApiController::class, 'clockIn']);
                Route::post('/attendance/clock-out', [AttendanceApiController::class, 'clockOut']);
                Route::get('/attendance/logs', [AttendanceApiController::class, 'logs']);

                // ── Billing & Regional Subscriptions Domain ──
                Route::get('/billing/plans', [BillingApiController::class, 'plans']);
                Route::get('/billing/subscription', [BillingApiController::class, 'subscription']);
                Route::post('/billing/checkout', [BillingApiController::class, 'checkout']);

                // ── Spatial Interactions & Knock / Wave / Ring Domain ──
                Route::post('/interactions/knock', [SpatialInteractionsApiController::class, 'knock'])->middleware('throttle:notifications');
                Route::post('/interactions/wave', [SpatialInteractionsApiController::class, 'wave'])->middleware('throttle:notifications');
                Route::post('/interactions/ring', [SpatialInteractionsApiController::class, 'ring'])->middleware('throttle:notifications');

                // ── WebRTC & LiveKit Meetings Domain ──
                Route::get('/meetings', [MeetingController::class, 'listMeetings']);
                Route::post('/meetings', [MeetingController::class, 'createMeeting']);
                Route::post('/meetings/{meeting}/end', [MeetingController::class, 'endMeeting']);
                Route::post('/meetings/{meeting}/token', [MeetingController::class, 'getMeetingToken']);
                Route::post('/rooms/{room}/livekit-token', [MeetingController::class, 'getLiveKitToken']);
                Route::get('/webrtc/diagnostics-config', [MeetingController::class, 'getDiagnosticsConfig']);
            });

        // ── In-App Notifications (authenticated) ──
        Route::get('/notifications', [SpatialInteractionsApiController::class, 'notifications']);
        Route::post('/notifications/{id}/read', [SpatialInteractionsApiController::class, 'markAsRead']);

        // ── Plans (public listing) ──
        Route::get('/plans', function () {
            return response()->json([
                'plans' => Plan::where('is_active', true)->get(),
            ]);
        });
    });

    // ── Public Guest Verification ──
    Route::get('/guest-invitations/{token}', [GuestController::class, 'verifyToken'])->middleware('throttle:guest-token');
});
