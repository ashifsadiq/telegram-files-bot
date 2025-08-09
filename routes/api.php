<?php

use App\Http\Controllers\TelegramWebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// https://api.telegram.org/bot6410770571:AAGKx0DGe_hfQ9ASSUJ88tPn0WJ5Ql0MWBk/setWebhook?url=https://cc491ba0f821.ngrok-free.app
// /api/telegram/webhooks/inbound
Route::prefix('telegram/webhooks')->group(function () {
    Route::post('inbound', [TelegramWebController::class, 'bot'])->name('api.webhook.inbound');
    Route::post('update', [TelegramWebController::class, 'store'])->name('api.webhook.update');
    Route::post('delete', [TelegramWebController::class, 'destroy'])->name('api.webhook.delete');
    Route::post('/run-command', [TelegramWebController::class, 'migrateFreshSeed'])->name('api.migrateFreshSeed');
});
