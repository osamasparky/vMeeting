<?php

use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
    Route::get('/office', [WebAuthController::class, 'office'])->name('office');
    Route::get('/editor', [WebAuthController::class, 'editor'])->name('editor');
});

// Guest Access Routes (Public / Unauthenticated)
Route::get('/guest/join/{token}', [WebAuthController::class, 'guestJoin'])->name('guest.join');
Route::post('/guest/join/{token}', [WebAuthController::class, 'guestEnter'])->name('guest.enter');


