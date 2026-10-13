<?php

use App\Http\Controllers\FoundItemController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LostItemController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('items', [ItemController::class, 'index'])->name('items.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'DashboardUser')->name('dashboard');

    Route::get('lost-items', [LostItemController::class, 'index'])->name('lost-items.index');
    Route::get('lost-items/create', [LostItemController::class, 'create'])->name('lost-items.create');
    Route::post('lost-items', [LostItemController::class, 'store'])->name('lost-items.store');
    Route::get('lost-items/{lost_item}/edit', [LostItemController::class, 'edit'])->name('lost-items.edit');
    Route::put('lost-items/{lost_item}', [LostItemController::class, 'update'])->name('lost-items.update');
    Route::patch('lost-items/{lost_item}/status', [LostItemController::class, 'updateStatus'])->name('lost-items.status');
    Route::delete('lost-items/{lost_item}', [LostItemController::class, 'destroy'])->name('lost-items.destroy');

    Route::get('found-items', [FoundItemController::class, 'index'])->name('found-items.index');
    Route::get('found-items/create', [FoundItemController::class, 'create'])->name('found-items.create');
    Route::post('found-items', [FoundItemController::class, 'store'])->name('found-items.store');
    Route::get('found-items/{found_item}/edit', [FoundItemController::class, 'edit'])->name('found-items.edit');
    Route::put('found-items/{found_item}', [FoundItemController::class, 'update'])->name('found-items.update');
    Route::patch('found-items/{found_item}/status', [FoundItemController::class, 'updateStatus'])->name('found-items.status');
    Route::delete('found-items/{found_item}', [FoundItemController::class, 'destroy'])->name('found-items.destroy');
});

require __DIR__.'/settings.php';
