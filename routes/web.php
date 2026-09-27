<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Monitoring\HomeController as MonitoringHomeController;
use App\Http\Controllers\Monitoring\AccessController as MonitoringAccessController;
use App\Http\Controllers\Monitoring\DeviceController as MonitoringDeviceController;
use App\Http\Controllers\Monitoring\CacheController as MonitoringCacheController;
use App\Http\Controllers\Monitoring\StatisticsController as MonitoringStatisticsController;
use App\Http\Controllers\Monitoring\BlacklistController as MonitoringBlacklistController;
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
| HOME / MONITORING
|--------------------------------------------------------------------------
|
| Halaman utama untuk user / dosen.
| Bersifat read-only.
|
*/

Route::get(
    '/',
    [MonitoringHomeController::class, 'index']
)->name('monitoring.home');


/*
|--------------------------------------------------------------------------
| Redirect /monitoring ke halaman utama
|--------------------------------------------------------------------------
*/

Route::redirect(
    '/monitoring',
    '/'
);


/*
|--------------------------------------------------------------------------
| MONITORING / USER / DOSEN
|--------------------------------------------------------------------------
|
| Halaman monitoring read-only.
| Halaman selain beranda masih placeholder.
|
*/

Route::prefix('monitoring')
    ->name('monitoring.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Akses Jaringan
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/access',
            [MonitoringAccessController::class, 'index']
        )->name('access');


        /*
        |--------------------------------------------------------------------------
        | Perangkat
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/devices',
            [MonitoringDeviceController::class, 'index']
        )->name('devices');


        /*
        |--------------------------------------------------------------------------
        | Cache
        |--------------------------------------------------------------------------
        */


        Route::get(
            '/cache',
            [MonitoringCacheController::class, 'index']
        )->name('cache');


        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/statistics',
            [MonitoringStatisticsController::class, 'index']
        )->name('statistics');


        /*
        |--------------------------------------------------------------------------
        | Filter & Blacklist
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/blacklist',
            [MonitoringBlacklistController::class, 'index']
        )->name('blacklist');


        /*
        |--------------------------------------------------------------------------
        | Status Sistem
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/status',
            [MonitoringSystemStatusController::class, 'index']
        )->name('status');


        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/profile',
            [MonitoringProfileController::class, 'index']
        )->name('profile');
    });


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
|
| Belum menggunakan middleware/login.
| Masih tahap development.
|
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [DashboardController::class, 'index']
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Access
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/access',
            [AccessLogController::class, 'index']
        )->name('access.index');


        /*
        |--------------------------------------------------------------------------
        | Devices
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/devices',
            [DeviceController::class, 'index']
        )->name('devices.index');


        Route::post(
            '/devices/sync',
            [DeviceController::class, 'sync']
        )->name('devices.sync');


        /*
        |--------------------------------------------------------------------------
        | Cache
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/cache',
            [CacheController::class, 'index']
        )->name('cache.index');


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/statistics',
            [StatisticsController::class, 'index']
        )->name('statistics.index');


        /*
        |--------------------------------------------------------------------------
        | Blacklist
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | System Status
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/status',
            [SystemStatusController::class, 'index']
        )->name('status.index');


        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/profile',
            [ProfileController::class, 'index']
        )->name('profile');
    });
