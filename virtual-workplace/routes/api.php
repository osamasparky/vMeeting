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
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
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

