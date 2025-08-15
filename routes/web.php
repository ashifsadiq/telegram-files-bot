<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramWebController;
use App\Http\Controllers\TGMiniAppController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::prefix('telegram/web-app')->group(function () {
    Route::get('files/{chatID}', [TGMiniAppController::class, 'files'])->name('telegram.web-app.files');
});
Route::middleware('auth')->group(function () {
    Route::prefix('telegram/settings')->group(function () {
        Route::get('webhook', [TelegramWebController::class, 'index'])->name('telegram.webhook.index');
    });
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__ . '/auth.php';
