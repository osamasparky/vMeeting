<?php

use App\Domains\Identity\Controllers\AuthController;
use App\Domains\Tenancy\Controllers\OrganizationController;
use App\Domains\People\Controllers\PeopleController;
use App\Domains\People\Controllers\ProfileController;
use App\Domains\Administration\Controllers\AdminController;
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
                Route::post('/realtime-token', function (\App\Domains\Tenancy\Models\Organization $organization, \App\Domains\Identity\Services\RealtimeTokenService $service) {
                    return response()->json([
                        'token' => $service->generateToken(\Illuminate\Support\Facades\Auth::user(), $organization),
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
                Route::get('/floors', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'listFloors']);
                Route::post('/floors', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'createFloor'])
                    ->middleware('permission:organizations.manage');

                // Workspace: Maps
                Route::get('/maps', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'listMaps']);
                Route::post('/maps', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'createMap'])
                    ->middleware('permission:organizations.manage');
                Route::get('/maps/{map}', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'showMap']);
                Route::patch('/maps/{map}', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'updateMap'])
                    ->middleware('permission:organizations.manage');
                Route::post('/maps/{map}/publish', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'publishMap'])
                    ->middleware('permission:organizations.manage');
                Route::post('/maps/{map}/background', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'uploadBackground'])
                    ->middleware('permission:organizations.manage');
                Route::get('/maps/{map}/versions', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'getMapVersions']);

                // Workspace: Rooms & Zones & Objects
                Route::post('/rooms', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'createRoom'])
                    ->middleware('permission:organizations.manage');
                Route::patch('/rooms/{room}', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'updateRoom'])
                    ->middleware('permission:organizations.manage');
                Route::delete('/rooms/{room}', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'deleteRoom'])
                    ->middleware('permission:organizations.manage');

                Route::post('/zones', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'createZone'])
                    ->middleware('permission:organizations.manage');
                Route::delete('/zones/{zone}', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'deleteZone'])
                    ->middleware('permission:organizations.manage');

                Route::post('/maps/{map}/objects/sync', [\App\Domains\Workspace\Controllers\WorkspaceController::class, 'syncObjects'])
                    ->middleware('permission:organizations.manage');

                // ── Chat Domain ──
                Route::get('/channels', [\App\Domains\Chat\Controllers\ChatController::class, 'listChannels']);
                Route::get('/users/{targetUser}/dm', [\App\Domains\Chat\Controllers\ChatController::class, 'getOrCreateDm']);
                Route::get('/channels/{channel}/messages', [\App\Domains\Chat\Controllers\ChatController::class, 'listMessages']);
                Route::post('/channels/{channel}/messages', [\App\Domains\Chat\Controllers\ChatController::class, 'sendMessage']);

                // ── Meetings & LiveKit Domain ──
                Route::get('/meetings', [\App\Domains\Meetings\Controllers\MeetingController::class, 'listMeetings']);
                Route::post('/meetings', [\App\Domains\Meetings\Controllers\MeetingController::class, 'createMeeting']);
                Route::post('/meetings/{meeting}/end', [\App\Domains\Meetings\Controllers\MeetingController::class, 'endMeeting']);
                Route::post('/rooms/{room}/livekit-token', [\App\Domains\Meetings\Controllers\MeetingController::class, 'getLiveKitToken']);

                // ── Guest Invitations Domain ──
                Route::post('/rooms/{room}/guest-invitations', [\App\Domains\Guests\Controllers\GuestController::class, 'createInvitation']);

                // ── Session & Meeting Recordings Gallery ──
                Route::get('/recordings', [\App\Domains\Collaboration\Controllers\RecordingController::class, 'index']);
                Route::post('/recordings', [\App\Domains\Collaboration\Controllers\RecordingController::class, 'store']);
                Route::delete('/recordings/{recording}', [\App\Domains\Collaboration\Controllers\RecordingController::class, 'destroy']);

                // ── Projects Domain ──
                Route::get('/projects', [\App\Domains\Projects\Controllers\ProjectController::class, 'index'])
                    ->middleware('permission:projects.view');
                Route::post('/projects', [\App\Domains\Projects\Controllers\ProjectController::class, 'store'])
                    ->middleware('permission:projects.create');
                Route::get('/projects/{project}', [\App\Domains\Projects\Controllers\ProjectController::class, 'show'])
                    ->middleware('permission:projects.view');
                Route::patch('/projects/{project}', [\App\Domains\Projects\Controllers\ProjectController::class, 'update'])
                    ->middleware('permission:projects.edit');
                Route::delete('/projects/{project}', [\App\Domains\Projects\Controllers\ProjectController::class, 'destroy'])
                    ->middleware('permission:projects.delete');

                // ClickUp Multi-Views & Advance Modules
                Route::get('/projects/{project}/gantt', [\App\Domains\Projects\Controllers\ProjectController::class, 'gantt'])
                    ->middleware('permission:projects.view');
                Route::get('/projects/{project}/workload', [\App\Domains\Projects\Controllers\ProjectController::class, 'workload'])
                    ->middleware('permission:projects.view');

                // ClickUp Custom Fields
                Route::get('/projects/{project}/custom-fields', [\App\Domains\Projects\Controllers\ProjectController::class, 'customFields'])
                    ->middleware('permission:projects.view');
                Route::post('/projects/{project}/custom-fields', [\App\Domains\Projects\Controllers\ProjectController::class, 'storeCustomField'])
                    ->middleware('permission:projects.edit');

                // ClickUp Docs / Wiki
                Route::get('/projects/{project}/docs', [\App\Domains\Projects\Controllers\ProjectController::class, 'documents'])
                    ->middleware('permission:projects.view');
                Route::post('/projects/{project}/docs', [\App\Domains\Projects\Controllers\ProjectController::class, 'storeDocument'])
                    ->middleware('permission:projects.edit');
                Route::put('/projects/{project}/docs/{document}', [\App\Domains\Projects\Controllers\ProjectController::class, 'updateDocument'])
                    ->middleware('permission:projects.edit');
                Route::delete('/projects/{project}/docs/{document}', [\App\Domains\Projects\Controllers\ProjectController::class, 'destroyDocument'])
                    ->middleware('permission:projects.delete');

                // ClickUp Goals & Targets
                Route::get('/projects/{project}/goals', [\App\Domains\Projects\Controllers\ProjectController::class, 'goals'])
                    ->middleware('permission:projects.view');
                Route::post('/projects/{project}/goals', [\App\Domains\Projects\Controllers\ProjectController::class, 'storeGoal'])
                    ->middleware('permission:projects.edit');
                Route::post('/projects/{project}/goals/{goal}/targets', [\App\Domains\Projects\Controllers\ProjectController::class, 'storeGoalTarget'])
                    ->middleware('permission:projects.edit');
                Route::patch('/projects/{project}/goals/{goal}/targets/{target}', [\App\Domains\Projects\Controllers\ProjectController::class, 'updateGoalTarget'])
                    ->middleware('permission:projects.edit');

                // ClickUp Sprints
                Route::get('/projects/{project}/sprints', [\App\Domains\Projects\Controllers\ProjectController::class, 'sprints'])
                    ->middleware('permission:projects.view');
                Route::post('/projects/{project}/sprints', [\App\Domains\Projects\Controllers\ProjectController::class, 'storeSprint'])
                    ->middleware('permission:projects.edit');

                // Milestones & Roadmap
                Route::get('/projects/{project}/milestones', [\App\Domains\Projects\Controllers\ProjectController::class, 'milestones'])
                    ->middleware('permission:projects.view');
                Route::post('/projects/{project}/milestones', [\App\Domains\Projects\Controllers\ProjectController::class, 'storeMilestone'])
                    ->middleware('permission:projects.edit');
                Route::patch('/projects/{project}/milestones/{milestone}', [\App\Domains\Projects\Controllers\ProjectController::class, 'updateMilestone'])
                    ->middleware('permission:projects.edit');
                Route::delete('/projects/{project}/milestones/{milestone}', [\App\Domains\Projects\Controllers\ProjectController::class, 'destroyMilestone'])
                    ->middleware('permission:projects.edit');

                // ── Tasks Domain ──
                Route::get('/tasks', [\App\Domains\Projects\Controllers\TaskController::class, 'index'])
                    ->middleware('permission:tasks.view');
                Route::get('/tasks/my-tasks', [\App\Domains\Projects\Controllers\TaskController::class, 'myTasks'])
                    ->middleware('permission:tasks.view');
                Route::post('/tasks', [\App\Domains\Projects\Controllers\TaskController::class, 'store'])
                    ->middleware('permission:tasks.create');
                Route::get('/tasks/{task}', [\App\Domains\Projects\Controllers\TaskController::class, 'show'])
                    ->middleware('permission:tasks.view');
                Route::get('/tasks/{task}/activity', [\App\Domains\Projects\Controllers\TaskController::class, 'activity'])
                    ->middleware('permission:tasks.view');
                Route::patch('/tasks/{task}', [\App\Domains\Projects\Controllers\TaskController::class, 'update'])
                    ->middleware('permission:tasks.edit');
                Route::patch('/tasks/{task}/status', [\App\Domains\Projects\Controllers\TaskController::class, 'updateStatus'])
                    ->middleware('permission:tasks.edit');
                Route::patch('/tasks/{task}/assign', [\App\Domains\Projects\Controllers\TaskController::class, 'assign'])
                    ->middleware('permission:tasks.assign');
                Route::patch('/tasks/{task}/milestone', [\App\Domains\Projects\Controllers\TaskController::class, 'setMilestone'])
                    ->middleware('permission:tasks.edit');
                Route::delete('/tasks/{task}', [\App\Domains\Projects\Controllers\TaskController::class, 'destroy'])
                    ->middleware('permission:tasks.delete');
                Route::post('/tasks/{task}/checklist', [\App\Domains\Projects\Controllers\TaskController::class, 'addChecklistItem'])
                    ->middleware('permission:tasks.edit');
                Route::patch('/tasks/{task}/checklist/{item}', [\App\Domains\Projects\Controllers\TaskController::class, 'toggleChecklistItem'])
                    ->middleware('permission:tasks.edit');
                Route::post('/tasks/{task}/comments', [\App\Domains\Projects\Controllers\TaskController::class, 'addComment'])
                    ->middleware('permission:tasks.view');
                Route::post('/tasks/{task}/dependencies', [\App\Domains\Projects\Controllers\TaskController::class, 'addDependency'])
                    ->middleware('permission:tasks.edit');
                Route::post('/tasks/{task}/duplicate', [\App\Domains\Projects\Controllers\TaskController::class, 'duplicate'])
                    ->middleware('permission:tasks.create');
                Route::post('/tasks/{task}/move', [\App\Domains\Projects\Controllers\TaskController::class, 'move'])
                    ->middleware('permission:tasks.edit');
                Route::post('/tasks/{task}/custom-fields', [\App\Domains\Projects\Controllers\TaskController::class, 'setCustomFieldValue'])
                    ->middleware('permission:tasks.edit');
                Route::patch('/tasks/{task}/sprint', [\App\Domains\Projects\Controllers\TaskController::class, 'setSprint'])
                    ->middleware('permission:tasks.edit');

                // ── Time Tracking & Live Timers ──
                Route::get('/time/active-timer', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'getActiveTimer'])
                    ->middleware('permission:time.view');
                Route::post('/time/timer/start', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'startTimer'])
                    ->middleware('permission:time.create');
                Route::post('/time/timer/stop', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'stopTimer'])
                    ->middleware('permission:time.create');
                Route::post('/time/entries/manual', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'logManual'])
                    ->middleware('permission:time.create');
                Route::get('/time/entries', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'index'])
                    ->middleware('permission:time.view');
                Route::patch('/time/entries/{timeEntry}', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'update'])
                    ->middleware('permission:time.edit');
                Route::delete('/time/entries/{timeEntry}', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'destroy'])
                    ->middleware('permission:time.delete');

                // ── Time Entries Route Aliases ──
                Route::post('/time-entries/timer/start', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'startTimer']);
                Route::post('/time-entries/timer/stop', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'stopTimer']);
                Route::post('/time-entries/manual', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'logManual']);
                Route::get('/time-entries', [\App\Domains\Projects\Controllers\TimeTrackingController::class, 'index']);

                // ── Timesheets Domain ──
                Route::get('/timesheets', [\App\Domains\Projects\Controllers\TimesheetController::class, 'index'])
                    ->middleware('permission:timesheets.view');
                Route::get('/timesheets/my-current', [\App\Domains\Projects\Controllers\TimesheetController::class, 'myCurrent'])
                    ->middleware('permission:timesheets.view');
                Route::get('/timesheets/{timesheet}', [\App\Domains\Projects\Controllers\TimesheetController::class, 'show'])
                    ->middleware('permission:timesheets.view');
                Route::post('/timesheets/submit', [\App\Domains\Projects\Controllers\TimesheetController::class, 'submit'])
                    ->middleware('permission:timesheets.submit');
                Route::post('/timesheets/{timesheet}/approve', [\App\Domains\Projects\Controllers\TimesheetController::class, 'approve'])
                    ->middleware('permission:timesheets.approve');
                Route::post('/timesheets/{timesheet}/reject', [\App\Domains\Projects\Controllers\TimesheetController::class, 'reject'])
                    ->middleware('permission:timesheets.approve');

                // ── WebRTC & LiveKit Meetings Domain ──
                Route::get('/meetings', [\App\Domains\Meetings\Controllers\MeetingController::class, 'listMeetings']);
                Route::post('/meetings', [\App\Domains\Meetings\Controllers\MeetingController::class, 'createMeeting']);
                Route::post('/meetings/{meeting}/end', [\App\Domains\Meetings\Controllers\MeetingController::class, 'endMeeting']);
                Route::post('/meetings/{meeting}/token', [\App\Domains\Meetings\Controllers\MeetingController::class, 'getMeetingToken']);
                Route::post('/rooms/{room}/livekit-token', [\App\Domains\Meetings\Controllers\MeetingController::class, 'getLiveKitToken']);
                Route::get('/webrtc/diagnostics-config', [\App\Domains\Meetings\Controllers\MeetingController::class, 'getDiagnosticsConfig']);
            });

        // ── Plans (public listing) ──
        Route::get('/plans', function () {
            return response()->json([
                'plans' => \App\Domains\Tenancy\Models\Plan::where('is_active', true)->get(),
            ]);
        });
    });

    // ── Public Guest Verification ──
    Route::get('/guest-invitations/{token}', [\App\Domains\Guests\Controllers\GuestController::class, 'verifyToken']);
});

