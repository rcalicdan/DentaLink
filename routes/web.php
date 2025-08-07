<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', App\Livewire\Auth\LoginPage::class)->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('', App\Livewire\Users\Table::class)->name('index');
        Route::get('/create', App\Livewire\Users\CreatePage::class)->name('create');
        Route::get('/{user}/edit', App\Livewire\Users\UpdatePage::class)->name('edit');
    });
});
