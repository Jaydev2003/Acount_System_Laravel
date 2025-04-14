<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;



    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboardpage');
    Route::get('/account-temp', [DashboardController::class, 'temp'])->name('temp');

// Route::get('/dashboard',[DashboardController::class,'dashboard'])->name('dashboardpage');
Route::get('/admin',[DashboardController::class,'admin'])->name('admin');
Route::get('/balance-sheet',[DashboardController::class,'balanceSheet'])->name('balancesheet');
Route::get('/role',[DashboardController::class,'role'])->name('role');
Route::get('/user-list',[DashboardController::class,'user'])->name('user-list');
Route::get('/',[DashboardController::class,'login']);
Route::get('/verify-otp',[DashboardController::class,'verifyOtp']);
Route::get('/forgot-password',[DashboardController::class,'fogotpassword']);
Route::get('/report',[DashboardController::class,'report']);

Route::get('/download-report', [DashboardController::class, 'downloadReport']);

// Route::get('/dashboard', function () {
//     return view('Layout.index2');
// });

Route::get('/reset-password-form', [DashboardController::class, 'showResetPasswordForm'])->name('password.reset');





