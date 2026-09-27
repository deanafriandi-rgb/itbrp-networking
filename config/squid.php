<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mode Integrasi Squid
    |--------------------------------------------------------------------------
    |
    | local      = development di Windows / Laragon
    | production = Ubuntu Server + Squid asli
    |
    */

    'mode' => env('SQUID_MODE', 'local'),


    /*
    |--------------------------------------------------------------------------
    | Squid Server
    |--------------------------------------------------------------------------
    */

    'host' => env(
        'SQUID_HOST',
        '10.50.50.2'
    ),


    /*
    |--------------------------------------------------------------------------
    | Squid Ports
    |--------------------------------------------------------------------------
    |
    | 3128 = Forward Proxy
    | 3129 = HTTP Intercept
    | 3130 = HTTPS Intercept
    |
    */

    'ports' => [

        'forward' => (int) env(
            'SQUID_FORWARD_PORT',
            3128
        ),

        'http_intercept' => (int) env(
            'SQUID_HTTP_INTERCEPT_PORT',
            3129
        ),

        'https_intercept' => (int) env(
            'SQUID_HTTPS_INTERCEPT_PORT',
            3130
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Access Log
    |--------------------------------------------------------------------------
    |
    | LOCAL:
    | storage/logs/squid-access.log
    |
    | PRODUCTION:
    | /var/log/squid/access.log
    |
    */

    'access_log' => env(
        'SQUID_ACCESS_LOG',
        storage_path('logs/squid-access.log')
    ),


    /*
    |--------------------------------------------------------------------------
    | Blacklist
    |--------------------------------------------------------------------------
    |
    | LOCAL:
    | storage/app/squid/blacklist.txt
    |
    | PRODUCTION:
    | /etc/squid/blacklist.txt
    |
    */

    'blacklist_file' => env(
        'SQUID_BLACKLIST_FILE',
        storage_path(
            'app/squid/blacklist.txt'
        )
    ),


    /*
    |--------------------------------------------------------------------------
    | Reconfigure
    |--------------------------------------------------------------------------
    |
    | FALSE selama development.
    | TRUE nanti setelah Laravel berada di Ubuntu.
    |
    */

    'reconfigure_enabled' => filter_var(
        env(
            'SQUID_RECONFIGURE_ENABLED',
            false
        ),
        FILTER_VALIDATE_BOOL
    ),


    /*
    |--------------------------------------------------------------------------
    | Command Squid
    |--------------------------------------------------------------------------
    */

    'commands' => [

        'binary' => env(
            'SQUID_BINARY',
            'squid'
        ),

        'parse' => env(
            'SQUID_PARSE_COMMAND',
            'squid -k parse'
        ),

        'reconfigure' => env(
            'SQUID_RECONFIGURE_COMMAND',
            'squid -k reconfigure'
        ),

    ],
    /*
|--------------------------------------------------------------------------
| Production Safety
|--------------------------------------------------------------------------
|
| Proteksi agar command Squid production tidak dijalankan
| dari environment yang salah.
|
*/

    'safety' => [

        /*
    |--------------------------------------------------------------------------
    | Master Permission
    |--------------------------------------------------------------------------
    |
    | Harus TRUE di Ubuntu production agar Laravel boleh
    | menjalankan command Squid.
    |
    */

        'allow_production_commands' => filter_var(
            env(
                'SQUID_ALLOW_PRODUCTION_COMMANDS',
                false
            ),
            FILTER_VALIDATE_BOOL
        ),


        /*
    |--------------------------------------------------------------------------
    | Production wajib Linux
    |--------------------------------------------------------------------------
    */

        'require_linux' => filter_var(
            env(
                'SQUID_REQUIRE_LINUX',
                true
            ),
            FILTER_VALIDATE_BOOL
        ),


        /*
    |--------------------------------------------------------------------------
    | Binary production harus absolute path
    |--------------------------------------------------------------------------
    |
    | Contoh:
    | /usr/sbin/squid
    |
    */

        'require_absolute_binary' => filter_var(
            env(
                'SQUID_REQUIRE_ABSOLUTE_BINARY',
                true
            ),
            FILTER_VALIDATE_BOOL
        ),


        /*
    |--------------------------------------------------------------------------
    | Binary harus executable
    |--------------------------------------------------------------------------
    */

        'require_executable_binary' => filter_var(
            env(
                'SQUID_REQUIRE_EXECUTABLE_BINARY',
                true
            ),
            FILTER_VALIDATE_BOOL
        ),

    ],

    /*
    |--------------------------------------------------------------------------
    | IP yang Tidak Perlu Masuk Monitoring
    |--------------------------------------------------------------------------
    |
    | 10.50.50.1 = CCR MikroTik / Netwatch
    |
    */

    'ignored_ips' => array_values(
        array_filter(
            array_map(
                'trim',
                explode(
                    ',',
                    env(
                        'SQUID_IGNORED_IPS',
                        '10.50.50.1,127.0.0.1,::1'
                    )
                )
            )
        )
    ),

];
