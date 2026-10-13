<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('items', [ItemController::class, 'index'])->name('items.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'DashboardUser')->name('dashboard');
});

require __DIR__.'/settings.php';
