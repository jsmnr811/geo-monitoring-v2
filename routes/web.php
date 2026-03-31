<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/login', [\App\Http\Controllers\GeoMappingLoginController::class, 'create'])->name('login');
Route::post('/login', [\App\Http\Controllers\GeoMappingLoginController::class, 'store']);
Route::get('/geo-mapping-login', [\App\Http\Controllers\GeoMappingLoginController::class, 'create'])->name('geo-mapping.login');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('/synced-albums', \App\Livewire\SyncedAlbums::class)->name('synced-albums');
    Route::get('/sidlan-data', \App\Livewire\SidlanData::class)->name('sidlan-data');
});

require __DIR__.'/settings.php';
require __DIR__.'/api-test.php';
