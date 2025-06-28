<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthenticationController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\ResetPasswordController;

Route::post('register', [AuthenticationController::class, 'register'])->name('register');
Route::post('login', [AuthenticationController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function () {
    Route::get('get-user', [AuthenticationController::class, 'userInfo'])->name('get-user');
    Route::post('logout', [AuthenticationController::class, 'logOut'])->name('logout');

    Route::get('get-user-info', [AuthenticationController::class, 'getuserdata'])->name('get-user-info');

    Route::post('new-conversation', [ConversationController::class, 'newconversation'])->name('new-conversation');
    Route::get('get-conversations', [ConversationController::class, 'getConversations'])->name('get-conversations');
    Route::get('get-messages/{id}', [ConversationController::class, 'getMessages'])->name('get-messages');
    Route::post('send-message', [MessageController::class, 'sendMessage'])->name('send-message');
});

Route::post('/send-otp', [VerifyEmailController::class, 'sendOtp'])
    ->name('verification.send')
    ->middleware(['throttle:6,1']);

Route::post('/verify-otp', [VerifyEmailController::class, 'verifyOtp'])
    ->name('verification.verify')
    ->middleware(['throttle:6,1']);

Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink'])
    ->name('password.email')
    ->middleware(['throttle:6,1']);

Route::post('/forgot-password-token', [ResetPasswordController::class, 'verifyOtp'])
    ->name('password.reset')
    ->middleware(['throttle:6,1']);

Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])
    ->name('password.update')
    ->middleware(['throttle:6,1']);