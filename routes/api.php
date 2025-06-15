<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthenticationController;
use App\Http\Controllers\ConversationController;

Route::post('register', [AuthenticationController::class, 'register'])->name('register');
Route::post('login', [AuthenticationController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function () {
    Route::get('get-user', [AuthenticationController::class, 'userInfo'])->name('get-user');
    Route::post('logout', [AuthenticationController::class, 'logOut'])->name('logout');

    Route::get('get-user-info', [AuthenticationController::class, 'getuserdata'])->name('get-user-info');

    Route::post('new-conversation', [ConversationController::class, 'newconversation'])->name('new-conversation');
    Route::get('get-conversations', [ConversationController::class, 'getConversations'])->name('get-conversations');
    Route::get('get-messages/{id}', [ConversationController::class, 'getMessages'])->name('get-messages');
});
