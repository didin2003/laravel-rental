<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Controllers\UserController;

Route::middleware(['web'])->group(function () {
    Route::get('login', [AuthController::class, 'loginForm'])->name('login.form');
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::get('otp-send', [AuthController::class, 'showOtpForm'])->name('otp.form');
    Route::post('otp-send', [AuthController::class, 'otpSend'])->name('otp.send');

    Route::get('otp-verify', [AuthController::class, 'verifyOtpForm'])->name('otp.verify.form');
    Route::post('otp-verify', [AuthController::class, 'verifyOtp'])->name('otp.verify');

    Route::middleware(['otp.verified'])->group(function () {
        Route::get('register', [AuthController::class, 'registerForm'])->name('register.form');
        Route::post('register', [AuthController::class, 'register'])->name('register');

        Route::get('forgot', [AuthController::class, 'forgotForm'])->name('forgot.form');
        Route::post('forgot', [AuthController::class, 'forgot'])->name('forgot');
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('profile', [AuthController::class, 'profile'])->name('profile');
        Route::put('edit-profile', [AuthController::class, 'editProfile'])->name('edit.profile');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('reset.password');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware(['role:super_admin'])->group(function () {
            Route::get('/manage-admins', [UserController::class, 'manageAdmins'])->name('manage.admins');
            Route::post('/register-admins', [UserController::class, 'registerAdmin'])->name('register.admins');
            Route::post('/edit-admin/{id}', [UserController::class, 'editAdmin'])->name('edit.admin');
            Route::delete('/delete-admin/{id}', [UserController::class, 'deleteAdmin'])->name('delete.admin');
            Route::delete('delete-multiple-admins', [UserController::class, 'deleteMultipleAdmins'])->name('delete.multiple.admins');
        });

    });

});
