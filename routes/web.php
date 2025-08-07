<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('contents.dashboard.index');
    })->name('dashboard.index');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('', App\Livewire\Users\Table::class)->name('index');
        Route::get('/create', App\Livewire\Users\CreatePage::class)->name('create');
        Route::get('/{user}/edit', App\Livewire\Users\UpdatePage::class)->name('edit');
    });
});
