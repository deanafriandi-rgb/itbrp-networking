<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MikroTik Integration Mode
    |--------------------------------------------------------------------------
    |
    | local      = development Laravel di Windows
    | production = koneksi ke RouterOS asli
    |
    */

    'mode' => env(
        'MIKROTIK_MODE',
        'local'
    ),


    /*
    |--------------------------------------------------------------------------
    | Integration Enabled
    |--------------------------------------------------------------------------
    */

    'enabled' => filter_var(
        env(
            'MIKROTIK_ENABLED',
            false
        ),
        FILTER_VALIDATE_BOOL
    ),


    /*
    |--------------------------------------------------------------------------
    | Primary Router
    |--------------------------------------------------------------------------
    |
    | CCR Putih:
    | 10.50.50.1
    |
    */

    'host' => env(
        'MIKROTIK_HOST',
        '10.50.50.1'
    ),


    /*
    |--------------------------------------------------------------------------
    | RouterOS API
    |--------------------------------------------------------------------------
    |
    | 8728 = API biasa
    | 8729 = API SSL
    |
    | Kita targetkan API-SSL untuk production.
    |
    */

    'port' => (int) env(
        'MIKROTIK_PORT',
        8729
    ),

    'use_ssl' => filter_var(
        env(
            'MIKROTIK_USE_SSL',
            true
        ),
        FILTER_VALIDATE_BOOL
    ),


    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | Jangan gunakan akun admin utama.
    | Nanti buat user RouterOS khusus Laravel.
    |
    */

    'username' => env(
        'MIKROTIK_USERNAME',
        ''
    ),

    'password' => env(
        'MIKROTIK_PASSWORD',
        ''
    ),


    /*
    |--------------------------------------------------------------------------
    | Connection
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env(
        'MIKROTIK_TIMEOUT',
        5
    ),

    'attempts' => (int) env(
        'MIKROTIK_ATTEMPTS',
        2
    ),


    /*
    |--------------------------------------------------------------------------
    | Device Synchronization
    |--------------------------------------------------------------------------
    */

    'device_sync' => [

        'enabled' => filter_var(
            env(
                'MIKROTIK_DEVICE_SYNC_ENABLED',
                false
            ),
            FILTER_VALIDATE_BOOL
        ),

        /*
        |--------------------------------------------------------------------------
        | Berapa menit perangkat tidak terlihat
        | sebelum dianggap offline.
        |--------------------------------------------------------------------------
        */

        'offline_after_minutes' => (int) env(
            'MIKROTIK_OFFLINE_AFTER_MINUTES',
            5
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Campus Network Mapping
    |--------------------------------------------------------------------------
    */

    'segments' => [

        [
            'cidr' => '172.16.4.0/23',
            'name' => 'Gedung A',
        ],

        [
            'cidr' => '172.16.6.0/23',
            'name' => 'Gedung B',
        ],

        [
            'cidr' => '12.12.12.0/24',
            'name' => 'Akademik',
        ],

        [
            'cidr' => '10.10.10.0/24',
            'name' => 'Link RB3011',
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Safety Guard
    |--------------------------------------------------------------------------
    */

    'safety' => [

        /*
        |--------------------------------------------------------------------------
        | Izin eksplisit untuk koneksi router asli.
        |--------------------------------------------------------------------------
        */

        'allow_remote_connection' => filter_var(
            env(
                'MIKROTIK_ALLOW_REMOTE_CONNECTION',
                false
            ),
            FILTER_VALIDATE_BOOL
        ),


        /*
        |--------------------------------------------------------------------------
        | Jangan izinkan username kosong.
        |--------------------------------------------------------------------------
        */

        'require_username' => true,


        /*
        |--------------------------------------------------------------------------
        | Jangan izinkan password kosong.
        |--------------------------------------------------------------------------
        */

        'require_password' => true,


        /*
        |--------------------------------------------------------------------------
        | Production wajib SSL.
        |--------------------------------------------------------------------------
        */

        'require_ssl' => filter_var(
            env(
                'MIKROTIK_REQUIRE_SSL',
                true
            ),
            FILTER_VALIDATE_BOOL
        ),

    ],

];
