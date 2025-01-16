<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/support/get', [ChatController::class, 'getSupportChats']);
Route::get('/user/messages', [ChatController::class, 'getUserMessages']);
Route::get('/messages/{chatId}', [ChatController::class, 'getChatMessages']);
Route::post('/send', [ChatController::class, 'sendMessage']);

