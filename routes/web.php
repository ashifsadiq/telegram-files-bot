<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramWebController;
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
Route::prefix('telegram/settings')->group(function () {
    Route::get('webhook', [TelegramWebController::class, 'index'])->name('telegram.webhook.index');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
[
    'update_id' => 279888222,
    'message'   => [
        'message_id' => 21161,
        'from'       => [
            'id'            => 824045233,
            'is_bot'        => false,
            'first_name'    => 'Ashif Sadiq',
            'username'      => 'ashifsadiq1',
            'language_code' => 'en',
        ],
        'chat'       => [
            'id'         => 824045233,
            'first_name' => 'Ashif Sadiq',
            'username'   => 'ashifsadiq1',
            'type'       => 'private',
        ],
        'date'       => 1754505850,
        'text'       => '/start',
        'entities'   => [
            0 => [
                'offset' => 0,
                'length' => 6,
                'type'   => 'bot_command',
            ],
        ],
    ],
];
require __DIR__ . '/auth.php';
