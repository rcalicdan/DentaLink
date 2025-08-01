<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('users')->name('users.')->group(function () {
    Route::get('', App\Livewire\Users\Table::class)->name('index');
});

Route::view('appointments', 'static-contents.appointment-scheduling-form');
Route::view('branches', 'static-contents.branch-form');
Route::view('inventories', 'static-contents.inventory-management-form');
Route::view('patient-visits', 'static-contents.patients-visit-form');
Route::view('patients', 'static-contents.patient-registration-form');
Route::view('quick-actions', 'static-contents.quick-actions');
Route::view('service-types', 'static-contents.service-type-form');
Route::view('services', 'static-contents.service-form');
// Route::view('users', 'static-contents.user-management');

