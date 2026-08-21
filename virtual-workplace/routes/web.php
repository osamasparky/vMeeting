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
    Route::delete('/editor/rooms/{room}', [WebAuthController::class, 'deleteEditorRoom'])->name('editor.rooms.delete');
    Route::get('/projects/{project}', [WebAuthController::class, 'projectHub'])->name('projects.hub');

    // Departments & Teams Management
    Route::post('/departments', [WebAuthController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{department}', [WebAuthController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [WebAuthController::class, 'deleteDepartment'])->name('departments.delete');

    Route::post('/teams', [WebAuthController::class, 'storeTeam'])->name('teams.store');
    Route::delete('/teams/{team}', [WebAuthController::class, 'deleteTeam'])->name('teams.delete');

    Route::post('/members/{member}/assign', [WebAuthController::class, 'assignMemberDepartment'])->name('members.assign_department');

    // Bulk Clear Routes
    Route::post('/organization/guest-invitations/clear', [WebAuthController::class, 'clearGuestInvitations'])->name('guest_invitations.clear');
    Route::post('/organization/audit-logs/clear', [WebAuthController::class, 'clearAuditLogs'])->name('audit_logs.clear');

    // Scheduled Meetings & SMTP Test Routes
    Route::post('/meetings/schedule', [WebAuthController::class, 'storeScheduledMeeting'])->name('meetings.schedule');
    Route::post('/meetings/{meeting}/cancel', [WebAuthController::class, 'cancelMeeting'])->name('meetings.cancel');
    Route::post('/organization/smtp/test', [WebAuthController::class, 'testSmtpConnection'])->name('organization.smtp.test');

    // Recordings Gallery Routes (Web Session)
    Route::get('/organizations/{organization}/recordings', [\App\Domains\Collaboration\Controllers\RecordingController::class, 'index'])->name('recordings.index');
    Route::post('/organizations/{organization}/recordings', [\App\Domains\Collaboration\Controllers\RecordingController::class, 'store'])->name('recordings.store');
    Route::delete('/organizations/{organization}/recordings/{recording}', [\App\Domains\Collaboration\Controllers\RecordingController::class, 'destroy'])->name('recordings.destroy');
});

// Guest Access Routes (Public / Unauthenticated)
Route::get('/guest/join/{token}', [WebAuthController::class, 'guestJoin'])->name('guest.join');
Route::post('/guest/join/{token}', [WebAuthController::class, 'guestEnter'])->middleware('throttle:10,1')->name('guest.enter');

// Super Admin Portal Routes
Route::prefix('superadmin')->middleware(['auth', 'superadmin'])->name('superadmin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/companies', [\App\Http\Controllers\SuperAdminController::class, 'companies'])->name('companies');
    Route::post('/companies/{organization}/plan', [\App\Http\Controllers\SuperAdminController::class, 'updateCompanyPlan'])->name('companies.plan');
    Route::post('/companies/{organization}/toggle-status', [\App\Http\Controllers\SuperAdminController::class, 'toggleCompanyStatus'])->name('companies.toggle');

    Route::get('/plans', [\App\Http\Controllers\SuperAdminController::class, 'plans'])->name('plans');
    Route::post('/plans', [\App\Http\Controllers\SuperAdminController::class, 'storePlan'])->name('plans.store');
    Route::put('/plans/{plan}', [\App\Http\Controllers\SuperAdminController::class, 'updatePlan'])->name('plans.update');
    Route::delete('/plans/{plan}', [\App\Http\Controllers\SuperAdminController::class, 'deletePlan'])->name('plans.delete');

    Route::get('/matrix', [\App\Http\Controllers\SuperAdminController::class, 'matrix'])->name('matrix');
    Route::post('/matrix/sync', [\App\Http\Controllers\SuperAdminController::class, 'syncMatrix'])->name('matrix.sync');

    Route::get('/settings', [\App\Http\Controllers\SuperAdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\SuperAdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/default-blueprint', [\App\Http\Controllers\SuperAdminController::class, 'uploadDefaultBlueprint'])->name('settings.blueprint');

    // Furniture & Assets Catalog Management
    Route::get('/furniture', [\App\Http\Controllers\SuperAdminController::class, 'furniture'])->name('furniture');
    Route::post('/furniture/category', [\App\Http\Controllers\SuperAdminController::class, 'storeFurnitureCategory'])->name('furniture.category.store');
    Route::put('/furniture/category/{category}', [\App\Http\Controllers\SuperAdminController::class, 'updateFurnitureCategory'])->name('furniture.category.update');
    Route::delete('/furniture/category/{category}', [\App\Http\Controllers\SuperAdminController::class, 'deleteFurnitureCategory'])->name('furniture.category.delete');

    Route::post('/furniture/item', [\App\Http\Controllers\SuperAdminController::class, 'storeFurnitureItem'])->name('furniture.item.store');
    Route::put('/furniture/item/{item}', [\App\Http\Controllers\SuperAdminController::class, 'updateFurnitureItem'])->name('furniture.item.update');
    Route::delete('/furniture/item/{item}', [\App\Http\Controllers\SuperAdminController::class, 'deleteFurnitureItem'])->name('furniture.item.delete');
});


