<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\School\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix(__('routes.system'))->group(function () {
    Route::get('/', function () {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return redirect()->route('login');
    });

    Route::get('/' . __('routes.home'), [DashboardController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('guest')->group(function () {
        // Route::get('registro', [RegisteredUserController::class, 'create'])
        //             ->name('register');

        // Route::post('registro', [RegisteredUserController::class, 'store']);

        Route::get(__('routes.access'), [AuthenticatedSessionController::class, 'create'])
                    ->name('login');

        Route::post(__('routes.access'), [AuthenticatedSessionController::class, 'store']);

        Route::get(__('routes.forgot-password'), [PasswordResetLinkController::class, 'create'])
                    ->name('password.request');

        Route::post(__('routes.forgot-password'), [PasswordResetLinkController::class, 'store'])
                    ->name('password.email');

        Route::get(__('routes.reset-password') . '/{token}', [NewPasswordController::class, 'create'])
                    ->name('password.reset');

        Route::post(__('routes.reset-password'), [NewPasswordController::class, 'store'])
                    ->name('password.store');
    });

    Route::middleware('auth')->group(function () {
        Route::get(__('routes.verify-email'), EmailVerificationPromptController::class)
                    ->name('verification.notice');

        Route::get(__('routes.verify-email') . '/{id}/{hash}', VerifyEmailController::class)
                    ->middleware(['signed', 'throttle:6,1'])
                    ->name('verification.verify');

        Route::post(__('routes.email/verification-notification'), [EmailVerificationNotificationController::class, 'store'])
                    ->middleware('throttle:6,1')
                    ->name('verification.send');

        Route::get(__('routes.confirm-password'), [ConfirmablePasswordController::class, 'show'])
                    ->name('password.confirm');

        Route::post(__('routes.confirm-password'), [ConfirmablePasswordController::class, 'store']);

        Route::put(__('routes.password'), [PasswordController::class, 'update'])->name('password.update');

        Route::post(__('routes.logout'), [AuthenticatedSessionController::class, 'destroy'])
                    ->name('logout');
        Route::get(__('routes.logout'), [AuthenticatedSessionController::class, 'destroy'])
                    ->name('logout.get');
    });
});
