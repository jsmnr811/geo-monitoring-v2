<?php

use App\Http\Controllers\GeoMappingLoginController;
use App\Livewire\SidlanData;
use App\Livewire\SidlanProgress;
use App\Livewire\SpAlbums;
use App\Livewire\SyncedAlbums;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/login', [GeoMappingLoginController::class, 'create'])->name('login');
Route::post('/login', [GeoMappingLoginController::class, 'store'])->name('login.store');
Route::get('/geo-mapping-login', [GeoMappingLoginController::class, 'create'])->name('geo-mapping.login');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('/synced-albums/{spId?}', SyncedAlbums::class)->name('synced-albums');
    Route::get('/sidlan-data', SidlanData::class)->name('sidlan-data');
    Route::get('/sidlan-progress', SidlanProgress::class)->name('sidlan-progress');
    Route::get('/sp-albums/{spId}', SpAlbums::class)->name('sp-albums');
});

require __DIR__.'/settings.php';
require __DIR__.'/api-test.php';
