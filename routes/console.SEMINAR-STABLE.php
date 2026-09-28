<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
/*
|--------------------------------------------------------------------------
| MikroTik Device Synchronization
|--------------------------------------------------------------------------
|
| Sinkronisasi ARP + DHCP MikroTik ke tabel devices.
| Hanya berjalan ketika MIKROTIK_DEVICE_SYNC_ENABLED=true.
|
*/

Schedule::command('mikrotik:devices-sync')
    ->everyMinute()
    ->withoutOverlapping()
    ->when(function () {
        return (bool) config(
            'mikrotik.device_sync.enabled',
            false
        );
    });
Schedule::command('squid:import')
    ->everyMinute()
    ->withoutOverlapping();

