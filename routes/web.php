<?php

use Illuminate\Support\Facades\Route;

Route::get('login', App\Livewire\Auth\LoginPage::class)->name('login');
Route::get('/', fn() => redirect()->route('login'))->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('contents.dashboard.index');
    })->name('dashboard.index');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('', \App\Livewire\Users\Table::class)->name('index');
        Route::get('/create', \App\Livewire\Users\CreatePage::class)->name('create');
        Route::get('/{user}/edit', \App\Livewire\Users\UpdatePage::class)->name('edit');
    });

    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('', \App\Livewire\Branches\Table::class)->name('index');
        Route::get('/create', \App\Livewire\Branches\CreatePage::class)->name('create');
        Route::get('/{branch}/edit', \App\Livewire\Branches\UpdatePage::class)->name('edit');
    });

    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('', \App\Livewire\Patients\Table::class)->name('index');
        Route::get('/create', \App\Livewire\Patients\CreatePage::class)->name('create');
        Route::get('/{patient}/edit', \App\Livewire\Patients\UpdatePage::class)->name('edit');
        Route::get('/{patient}/view', \App\Livewire\Patients\ViewPage::class)->name('view');
    });

    Route::prefix('dental-service-types')->name('dental-service-types.')->group(function () {
        Route::get('', \App\Livewire\DentalServiceTypes\Table::class)->name('index');
        Route::get('/create', \App\Livewire\DentalServiceTypes\CreatePage::class)->name('create');
        Route::get('/{dentalServiceType}/edit', \App\Livewire\DentalServiceTypes\UpdatePage::class)->name('edit');
    });

    Route::prefix('dental-services')->name('dental-services.')->group(function () {
        Route::get('', \App\Livewire\DentalServices\Table::class)->name('index');
        Route::get('/create', \App\Livewire\DentalServices\CreatePage::class)->name('create');
        Route::get('/{dentalService}/edit', \App\Livewire\DentalServices\UpdatePage::class)->name('edit');
    });

    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('', \App\Livewire\Appointments\Table::class)->name('index');
        Route::get('/create', \App\Livewire\Appointments\CreatePage::class)->name('create');
        Route::get('/{appointment}/edit', \App\Livewire\Appointments\UpdatePage::class)->name('edit');
        Route::get('/{appointment}/view', \App\Livewire\Appointments\ViewPage::class)->name('view');
    });

    Route::prefix('patient-visits')->name('patient-visits.')->group(function () {
        Route::get('', \App\Livewire\PatientVisits\Table::class)->name('index');
        Route::get('/create', \App\Livewire\PatientVisits\CreatePage::class)->name('create');
        Route::get('/{patientVisit}/edit', \App\Livewire\PatientVisits\UpdatePage::class)->name('edit');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('', \App\Livewire\Inventories\Table::class)->name('index');
        Route::get('/create', \App\Livewire\Inventories\CreatePage::class)->name('create');
        Route::get('/{inventory}/edit', \App\Livewire\Inventories\UpdatePage::class)->name('edit');
    });
});