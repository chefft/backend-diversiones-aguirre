<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login/admin', [SessionController::class, 'showAdminLogin'])->name('login.admin');
    Route::post('/login/admin', [SessionController::class, 'loginAdmin'])->name('login.admin.attempt')->middleware('throttle:6,1');

    Route::get('/login/cliente', [SessionController::class, 'showClientLogin'])->name('login.client');
    Route::post('/login/cliente', [SessionController::class, 'loginClient'])->name('login.client.attempt')->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessionController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('client.dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
});

Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/cliente/dashboard', ClientDashboardController::class)->name('client.dashboard');
});
