<?php

use App\Http\Controllers\API\AccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoleController;
use App\Http\Middleware\RefreshTokenMiddleware; 

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);




    Route::post('/user/create', [UserController::class, 'create']);
    Route::put('/user/update', [UserController::class, 'update']);
    Route::delete('/user/delete', [UserController::class, 'delete']);
    Route::get('/user/display', [UserController::class, 'display']);
    Route::post('/user/user-data', [UserController::class, 'getUserData']);

    Route::post('/user/account-detail/create', [AccountController::class, 'create']);
    Route::get('/user/account-detail/view', [AccountController::class, 'view']);
    Route::post('/user/account-detail/Userview', [AccountController::class, 'Userview']);
    Route::post('/user/account-detail/get-transaction', [AccountController::class, 'getTransaction']);
   
    Route::put('/user/account-detail/update', [AccountController::class, 'update']);
    Route::delete('/user/account-detail/delete', [AccountController::class, 'delete']);
    Route::post('/user/account-detail/report', [AccountController::class, 'report']);

    Route::post('role/create', [RoleController::class, 'createRole']);
    Route::post('role/getRole', [RoleController::class, 'getRole']);
    Route::post('role/update', [RoleController::class, 'updateRole']);
    Route::delete('role/delete', [RoleController::class, 'deleteRole']);
    Route::get('role/roles-with-permissions', [RoleController::class, 'getPermission']);
    Route::post('role/assignpermissions', [RoleController::class, 'assignpermission']);

    Route::post('/logout', [AuthController::class, 'logout']);


