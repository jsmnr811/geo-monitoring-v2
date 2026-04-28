<?php

use App\Http\Controllers\GeoMappingLoginController;
use App\Livewire\ManagementDashboard;
use App\Livewire\SidlanData;
use App\Livewire\SpAlbums;
use App\Livewire\SyncedAlbums;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('subprojects') : redirect()->route('login');
})->name('home');

Route::get('/login', [GeoMappingLoginController::class, 'create'])->name('login');
Route::post('/login', [GeoMappingLoginController::class, 'store'])->name('login.store');
Route::get('/geo-mapping-login', [GeoMappingLoginController::class, 'create'])->name('geo-mapping.login');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('/management-dashboard', ManagementDashboard::class)->name('management-dashboard');
    Route::get('/synced-albums/{spId?}', SyncedAlbums::class)->name('synced-albums');
    Route::get('/subprojects', SidlanData::class)->name('subprojects');

    Route::get('/sp-data/{spId}', SpAlbums::class)->name('sp-data');
});

// Route::prefix('guest')->group(function () {
//     Route::get('/management-dashboard', ManagementDashboard::class)->name('management-dashboard');
//     Route::get('/synced-albums/{spId?}', SyncedAlbums::class)->name('synced-albums');
//     Route::get('/subprojects', SidlanData::class)->name('subprojects');
// });

require __DIR__.'/settings.php';
require __DIR__.'/api-test.php';
