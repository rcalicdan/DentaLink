<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('login', App\Livewire\Auth\LoginPage::class)->name('login');
Route::get('/', fn() => redirect()->route('login'))->name('home');
Route::get('feedback', \App\Livewire\Feedback\PublicFeedbackPage::class)->name('feedback.public');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index')->can('view-dashboard');

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

    Route::prefix('feedback-admin')->name('feedback.')->group(function () {
        Route::get('', \App\Livewire\Feedback\Table::class)->name('index');
    });

    Route::prefix('dental-services')->name('dental-services.')->group(function () {
        Route::get('', \App\Livewire\DentalServices\Table::class)->name('index');
        Route::get('/create', \App\Livewire\DentalServices\CreatePage::class)->name('create');
        Route::get('/{dentalService}/edit', \App\Livewire\DentalServices\UpdatePage::class)->name('edit');
    });

    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('', \App\Livewire\Appointments\Table::class)->name('index');
        Route::get('/calendar', \App\Livewire\Appointments\Calendar::class)->name('calendar'); // Add this
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

    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('', \App\Livewire\AuditLogs\Table::class)->name('index');
        Route::get('/{auditLog}', \App\Livewire\AuditLogs\ViewPage::class)->name('view');
    });

    Route::get('profile', \App\Livewire\Profile\ProfilePage::class)->name('profile.edit');

    Route::get('/chat/stream', [ChatController::class, 'stream'])->name('chat.stream');
});
