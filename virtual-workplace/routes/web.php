<?php

use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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

// Authentication & Dashboard Routes (Web UI)
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->middleware('throttle:6,1')->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/organization/upgrade-plan', [WebAuthController::class, 'upgradePlan'])->name('organization.upgrade_plan');
    Route::get('/billing/payment/{plan}', [WebAuthController::class, 'showPaymentPage'])->name('subscription.payment');
    Route::post('/billing/payment/{plan}/submit', [WebAuthController::class, 'submitBankTransferPayment'])->name('subscription.payment.submit');
    Route::post('/billing/payment-requests/{subscriptionRequest}/cancel', [WebAuthController::class, 'cancelSubscriptionRequest'])->name('subscription.payment.cancel');
    Route::post('/organization/settings', [WebAuthController::class, 'updateOrganizationSettings'])->name('organization.settings.update');
    Route::post('/profile', [WebAuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [WebAuthController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/office', [WebAuthController::class, 'office'])->name('office');
    Route::get('/editor', [WebAuthController::class, 'editor'])->name('editor');
    Route::post('/editor/maps/{map}/background', [WebAuthController::class, 'uploadMapBackground'])->name('editor.maps.background');
    Route::delete('/editor/maps/{map}/background', [WebAuthController::class, 'deleteMapBackground'])->name('editor.maps.background.delete');
    Route::post('/editor/maps/{map}/save', [WebAuthController::class, 'saveEditorMap'])->name('editor.maps.save');
    Route::post('/editor/maps/{map}/clear', [WebAuthController::class, 'clearEditorMap'])->name('editor.maps.clear');
    Route::post('/editor/maps/{map}/publish', [WebAuthController::class, 'publishEditorMap'])->name('editor.maps.publish');
    Route::post('/editor/rooms', [WebAuthController::class, 'saveEditorRoom'])->name('editor.rooms.store');
    Route::patch('/editor/rooms/{room}', [WebAuthController::class, 'updateEditorRoom'])->name('editor.rooms.update');
    Route::get('/projects/{project}', [WebAuthController::class, 'projectHub'])->name('projects.hub');

    // Multi-Office & Branches Management
    Route::post('/offices', [WebAuthController::class, 'storeOffice'])->name('offices.store');
    Route::put('/offices/{floor}', [WebAuthController::class, 'updateOffice'])->name('offices.update');
    Route::delete('/offices/{floor}', [WebAuthController::class, 'deleteOffice'])->name('offices.delete');
    Route::post('/organization/ai-map/generate', [WebAuthController::class, 'generateAiOffice'])->name('organization.ai_map.generate');

    // Departments & Teams Management
    Route::post('/departments', [WebAuthController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{department}', [WebAuthController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [WebAuthController::class, 'deleteDepartment'])->name('departments.delete');

    Route::post('/teams', [WebAuthController::class, 'storeTeam'])->name('teams.store');
    Route::delete('/teams/{team}', [WebAuthController::class, 'deleteTeam'])->name('teams.delete');

    Route::get('/organization/members/{member}/details', [WebAuthController::class, 'getMemberProfileDetails'])->name('organization.members.details');
    Route::post('/organization/members/create', [WebAuthController::class, 'storeMember'])->name('organization.members.store');
    Route::post('/members/{member}/assign', [WebAuthController::class, 'assignMemberDepartment'])->name('members.assign_department');
    Route::put('/organization/members/{member}', [WebAuthController::class, 'updateOrganizationMember'])->name('organization.members.update');
    Route::post('/organization/members/{member}/password', [WebAuthController::class, 'updateMemberPassword'])->name('organization.members.password');
    Route::delete('/organization/members/{member}', [WebAuthController::class, 'deleteOrganizationMember'])->name('organization.members.delete');

    // Web Chat & Direct Messaging Routes
    Route::get('/chat/conversations', [\App\Domains\Chat\Controllers\ChatController::class, 'webConversations'])->name('chat.conversations');
    Route::get('/chat/dm/{targetUser}', [\App\Domains\Chat\Controllers\ChatController::class, 'webGetOrCreateDm'])->name('chat.dm');
    Route::get('/chat/channels/{channel}/messages', [\App\Domains\Chat\Controllers\ChatController::class, 'webListMessages'])->name('chat.messages.list');
    Route::post('/chat/channels/{channel}/messages', [\App\Domains\Chat\Controllers\ChatController::class, 'webSendMessage'])->name('chat.messages.send');

    // Bulk Clear Routes
    Route::post('/organization/guest-invitations/clear', [WebAuthController::class, 'clearGuestInvitations'])->name('guest_invitations.clear');
    Route::post('/organization/audit-logs/clear', [WebAuthController::class, 'clearAuditLogs'])->name('audit_logs.clear');

    // Scheduled Meetings & SMTP / AI Test Routes
    Route::get('/meetings/schedule', fn() => redirect('/dashboard#meetings'))->name('meetings.schedule.get');
    Route::post('/meetings/schedule', [WebAuthController::class, 'storeScheduledMeeting'])->name('meetings.schedule');
    Route::post('/meetings/{meeting}/cancel', [WebAuthController::class, 'cancelMeeting'])->name('meetings.cancel');
    Route::post('/organization/smtp/test', [WebAuthController::class, 'testSmtpConnection'])->name('organization.smtp.test');
    Route::post('/organization/ai/test', [WebAuthController::class, 'testOrgAiConnection'])->name('organization.ai.test');

    // Organization Member Impersonation
    Route::post('/organization/members/{member}/impersonate', [WebAuthController::class, 'impersonateMember'])->name('organization.members.impersonate');
    Route::post('/organization/impersonate/leave', [WebAuthController::class, 'leaveMemberImpersonation'])->name('organization.members.impersonate.leave');
    Route::get('/organization/impersonate/leave', [WebAuthController::class, 'leaveMemberImpersonation'])->name('organization.members.impersonate.leave.get');

    // Project Files & Task Attachments
    Route::post('/projects/{project}/files', [WebAuthController::class, 'uploadProjectFile'])->name('projects.files.store');
    Route::delete('/projects/{project}/files/{file}', [WebAuthController::class, 'deleteProjectFile'])->name('projects.files.destroy');
    Route::post('/tasks/{task}/attachments', [WebAuthController::class, 'uploadTaskAttachment'])->name('tasks.attachments.store');
    Route::delete('/tasks/{task}/attachments/{attachment}', [WebAuthController::class, 'deleteTaskAttachment'])->name('tasks.attachments.destroy');

    // Task Comments & Mentions
    Route::get('/tasks/{task}/comments', [WebAuthController::class, 'getTaskComments'])->name('tasks.comments.index');
    Route::post('/tasks/{task}/comments', [WebAuthController::class, 'storeTaskComment'])->name('tasks.comments.store');

    // Task PM Review & Approvals
    Route::post('/tasks/{task}/approve', [WebAuthController::class, 'approveTask'])->name('tasks.approve');
    Route::post('/tasks/{task}/reject', [WebAuthController::class, 'rejectTask'])->name('tasks.reject');

    // Recordings Gallery Routes (Web Session)
    Route::get('/organizations/{organization}/recordings', [\App\Domains\Collaboration\Controllers\RecordingController::class, 'index'])->name('recordings.index');
    Route::post('/organizations/{organization}/recordings', [\App\Domains\Collaboration\Controllers\RecordingController::class, 'store'])->name('recordings.store');
    Route::get('/organizations/{organization}/recordings/{recording}/download', [\App\Domains\Collaboration\Controllers\RecordingController::class, 'download'])->name('recordings.download');
    Route::delete('/organizations/{organization}/recordings/{recording}', [\App\Domains\Collaboration\Controllers\RecordingController::class, 'destroy'])->name('recordings.destroy');

    // Impersonation Exit Routes (SuperAdmin)
    Route::post('/impersonate/leave', [\App\Http\Controllers\SuperAdminController::class, 'leaveImpersonation'])->name('impersonate.leave');
    Route::get('/impersonate/leave', [\App\Http\Controllers\SuperAdminController::class, 'leaveImpersonation'])->name('impersonate.leave.get');

    // In-App Notification System Routes
    Route::get('/api/notifications', [WebAuthController::class, 'getUserNotifications'])->name('notifications.index');
    Route::post('/api/notifications/{id}/read', [WebAuthController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/api/notifications/read-all', [WebAuthController::class, 'markAllNotificationsRead'])->name('notifications.read_all');
    Route::delete('/api/notifications/clear', [WebAuthController::class, 'clearAllNotifications'])->name('notifications.clear');
    Route::post('/api/notifications/wave', [WebAuthController::class, 'sendDirectWave'])->name('notifications.wave');
    Route::post('/api/notifications/knock', [WebAuthController::class, 'sendDoorKnock'])->name('notifications.knock');
});

// Hybrid Workplace & Media Plane Routes (Accessible by Authenticated Members and Invited Guests)
Route::post('/organizations/{organization}/rooms/{room}/livekit-token', [\App\Domains\Meetings\Controllers\MeetingController::class, 'getLiveKitToken'])->name('web.rooms.livekit_token');
Route::get('/organizations/{organization}/webrtc/diagnostics-config', [\App\Domains\Meetings\Controllers\MeetingController::class, 'getDiagnosticsConfig'])->name('web.webrtc.diagnostics');
Route::get('/organizations/{organization}/rooms/{room}/files', [WebAuthController::class, 'listRoomFiles'])->name('room_files.index');
Route::post('/organizations/{organization}/rooms/{room}/files', [WebAuthController::class, 'uploadRoomFile'])->name('room_files.store');
Route::delete('/organizations/{organization}/rooms/{room}/files/{file}', [WebAuthController::class, 'deleteRoomFile'])->name('room_files.destroy');
Route::post('/organizations/{organization}/chat/upload', [WebAuthController::class, 'uploadChatAttachment'])->name('chat.upload');
Route::get('/api/members/{userId}/activity', [WebAuthController::class, 'memberActivity'])->name('members.activity');
Route::post('/api/office/attendance/log', [WebAuthController::class, 'logRoomAttendance'])->name('office.attendance.log');
Route::get('/api/office/attendance/summary', [WebAuthController::class, 'getAttendanceSummary'])->name('office.attendance.summary');

// Guest Access Routes (Public / Unauthenticated)
Route::get('/guest/join/{token}', [WebAuthController::class, 'guestJoin'])->name('guest.join');
Route::post('/guest/join/{token}', [WebAuthController::class, 'guestEnter'])->middleware('throttle:10,1')->name('guest.enter');

// Super Admin Portal Routes
Route::prefix('superadmin')->middleware(['auth', 'superadmin'])->name('superadmin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/companies', [\App\Http\Controllers\SuperAdminController::class, 'companies'])->name('companies');
    Route::get('/companies/{organization}', [\App\Http\Controllers\SuperAdminController::class, 'showCompany'])->name('companies.show');
    Route::post('/companies/{organization}/impersonate', [\App\Http\Controllers\SuperAdminController::class, 'impersonateCompany'])->name('companies.impersonate');
    Route::put('/companies/{organization}', [\App\Http\Controllers\SuperAdminController::class, 'updateCompanyDetails'])->name('companies.update');
    Route::delete('/companies/{organization}', [\App\Http\Controllers\SuperAdminController::class, 'deleteCompany'])->name('companies.delete');
    Route::post('/companies/{organization}/plan', [\App\Http\Controllers\SuperAdminController::class, 'updateCompanyPlan'])->name('companies.plan');
    Route::post('/companies/{organization}/toggle-status', [\App\Http\Controllers\SuperAdminController::class, 'toggleCompanyStatus'])->name('companies.toggle');

    Route::get('/plans', [\App\Http\Controllers\SuperAdminController::class, 'plans'])->name('plans');
    Route::post('/plans', [\App\Http\Controllers\SuperAdminController::class, 'storePlan'])->name('plans.store');
    Route::put('/plans/{plan}', [\App\Http\Controllers\SuperAdminController::class, 'updatePlan'])->name('plans.update');
    Route::delete('/plans/{plan}', [\App\Http\Controllers\SuperAdminController::class, 'deletePlan'])->name('plans.delete');

    // Subscription & Bank Transfer Payment Requests
    Route::get('/subscriptions', [\App\Http\Controllers\SuperAdminController::class, 'subscriptionRequests'])->name('subscriptions');
    Route::post('/subscriptions/{subscriptionRequest}/approve', [\App\Http\Controllers\SuperAdminController::class, 'approveSubscriptionRequest'])->name('subscriptions.approve');
    Route::post('/subscriptions/{subscriptionRequest}/reject', [\App\Http\Controllers\SuperAdminController::class, 'rejectSubscriptionRequest'])->name('subscriptions.reject');
    Route::get('/subscriptions/{subscriptionRequest}/receipt', [\App\Http\Controllers\SuperAdminController::class, 'viewSubscriptionReceipt'])->name('subscriptions.receipt');

    Route::get('/matrix', [\App\Http\Controllers\SuperAdminController::class, 'matrix'])->name('matrix');
    Route::post('/matrix/sync', [\App\Http\Controllers\SuperAdminController::class, 'syncMatrix'])->name('matrix.sync');

    Route::get('/settings', [\App\Http\Controllers\SuperAdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\SuperAdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/payment', [\App\Http\Controllers\SuperAdminController::class, 'updatePaymentSettings'])->name('settings.payment');
    Route::post('/settings/ai', [\App\Http\Controllers\SuperAdminController::class, 'updateAiSettings'])->name('settings.ai');
    Route::post('/settings/ai/test', [\App\Http\Controllers\SuperAdminController::class, 'testAiConnection'])->name('settings.ai.test');
    Route::post('/settings/default-blueprint', [\App\Http\Controllers\SuperAdminController::class, 'uploadDefaultBlueprint'])->name('settings.blueprint');

    // System Translations & Localization Manager
    Route::get('/translations', [\App\Http\Controllers\SuperAdminController::class, 'translations'])->name('translations');
    Route::post('/translations', [\App\Http\Controllers\SuperAdminController::class, 'updateTranslations'])->name('translations.update');
    Route::post('/translations/add', [\App\Http\Controllers\SuperAdminController::class, 'addTranslationKey'])->name('translations.add');
    Route::post('/translations/delete', [\App\Http\Controllers\SuperAdminController::class, 'deleteTranslationKey'])->name('translations.delete');

    // Default Office Template & Rooms Designer
    Route::get('/template', [\App\Http\Controllers\SuperAdminController::class, 'defaultTemplate'])->name('template');
    Route::post('/template', [\App\Http\Controllers\SuperAdminController::class, 'updateDefaultTemplate'])->name('template.update');
    Route::post('/template/room', [\App\Http\Controllers\SuperAdminController::class, 'saveTemplateRoom'])->name('template.room.save');
    Route::post('/template/rooms/bulk', [\App\Http\Controllers\SuperAdminController::class, 'saveAllTemplateRooms'])->name('template.rooms.bulk');
    Route::delete('/template/room/{roomIndex}', [\App\Http\Controllers\SuperAdminController::class, 'deleteTemplateRoom'])->name('template.room.delete');
    Route::post('/template/background', [\App\Http\Controllers\SuperAdminController::class, 'uploadTemplateBackground'])->name('template.background');
    Route::post('/template/sync', [\App\Http\Controllers\SuperAdminController::class, 'syncTemplateToOrganizations'])->name('template.sync');

    // Furniture & Assets Catalog Management
    Route::get('/furniture', [\App\Http\Controllers\SuperAdminController::class, 'furniture'])->name('furniture');
    Route::post('/furniture/category', [\App\Http\Controllers\SuperAdminController::class, 'storeFurnitureCategory'])->name('furniture.category.store');
    Route::put('/furniture/category/{category}', [\App\Http\Controllers\SuperAdminController::class, 'updateFurnitureCategory'])->name('furniture.category.update');
    Route::delete('/furniture/category/{category}', [\App\Http\Controllers\SuperAdminController::class, 'deleteFurnitureCategory'])->name('furniture.category.delete');

    Route::post('/furniture/item', [\App\Http\Controllers\SuperAdminController::class, 'storeFurnitureItem'])->name('furniture.item.store');
    Route::put('/furniture/item/{item}', [\App\Http\Controllers\SuperAdminController::class, 'updateFurnitureItem'])->name('furniture.item.update');
    Route::delete('/furniture/item/{item}', [\App\Http\Controllers\SuperAdminController::class, 'deleteFurnitureItem'])->name('furniture.item.delete');
});


