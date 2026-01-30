<?php

declare(strict_types=1);

use Arzou\MimeGuard\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('mime-guard')->name('mime-guard.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::post('/', [SettingsController::class, 'update'])->name('update');
});
