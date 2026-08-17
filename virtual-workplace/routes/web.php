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
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/organization/upgrade-plan', [WebAuthController::class, 'upgradePlan'])->name('organization.upgrade_plan');
    Route::get('/office', [WebAuthController::class, 'office'])->name('office');
    Route::get('/editor', [WebAuthController::class, 'editor'])->name('editor');

    // Departments & Teams Management
    Route::post('/departments', [WebAuthController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{department}', [WebAuthController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [WebAuthController::class, 'deleteDepartment'])->name('departments.delete');

    Route::post('/teams', [WebAuthController::class, 'storeTeam'])->name('teams.store');
    Route::delete('/teams/{team}', [WebAuthController::class, 'deleteTeam'])->name('teams.delete');

    Route::post('/members/{member}/assign', [WebAuthController::class, 'assignMemberDepartment'])->name('members.assign_department');
});

// Guest Access Routes (Public / Unauthenticated)
Route::get('/guest/join/{token}', [WebAuthController::class, 'guestJoin'])->name('guest.join');
Route::post('/guest/join/{token}', [WebAuthController::class, 'guestEnter'])->name('guest.enter');

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
});


