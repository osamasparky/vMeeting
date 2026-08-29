<?php

use App\Domains\Chat\Controllers\ChatController;
use App\Domains\Collaboration\Controllers\RecordingController;
use App\Domains\Meetings\Controllers\MeetingController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\GuestAccessController;
use App\Http\Controllers\Web\OfficeController;
use App\Http\Controllers\Web\OrganizationSettingsController;
use App\Http\Controllers\Web\ProjectHubController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'home'])->name('landing.home');

// Language Switcher
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
        cookie()->queue('locale', $locale, 525600);
    }
    $referer = request()->header('referer');
    if ($referer) {
        return redirect($referer)->withCookie(cookie('locale', $locale, 525600));
    }

    return redirect()->route('dashboard')->withCookie(cookie('locale', $locale, 525600));
})->name('lang.switch');

// Authentication Routes (Web UI)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('/organization/upgrade-plan', [OrganizationSettingsController::class, 'upgradePlan'])->name('organization.upgrade_plan');
    Route::get('/billing/payment/{plan}', [OrganizationSettingsController::class, 'showPaymentPage'])->name('subscription.payment');
    Route::post('/billing/payment/{plan}/submit', [OrganizationSettingsController::class, 'submitBankTransferPayment'])->name('subscription.payment.submit');
    Route::post('/billing/payment-requests/{subscriptionRequest}/cancel', [OrganizationSettingsController::class, 'cancelSubscriptionRequest'])->name('subscription.payment.cancel');
    Route::post('/organization/settings', [OrganizationSettingsController::class, 'updateOrganizationSettings'])->name('organization.settings.update');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/office', [OfficeController::class, 'office'])->name('office');
    Route::get('/editor', [OfficeController::class, 'editor'])->name('editor');
    Route::post('/editor/maps/{map}/background', [OfficeController::class, 'uploadMapBackground'])->name('editor.maps.background');
    Route::delete('/editor/maps/{map}/background', [OfficeController::class, 'deleteMapBackground'])->name('editor.maps.background.delete');
    Route::post('/editor/maps/{map}/save', [OfficeController::class, 'saveEditorMap'])->name('editor.maps.save');
    Route::post('/editor/maps/{map}/clear', [OfficeController::class, 'clearEditorMap'])->name('editor.maps.clear');
    Route::post('/editor/maps/{map}/publish', [OfficeController::class, 'publishEditorMap'])->name('editor.maps.publish');
    Route::post('/editor/rooms', [OfficeController::class, 'saveEditorRoom'])->name('editor.rooms.store');
    Route::patch('/editor/rooms/{room}', [OfficeController::class, 'updateEditorRoom'])->name('editor.rooms.update');
    Route::delete('/editor/rooms/{room}', [OfficeController::class, 'deleteEditorRoom'])->name('editor.rooms.destroy');
    Route::get('/projects/{project}', [ProjectHubController::class, 'show'])->name('projects.hub');

    // Multi-Office & Branches Management
    Route::post('/offices', [OfficeController::class, 'storeOffice'])->name('offices.store');
    Route::put('/offices/{floor}', [OfficeController::class, 'updateOffice'])->name('offices.update');
    Route::delete('/offices/{floor}', [OfficeController::class, 'deleteOffice'])->name('offices.delete');
    Route::post('/organization/ai-map/generate', [OfficeController::class, 'generateAiOffice'])->name('organization.ai_map.generate');

    // Departments & Teams Management
    Route::post('/departments', [OrganizationSettingsController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{department}', [OrganizationSettingsController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [OrganizationSettingsController::class, 'deleteDepartment'])->name('departments.delete');
    Route::post('/teams', [OrganizationSettingsController::class, 'storeTeam'])->name('teams.store');
    Route::put('/teams/{team}', [OrganizationSettingsController::class, 'updateTeam'])->name('teams.update');
    Route::delete('/teams/{team}', [OrganizationSettingsController::class, 'deleteTeam'])->name('teams.delete');

    // Organization Attendance & Policies
    Route::post('/organization/smtp-test', [OrganizationSettingsController::class, 'testSmtpConnection'])->name('organization.smtp.test');
    Route::post('/organization/ai-test', [OrganizationSettingsController::class, 'testOrgAiConnection'])->name('organization.ai.test');

    // Projects & Files Storage
    Route::post('/projects/{project}/files', [ProjectHubController::class, 'storeFile'])->name('projects.files.store');
    Route::delete('/projects/{project}/files/{file}', [ProjectHubController::class, 'destroyFile'])->name('projects.files.destroy');
    Route::post('/tasks/{task}/attachments', [ProjectHubController::class, 'uploadTaskAttachment'])->name('tasks.attachments.store');
    Route::delete('/tasks/{task}/attachments/{attachment}', [ProjectHubController::class, 'deleteTaskAttachment'])->name('tasks.attachments.destroy');

    // Task Comments & Mentions
    Route::get('/tasks/{task}/comments', [ProjectHubController::class, 'getTaskComments'])->name('tasks.comments.index');
    Route::post('/tasks/{task}/comments', [ProjectHubController::class, 'addComment'])->name('tasks.comments.store');

    // Task PM Review & Approvals
    Route::post('/tasks/{task}/approve', [ProjectHubController::class, 'approveTask'])->name('tasks.approve');
    Route::post('/tasks/{task}/reject', [ProjectHubController::class, 'rejectTask'])->name('tasks.reject');

    Route::get('/organization/members/{member}/details', [OrganizationSettingsController::class, 'getMemberProfileDetails'])->name('organization.members.details');
    Route::post('/organization/members/create', [OrganizationSettingsController::class, 'storeMember'])->name('organization.members.store');
    Route::post('/members/{member}/assign', [OrganizationSettingsController::class, 'assignMemberDepartment'])->name('members.assign_department');
    Route::put('/organization/members/{member}', [OrganizationSettingsController::class, 'updateOrganizationMember'])->name('organization.members.update');
    Route::post('/organization/members/{member}/password', [OrganizationSettingsController::class, 'updateMemberPassword'])->name('organization.members.password');
    Route::delete('/organization/members/{member}', [OrganizationSettingsController::class, 'deleteOrganizationMember'])->name('organization.members.delete');

    // Web Chat & Direct Messaging Routes
    Route::get('/chat/conversations', [ChatController::class, 'webConversations'])->name('chat.conversations');
    Route::get('/chat/dm/{targetUser}', [ChatController::class, 'webGetOrCreateDm'])->name('chat.dm');
    Route::get('/chat/channels/{channel}/messages', [ChatController::class, 'webListMessages'])->name('chat.messages.list');
    Route::post('/chat/channels/{channel}/messages', [ChatController::class, 'webSendMessage'])->name('chat.messages.send');

    // Bulk Clear Routes
    Route::post('/organization/guest-invitations/clear', [GuestAccessController::class, 'clearGuestInvitations'])->name('guest_invitations.clear');
    Route::post('/organization/audit-logs/clear', [OrganizationSettingsController::class, 'clearAuditLogs'])->name('audit_logs.clear');

    // Scheduled Meetings & SMTP / AI Test Routes
    Route::get('/meetings/schedule', fn () => redirect('/dashboard#meetings'))->name('meetings.schedule.get');
    Route::post('/meetings/schedule', [DashboardController::class, 'storeScheduledMeeting'])->name('meetings.schedule');
    Route::post('/meetings/{meeting}/cancel', [DashboardController::class, 'cancelMeeting'])->name('meetings.cancel');
    Route::post('/organization/smtp/test', [OrganizationSettingsController::class, 'testSmtpConnection'])->name('organization.smtp.test.custom');
    Route::post('/organization/ai/test', [OrganizationSettingsController::class, 'testOrgAiConnection'])->name('organization.ai.test.custom');

    // Organization Member Impersonation
    Route::post('/organization/members/{member}/impersonate', [AuthController::class, 'impersonateMember'])->name('organization.members.impersonate');
    Route::post('/organization/impersonate/leave', [AuthController::class, 'leaveMemberImpersonation'])->name('organization.members.impersonate.leave');
    Route::get('/organization/impersonate/leave', [AuthController::class, 'leaveMemberImpersonation'])->name('organization.members.impersonate.leave.get');

    // Recordings Gallery Routes (Web Session)
    Route::get('/organizations/{organization}/recordings', [RecordingController::class, 'index'])->name('recordings.index');
    Route::post('/organizations/{organization}/recordings', [RecordingController::class, 'store'])->name('recordings.store');
    Route::get('/organizations/{organization}/recordings/{recording}/download', [RecordingController::class, 'download'])->name('recordings.download');
    Route::delete('/organizations/{organization}/recordings/{recording}', [RecordingController::class, 'destroy'])->name('recordings.destroy');

    // Impersonation Exit Routes (SuperAdmin)
    Route::post('/impersonate/leave', [SuperAdminController::class, 'leaveImpersonation'])->name('impersonate.leave');
    Route::get('/impersonate/leave', [SuperAdminController::class, 'leaveImpersonation'])->name('impersonate.leave.get');

    // In-App Notification System Routes
    Route::get('/api/notifications', [DashboardController::class, 'getUserNotifications'])->name('notifications.index');
    Route::post('/api/notifications/{id}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/api/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read_all');
    Route::delete('/api/notifications/clear', [DashboardController::class, 'clearAllNotifications'])->name('notifications.clear');
    Route::post('/api/notifications/wave', [OfficeController::class, 'sendDirectWave'])->name('notifications.wave');
    Route::post('/api/notifications/knock', [OfficeController::class, 'sendDoorKnock'])->name('notifications.knock');
});

// Hybrid Workplace & Media Plane Routes (Accessible by Authenticated Members and Invited Guests)
Route::post('/organizations/{organization}/rooms/{room}/livekit-token', [MeetingController::class, 'getLiveKitToken'])->name('web.rooms.livekit_token');
Route::get('/organizations/{organization}/webrtc/diagnostics-config', [MeetingController::class, 'getDiagnosticsConfig'])->name('web.webrtc.diagnostics');
Route::get('/organizations/{organization}/rooms/{room}/files', [OfficeController::class, 'listRoomFiles'])->name('room_files.index');
Route::post('/organizations/{organization}/rooms/{room}/files', [OfficeController::class, 'uploadRoomFile'])->name('room_files.store');
Route::delete('/organizations/{organization}/rooms/{room}/files/{file}', [OfficeController::class, 'deleteRoomFile'])->name('room_files.destroy');
Route::post('/organizations/{organization}/chat/upload', [DashboardController::class, 'uploadChatAttachment'])->name('chat.upload');
Route::post('/api/office/attendance/log', [AttendanceController::class, 'logRoomAttendance'])->name('office.attendance.log');
Route::get('/api/office/attendance/summary', [AttendanceController::class, 'getAttendanceSummary'])->name('office.attendance.summary');
Route::get('/api/timesheets/daily-summary', [AttendanceController::class, 'getDailyTimesheetsReport'])->name('timesheets.daily_summary');
Route::get('/api/office/my-tasks', [AttendanceController::class, 'getOfficeTasksAndTimer'])->name('office.my_tasks');
Route::get('/api/members/{userId}/activity', [AttendanceController::class, 'memberActivity'])->name('members.activity');
Route::post('/api/office/task-timer/start', [AttendanceController::class, 'startOfficeTaskTimer'])->name('office.task_timer.start');
Route::post('/api/office/task-timer/stop', [AttendanceController::class, 'stopOfficeTaskTimer'])->name('office.task_timer.stop');
Route::post('/api/office/tasks/{taskId}/status', [AttendanceController::class, 'updateOfficeTaskStatus'])->name('office.tasks.status');

// Guest Access Routes (Public / Unauthenticated)
Route::get('/guest/join/{token}', [GuestAccessController::class, 'guestJoin'])->name('guest.join');
Route::post('/guest/join/{token}', [GuestAccessController::class, 'guestEnter'])->middleware('throttle:10,1')->name('guest.enter');

// Super Admin Portal Routes
Route::prefix('superadmin')->middleware(['auth', 'superadmin'])->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/companies', [SuperAdminController::class, 'companies'])->name('companies');
    Route::get('/companies/{organization}', [SuperAdminController::class, 'showCompany'])->name('companies.show');
    Route::post('/companies/{organization}/impersonate', [SuperAdminController::class, 'impersonateCompany'])->name('companies.impersonate');
    Route::put('/companies/{organization}', [SuperAdminController::class, 'updateCompanyDetails'])->name('companies.update');
    Route::delete('/companies/{organization}', [SuperAdminController::class, 'deleteCompany'])->name('companies.delete');
    Route::post('/companies/{organization}/plan', [SuperAdminController::class, 'updateCompanyPlan'])->name('companies.plan');
    Route::post('/companies/{organization}/toggle-status', [SuperAdminController::class, 'toggleCompanyStatus'])->name('companies.toggle');

    Route::get('/plans', [SuperAdminController::class, 'plans'])->name('plans');
    Route::post('/plans', [SuperAdminController::class, 'storePlan'])->name('plans.store');
    Route::put('/plans/{plan}', [SuperAdminController::class, 'updatePlan'])->name('plans.update');
    Route::delete('/plans/{plan}', [SuperAdminController::class, 'deletePlan'])->name('plans.delete');

    // Subscription & Bank Transfer Payment Requests
    Route::get('/subscriptions', [SuperAdminController::class, 'subscriptionRequests'])->name('subscriptions');
    Route::post('/subscriptions/{subscriptionRequest}/approve', [SuperAdminController::class, 'approveSubscriptionRequest'])->name('subscriptions.approve');
    Route::post('/subscriptions/{subscriptionRequest}/reject', [SuperAdminController::class, 'rejectSubscriptionRequest'])->name('subscriptions.reject');
    Route::get('/subscriptions/{subscriptionRequest}/receipt', [SuperAdminController::class, 'viewSubscriptionReceipt'])->name('subscriptions.receipt');

    Route::get('/matrix', [SuperAdminController::class, 'matrix'])->name('matrix');
    Route::post('/matrix/sync', [SuperAdminController::class, 'syncMatrix'])->name('matrix.sync');

    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [SuperAdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/payment', [SuperAdminController::class, 'updatePaymentSettings'])->name('settings.payment');
    Route::post('/settings/ai', [SuperAdminController::class, 'updateAiSettings'])->name('settings.ai');
    Route::post('/settings/ai/test', [SuperAdminController::class, 'testAiConnection'])->name('settings.ai.test');
    Route::post('/settings/default-blueprint', [SuperAdminController::class, 'uploadDefaultBlueprint'])->name('settings.blueprint');

    // System Translations & Localization Manager
    Route::get('/translations', [SuperAdminController::class, 'translations'])->name('translations');
    Route::post('/translations', [SuperAdminController::class, 'updateTranslations'])->name('translations.update');
    Route::post('/translations/add', [SuperAdminController::class, 'addTranslationKey'])->name('translations.add');
    Route::post('/translations/delete', [SuperAdminController::class, 'deleteTranslationKey'])->name('translations.delete');

    // Default Office Template & Rooms Designer
    Route::get('/template', [SuperAdminController::class, 'defaultTemplate'])->name('template');
    Route::post('/template', [SuperAdminController::class, 'updateDefaultTemplate'])->name('template.update');
    Route::post('/template/room', [SuperAdminController::class, 'saveTemplateRoom'])->name('template.room.save');
    Route::post('/template/rooms/bulk', [SuperAdminController::class, 'saveAllTemplateRooms'])->name('template.rooms.bulk');
    Route::delete('/template/room/{roomIndex}', [SuperAdminController::class, 'deleteTemplateRoom'])->name('template.room.delete');
    Route::post('/template/background', [SuperAdminController::class, 'uploadTemplateBackground'])->name('template.background');
    Route::post('/template/sync', [SuperAdminController::class, 'syncTemplateToOrganizations'])->name('template.sync');

    // Furniture & Assets Catalog Management
    Route::get('/furniture', [SuperAdminController::class, 'furniture'])->name('furniture');
    Route::post('/furniture/category', [SuperAdminController::class, 'storeFurnitureCategory'])->name('furniture.category.store');
    Route::put('/furniture/category/{category}', [SuperAdminController::class, 'updateFurnitureCategory'])->name('furniture.category.update');
    Route::delete('/furniture/category/{category}', [SuperAdminController::class, 'deleteFurnitureCategory'])->name('furniture.category.delete');

    Route::post('/furniture/item', [SuperAdminController::class, 'storeFurnitureItem'])->name('furniture.item.store');
    Route::put('/furniture/item/{item}', [SuperAdminController::class, 'updateFurnitureItem'])->name('furniture.item.update');
    Route::delete('/furniture/item/{item}', [SuperAdminController::class, 'deleteFurnitureItem'])->name('furniture.item.delete');

    // CMS & Website Management Routes
    Route::get('/cms/pages', [SuperAdminController::class, 'cmsPages'])->name('cms.pages');
    Route::get('/cms/pages/{page}', [SuperAdminController::class, 'editCmsPage'])->name('cms.pages.edit');
    Route::put('/cms/sections/{section}', [SuperAdminController::class, 'updateCmsSection'])->name('cms.sections.update');
    Route::post('/cms/sections/{section}/toggle', [SuperAdminController::class, 'toggleCmsSection'])->name('cms.sections.toggle');

    Route::get('/cms/assets', [SuperAdminController::class, 'cmsAssets'])->name('cms.assets');
    Route::post('/cms/assets/upload', [SuperAdminController::class, 'uploadCmsAsset'])->name('cms.assets.upload');
    Route::delete('/cms/assets/{asset}', [SuperAdminController::class, 'deleteCmsAsset'])->name('cms.assets.delete');

    Route::get('/cms/theme', [SuperAdminController::class, 'cmsTheme'])->name('cms.theme');
    Route::post('/cms/theme', [SuperAdminController::class, 'updateCmsTheme'])->name('cms.theme.update');

    Route::get('/features', [SuperAdminController::class, 'featureFlags'])->name('features');
    Route::post('/features/{flag}/toggle', [SuperAdminController::class, 'toggleFeature'])->name('features.toggle');

    Route::get('/health', [SuperAdminController::class, 'systemHealth'])->name('health');
});

// Content Security Policy (CSP) Violation Reporting Endpoint
Route::post('/csp-violation-report', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::warning('CSP Violation Report', [
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'payload' => $request->json()->all() ?: $request->all(),
    ]);

    return response()->noContent();
})->name('csp.violation.report');

