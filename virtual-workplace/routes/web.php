<?php

use App\Domains\Chat\Controllers\ChatController;
use App\Domains\Collaboration\Controllers\RecordingController;
use App\Domains\Meetings\Controllers\MeetingController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SuperAdmin\CompanyController;
use App\Http\Controllers\SuperAdmin\OfficeCatalogController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\SuperAdmin\PlatformSettingsController;
use App\Http\Controllers\SuperAdmin\WebsiteContentController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\GuestAccessController;
use App\Domains\Assistant\Controllers\AssistantController;
use App\Domains\Workspace\Controllers\QuizController;
use App\Http\Controllers\Web\HrController;
use App\Http\Controllers\Web\NotificationPreferencesController;
use App\Http\Controllers\Web\OfficeController;
use App\Http\Controllers\Web\OrganizationSettingsController;
use App\Http\Controllers\Web\ProjectHubController;
use App\Http\Controllers\Web\PublicIntakeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'home'])->name('landing.home');

// Language Switcher
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
        cookie()->queue('locale', $locale, 525600);
    }
    $referer = request()->header('referer');
    if ($referer && ! str_contains($referer, '/lang/')) {
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

Route::middleware(['auth', 'project.visible'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/pulse', [DashboardController::class, 'pulse'])->name('dashboard.pulse');
    Route::post('/organization/upgrade-plan', [OrganizationSettingsController::class, 'upgradePlan'])->name('organization.upgrade_plan');
    Route::get('/billing/payment/{plan}', [OrganizationSettingsController::class, 'showPaymentPage'])->name('subscription.payment');
    Route::post('/billing/payment/{plan}/submit', [OrganizationSettingsController::class, 'submitBankTransferPayment'])->name('subscription.payment.submit');
    Route::post('/billing/seats/update', [OrganizationSettingsController::class, 'updateSubscriptionSeats'])->name('subscription.seats.update');
    Route::post('/billing/payment-requests/{subscriptionRequest}/cancel', [OrganizationSettingsController::class, 'cancelSubscriptionRequest'])->name('subscription.payment.cancel');
    Route::post('/organization/settings', [OrganizationSettingsController::class, 'updateOrganizationSettings'])->name('organization.settings.update');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/notifications', [NotificationPreferencesController::class, 'update'])->name('profile.notifications.update');
    Route::get('/office', [OfficeController::class, 'office'])->name('office');
    Route::get('/editor', [OfficeController::class, 'editor'])->name('editor');
    Route::post('/editor/maps/{map}/background', [OfficeController::class, 'uploadMapBackground'])->middleware('throttle:uploads')->name('editor.maps.background');
    Route::delete('/editor/maps/{map}/background', [OfficeController::class, 'deleteMapBackground'])->name('editor.maps.background.delete');
    Route::post('/editor/maps/{map}/save', [OfficeController::class, 'saveEditorMap'])->name('editor.maps.save');
    Route::post('/editor/maps/{map}/clear', [OfficeController::class, 'clearEditorMap'])->name('editor.maps.clear');
    Route::post('/editor/maps/{map}/publish', [OfficeController::class, 'publishEditorMap'])->name('editor.maps.publish');
    Route::post('/editor/upload-object-image', [OfficeController::class, 'uploadObjectImage'])->middleware('throttle:uploads')->name('editor.upload_object_image');
    Route::post('/editor/rooms', [OfficeController::class, 'saveEditorRoom'])->name('editor.rooms.store');
    Route::match(['post', 'patch', 'put'], '/editor/rooms/{room}', [OfficeController::class, 'updateEditorRoom'])->name('editor.rooms.update');
    Route::delete('/editor/rooms/{room}', [OfficeController::class, 'deleteEditorRoom'])->name('editor.rooms.destroy');
    Route::get('/projects/{project}', [ProjectHubController::class, 'show'])->name('projects.hub');

    // Multi-Office & Branches Management
    Route::post('/offices', [OfficeController::class, 'storeOffice'])->name('offices.store');
    Route::put('/offices/{floor}', [OfficeController::class, 'updateOffice'])->name('offices.update');
    Route::delete('/offices/{floor}', [OfficeController::class, 'deleteOffice'])->name('offices.delete');
    Route::post('/organization/ai-map/generate', [OfficeController::class, 'generateAiOffice'])->middleware('throttle:expensive')->name('organization.ai_map.generate');

    // Personnel affairs: official holidays
    // Office assistant: personal briefing + typed requests (read-only, own data).
    Route::get('/assistant/briefing', [AssistantController::class, 'briefing'])->middleware('throttle:60,1')->name('assistant.briefing');
    Route::post('/assistant/ask', [AssistantController::class, 'ask'])->middleware('throttle:30,1')->name('assistant.ask');

    // Office quiz machine: list / start (sealed ticket) for everyone; the owner writes the company's quizzes.
    Route::get('/games/quizzes', [QuizController::class, 'index'])->name('games.quizzes.index');
    Route::post('/games/quizzes', [QuizController::class, 'store'])->name('games.quizzes.store');
    Route::post('/games/quizzes/{id}/ticket', [QuizController::class, 'ticket'])->middleware('throttle:30,1')->name('games.quizzes.ticket');
    Route::put('/games/quizzes/{quiz}', [QuizController::class, 'update'])->name('games.quizzes.update');
    Route::delete('/games/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('games.quizzes.destroy');

    Route::get('/hr/panel/{panel}', [HrController::class, 'panel'])->name('hr.panel');
    Route::post('/hr/holidays', [HrController::class, 'storeHoliday'])->name('hr.holidays.store');
    Route::delete('/hr/holidays/{holiday}', [HrController::class, 'deleteHoliday'])->name('hr.holidays.delete');
    // Personnel affairs: leave requests (the company owner decides)
    Route::post('/hr/leaves', [HrController::class, 'storeLeave'])->name('hr.leaves.store');
    Route::post('/hr/leaves/{leave}/approve', [HrController::class, 'approveLeave'])->name('hr.leaves.approve');
    Route::post('/hr/leaves/{leave}/reject', [HrController::class, 'rejectLeave'])->name('hr.leaves.reject');
    Route::post('/hr/leaves/{leave}/cancel', [HrController::class, 'cancelLeave'])->name('hr.leaves.cancel');
    // Personnel affairs: monthly attendance report
    Route::get('/hr/attendance/export', [HrController::class, 'exportAttendance'])->name('hr.attendance.export');

    // Departments & Teams Management
    Route::post('/departments', [OrganizationSettingsController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{department}', [OrganizationSettingsController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [OrganizationSettingsController::class, 'deleteDepartment'])->name('departments.delete');
    Route::post('/teams', [OrganizationSettingsController::class, 'storeTeam'])->name('teams.store');
    Route::put('/teams/{team}', [OrganizationSettingsController::class, 'updateTeam'])->name('teams.update');
    Route::delete('/teams/{team}', [OrganizationSettingsController::class, 'deleteTeam'])->name('teams.delete');

    // Organization Attendance & Policies
    Route::post('/organization/smtp-test', [OrganizationSettingsController::class, 'testSmtpConnection'])->middleware('throttle:expensive')->name('organization.smtp.test');
    Route::post('/organization/ai-test', [OrganizationSettingsController::class, 'testOrgAiConnection'])->middleware('throttle:expensive')->name('organization.ai.test');

    // Projects & Files Storage
    Route::post('/projects/{project}/files', [ProjectHubController::class, 'storeFile'])->middleware('throttle:uploads')->name('projects.files.store');
    Route::delete('/projects/{project}/files/{file}', [ProjectHubController::class, 'destroyFile'])->name('projects.files.destroy');
    Route::post('/tasks/{task}/attachments', [ProjectHubController::class, 'uploadTaskAttachment'])->middleware('throttle:uploads')->name('tasks.attachments.store');
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
    Route::post('/chat/channels/{channel}/messages', [ChatController::class, 'webSendMessage'])->middleware('throttle:chat')->name('chat.messages.send');

    // Bulk Clear Routes
    Route::post('/organization/guest-invitations/clear', [GuestAccessController::class, 'clearGuestInvitations'])->name('guest_invitations.clear');
    Route::post('/organization/audit-logs/clear', [OrganizationSettingsController::class, 'clearAuditLogs'])->name('audit_logs.clear');

    // Scheduled Meetings & SMTP / AI Test Routes
    Route::get('/meetings/schedule', fn () => redirect('/dashboard#meetings'))->name('meetings.schedule.get');
    Route::post('/meetings/schedule', [DashboardController::class, 'storeScheduledMeeting'])->name('meetings.schedule');
    Route::post('/meetings/{meeting}/cancel', [DashboardController::class, 'cancelMeeting'])->name('meetings.cancel');
    Route::post('/organization/smtp/test', [OrganizationSettingsController::class, 'testSmtpConnection'])->middleware('throttle:expensive')->name('organization.smtp.test.custom');
    Route::post('/organization/ai/test', [OrganizationSettingsController::class, 'testOrgAiConnection'])->middleware('throttle:expensive')->name('organization.ai.test.custom');

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
    Route::post('/impersonate/leave', [CompanyController::class, 'leaveImpersonation'])->name('impersonate.leave');
    Route::get('/impersonate/leave', [CompanyController::class, 'leaveImpersonation'])->name('impersonate.leave.get');

    // In-App Notification System Routes
    Route::get('/api/notifications', [DashboardController::class, 'getUserNotifications'])->name('notifications.index');
    Route::post('/api/notifications/{id}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/api/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read_all');
    Route::delete('/api/notifications/clear', [DashboardController::class, 'clearAllNotifications'])->name('notifications.clear');
    Route::post('/api/notifications/wave', [OfficeController::class, 'sendDirectWave'])->middleware('throttle:notifications')->name('notifications.wave');
    Route::post('/api/notifications/knock', [OfficeController::class, 'sendDoorKnock'])->middleware('throttle:notifications')->name('notifications.knock');
});

// Hybrid Workplace & Media Plane Routes (Accessible by Authenticated Members and Invited Guests)
Route::post('/organizations/{organization}/rooms/{room}/livekit-token', [MeetingController::class, 'getLiveKitToken'])->middleware('throttle:public')->name('web.rooms.livekit_token');
Route::post('/organizations/{organization}/maps/{map}/open-area-token', [MeetingController::class, 'getOpenAreaToken'])->middleware('throttle:public')->name('web.maps.open_area_token');
Route::get('/organizations/{organization}/webrtc/diagnostics-config', [MeetingController::class, 'getDiagnosticsConfig'])->middleware('throttle:public')->name('web.webrtc.diagnostics');
Route::get('/organizations/{organization}/rooms/{room}/files', [OfficeController::class, 'listRoomFiles'])->middleware('throttle:public')->name('room_files.index');
Route::post('/organizations/{organization}/rooms/{room}/files', [OfficeController::class, 'uploadRoomFile'])->middleware('throttle:uploads')->name('room_files.store');
Route::delete('/organizations/{organization}/rooms/{room}/files/{file}', [OfficeController::class, 'deleteRoomFile'])->middleware('throttle:public')->name('room_files.destroy');
Route::post('/organizations/{organization}/chat/upload', [DashboardController::class, 'uploadChatAttachment'])->middleware('throttle:uploads')->name('chat.upload');
Route::post('/api/office/attendance/log', [AttendanceController::class, 'logRoomAttendance'])->name('office.attendance.log');
Route::get('/api/office/attendance/summary', [AttendanceController::class, 'getAttendanceSummary'])->name('office.attendance.summary');
Route::get('/api/office/team-presence-overview', [AttendanceController::class, 'getTeamPresenceOverview'])->name('office.team_presence.overview');
Route::get('/api/timesheets/daily-summary', [AttendanceController::class, 'getDailyTimesheetsReport'])->name('timesheets.daily_summary');
Route::get('/api/office/my-tasks', [AttendanceController::class, 'getOfficeTasksAndTimer'])->name('office.my_tasks');
Route::get('/api/members/{userId}/activity', [AttendanceController::class, 'memberActivity'])->name('members.activity');
Route::post('/api/office/task-timer/start', [AttendanceController::class, 'startOfficeTaskTimer'])->name('office.task_timer.start');
Route::post('/api/office/task-timer/stop', [AttendanceController::class, 'stopOfficeTaskTimer'])->name('office.task_timer.stop');
Route::post('/api/office/tasks/{taskId}/status', [AttendanceController::class, 'updateOfficeTaskStatus'])->name('office.tasks.status');

// Guest Access Routes (Public / Unauthenticated)
Route::get('/guest/join/{token}', [GuestAccessController::class, 'guestJoin'])->middleware('throttle:guest-token')->name('guest.join');
Route::post('/guest/join/{token}', [GuestAccessController::class, 'guestEnter'])->middleware('throttle:10,1')->name('guest.enter');

// Super Admin Portal Routes
Route::prefix('superadmin')->middleware(['auth', 'superadmin'])->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/companies', [CompanyController::class, 'companies'])->name('companies');
    Route::get('/companies/{organization}', [CompanyController::class, 'showCompany'])->name('companies.show');
    Route::post('/companies/{organization}/impersonate', [CompanyController::class, 'impersonateCompany'])->name('companies.impersonate');
    Route::put('/companies/{organization}', [CompanyController::class, 'updateCompanyDetails'])->name('companies.update');
    Route::delete('/companies/{organization}', [CompanyController::class, 'deleteCompany'])->name('companies.delete');
    Route::post('/companies/{organization}/plan', [CompanyController::class, 'updateCompanyPlan'])->name('companies.plan');
    Route::post('/companies/{organization}/toggle-status', [CompanyController::class, 'toggleCompanyStatus'])->name('companies.toggle');

    Route::get('/plans', [PlanController::class, 'plans'])->name('plans');
    Route::post('/plans', [PlanController::class, 'storePlan'])->name('plans.store');
    Route::put('/plans/{plan}', [PlanController::class, 'updatePlan'])->name('plans.update');
    Route::delete('/plans/{plan}', [PlanController::class, 'deletePlan'])->name('plans.delete');

    // Subscription & Bank Transfer Payment Requests
    Route::get('/subscriptions', [PlanController::class, 'subscriptionRequests'])->name('subscriptions');
    Route::post('/subscriptions/{subscriptionRequest}/approve', [PlanController::class, 'approveSubscriptionRequest'])->name('subscriptions.approve');
    Route::post('/subscriptions/{subscriptionRequest}/reject', [PlanController::class, 'rejectSubscriptionRequest'])->name('subscriptions.reject');
    Route::get('/subscriptions/{subscriptionRequest}/receipt', [PlanController::class, 'viewSubscriptionReceipt'])->name('subscriptions.receipt');

    Route::get('/matrix', [SuperAdminController::class, 'matrix'])->name('matrix');
    Route::post('/matrix/sync', [SuperAdminController::class, 'syncMatrix'])->name('matrix.sync');

    Route::get('/settings', [PlatformSettingsController::class, 'settings'])->name('settings');
    Route::post('/settings', [PlatformSettingsController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/payment', [PlatformSettingsController::class, 'updatePaymentSettings'])->name('settings.payment');
    Route::post('/settings/ai', [PlatformSettingsController::class, 'updateAiSettings'])->name('settings.ai');
    Route::post('/settings/ai/test', [PlatformSettingsController::class, 'testAiConnection'])->name('settings.ai.test');
    Route::post('/settings/default-blueprint', [OfficeCatalogController::class, 'uploadDefaultBlueprint'])->name('settings.blueprint');

    // System Translations & Localization Manager
    Route::get('/translations', [PlatformSettingsController::class, 'translations'])->name('translations');
    Route::post('/translations', [PlatformSettingsController::class, 'updateTranslations'])->name('translations.update');
    Route::post('/translations/add', [PlatformSettingsController::class, 'addTranslationKey'])->name('translations.add');
    Route::post('/translations/delete', [PlatformSettingsController::class, 'deleteTranslationKey'])->name('translations.delete');

    // Default Office Template & Rooms Designer
    Route::get('/template', [OfficeCatalogController::class, 'defaultTemplate'])->name('template');
    Route::post('/template', [OfficeCatalogController::class, 'updateDefaultTemplate'])->name('template.update');
    Route::post('/template/room', [OfficeCatalogController::class, 'saveTemplateRoom'])->name('template.room.save');
    Route::post('/template/rooms/bulk', [OfficeCatalogController::class, 'saveAllTemplateRooms'])->name('template.rooms.bulk');
    Route::delete('/template/room/{roomIndex}', [OfficeCatalogController::class, 'deleteTemplateRoom'])->name('template.room.delete');
    Route::post('/template/background', [OfficeCatalogController::class, 'uploadTemplateBackground'])->name('template.background');
    Route::post('/template/sync', [OfficeCatalogController::class, 'syncTemplateToOrganizations'])->name('template.sync');

    // Furniture & Assets Catalog Management
    Route::get('/furniture', [OfficeCatalogController::class, 'furniture'])->name('furniture');
    Route::post('/furniture/category', [OfficeCatalogController::class, 'storeFurnitureCategory'])->name('furniture.category.store');
    Route::put('/furniture/category/{category}', [OfficeCatalogController::class, 'updateFurnitureCategory'])->name('furniture.category.update');
    Route::delete('/furniture/category/{category}', [OfficeCatalogController::class, 'deleteFurnitureCategory'])->name('furniture.category.delete');

    Route::post('/furniture/item', [OfficeCatalogController::class, 'storeFurnitureItem'])->name('furniture.item.store');
    Route::put('/furniture/item/{item}', [OfficeCatalogController::class, 'updateFurnitureItem'])->name('furniture.item.update');
    Route::delete('/furniture/item/{item}', [OfficeCatalogController::class, 'deleteFurnitureItem'])->name('furniture.item.delete');

    // CMS & Website Management Routes
    Route::get('/cms/pages', [WebsiteContentController::class, 'cmsPages'])->name('cms.pages');
    Route::get('/cms/pages/{page}', [WebsiteContentController::class, 'editCmsPage'])->name('cms.pages.edit');
    Route::put('/cms/pages/{page}', [WebsiteContentController::class, 'updateCmsPage'])->name('cms.pages.update');
    Route::put('/cms/sections/{section}', [WebsiteContentController::class, 'updateCmsSection'])->name('cms.sections.update');
    Route::post('/cms/sections/{section}/toggle', [WebsiteContentController::class, 'toggleCmsSection'])->name('cms.sections.toggle');

    Route::get('/cms/assets', [WebsiteContentController::class, 'cmsAssets'])->name('cms.assets');
    Route::post('/cms/assets/upload', [WebsiteContentController::class, 'uploadCmsAsset'])->name('cms.assets.upload');
    Route::delete('/cms/assets/{asset}', [WebsiteContentController::class, 'deleteCmsAsset'])->name('cms.assets.delete');

    Route::get('/features', [SuperAdminController::class, 'featureFlags'])->name('features');
    Route::post('/features/{flag}/toggle', [SuperAdminController::class, 'toggleFeature'])->name('features.toggle');

    Route::get('/health', [SuperAdminController::class, 'systemHealth'])->name('health');
});

// Content Security Policy (CSP) Violation Reporting Endpoint
Route::post('/csp-violation-report', function (Request $request) {
    Log::warning('CSP Violation Report', [
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'payload' => $request->json()->all() ?: $request->all(),
    ]);

    return response()->noContent();
})->name('csp.violation.report');

// Project intake: public request form behind a project's shareable link (no account needed)
Route::get('/intake/{token}', [PublicIntakeController::class, 'show'])->middleware('throttle:public')->name('intake.show');
Route::post('/intake/{token}', [PublicIntakeController::class, 'store'])->middleware('throttle:guest-token')->name('intake.store');
