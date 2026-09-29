<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Monitoring\HomeController as MonitoringHomeController;
use App\Http\Controllers\Monitoring\SystemStatusController as MonitoringSystemStatusController;
use App\Http\Controllers\Monitoring\ProfileController as MonitoringProfileController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AccessLogController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\BlacklistController;
use App\Http\Controllers\Admin\SystemStatusController;
use App\Http\Controllers\Admin\ProfileController;


/*
|--------------------------------------------------------------------------
| MONITORING PUBLIK / DOSEN / USER
|--------------------------------------------------------------------------
|
| Semua monitoring utama digabung ke SATU index.
| Header publik hanya: Monitoring, Status, Profil.
|
*/

Route::get(
    '/',
    [MonitoringHomeController::class, 'index']
)->name('monitoring.home');

Route::redirect(
    '/monitoring',
    '/'
);


/*
|--------------------------------------------------------------------------
| URL MONITORING LAMA
|--------------------------------------------------------------------------
|
| Tidak dihapus agar link lama tidak 404.
| Semuanya diarahkan ke bagian yang sesuai pada index terpadu.
|
*/

Route::get(
    '/monitoring/access',
    fn () => redirect('/#akses-terbaru')
)->name('monitoring.access');

Route::get(
    '/monitoring/devices',
    fn () => redirect('/#perangkat')
)->name('monitoring.devices');

Route::get(
    '/monitoring/cache',
    fn () => redirect('/#cache')
)->name('monitoring.cache');

Route::get(
    '/monitoring/statistics',
    fn () => redirect('/#analisis')
)->name('monitoring.statistics');

Route::get(
    '/monitoring/blacklist',
    fn () => redirect('/#filter-blacklist')
)->name('monitoring.blacklist');


/*
|--------------------------------------------------------------------------
| STATUS DETAIL
|--------------------------------------------------------------------------
*/

Route::get(
    '/monitoring/status',
    [MonitoringSystemStatusController::class, 'index']
)->name('monitoring.status');


/*
|--------------------------------------------------------------------------
| PROFIL
|--------------------------------------------------------------------------
*/

Route::get(
    '/monitoring/profile',
    [MonitoringProfileController::class, 'index']
)->name('monitoring.profile');


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
|
| Admin tetap terpisah karena mempunyai aksi pengelolaan.
|
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/',
            [DashboardController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/access',
            [AccessLogController::class, 'index']
        )->name('access.index');

        Route::get(
            '/devices',
            [DeviceController::class, 'index']
        )->name('devices.index');

        Route::post(
            '/devices/sync',
            [DeviceController::class, 'sync']
        )->name('devices.sync');

        Route::get(
            '/cache',
            [CacheController::class, 'index']
        )->name('cache.index');

        Route::get(
            '/statistics',
            [StatisticsController::class, 'index']
        )->name('statistics.index');

        Route::get(
            '/blacklist',
            [BlacklistController::class, 'index']
        )->name('blacklist.index');

        Route::post(
            '/blacklist',
            [BlacklistController::class, 'store']
        )->name('blacklist.store');

        Route::post(
            '/blacklist/sync',
            [BlacklistController::class, 'sync']
        )->name('blacklist.sync');

        Route::patch(
            '/blacklist/{blacklistDomain}/toggle',
            [BlacklistController::class, 'toggle']
        )->name('blacklist.toggle');

        Route::put(
            '/blacklist/{blacklistDomain}',
            [BlacklistController::class, 'update']
        )->name('blacklist.update');

        Route::delete(
            '/blacklist/{blacklistDomain}',
            [BlacklistController::class, 'destroy']
        )->name('blacklist.destroy');

        Route::get(
            '/status',
            [SystemStatusController::class, 'index']
        )->name('status.index');

        Route::get(
            '/profile',
            [ProfileController::class, 'index']
        )->name('profile');
    });
