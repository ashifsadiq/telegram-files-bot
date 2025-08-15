<?php

use App\Http\Controllers\TelegramWebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::prefix('telegram/webhooks')->group(function () {
    Route::post('inbound', [TelegramWebController::class, 'bot'])->name('api.webhook.inbound');
    Route::post('update', [TelegramWebController::class, 'store'])->name('api.webhook.update');
    Route::post('delete', [TelegramWebController::class, 'destroy'])->name('api.webhook.delete');
    Route::post('reset', [TelegramWebController::class, 'reset'])->name('api.webhook.reset');
    Route::post('/run-command', [TelegramWebController::class, 'migrateFreshSeed'])->name('api.migrateFreshSeed');
});
