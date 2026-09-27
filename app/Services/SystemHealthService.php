<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class SystemHealthService
{
    public function __construct(
        private SquidControlService $squidControl,
        private SquidBlacklistService $blacklist,
        private MikrotikClientService $mikrotik
    ) {
    }


    /**
     * Semua status sistem untuk dashboard.
     */
    public function getStatusData(): array
    {
        $database =
            $this->checkDatabase();

        $accessLog =
            $this->checkAccessLog();

        $blacklist =
            $this->checkBlacklist();

        $squid =
            $this->checkSquidControl();

        $mikrotik =
            $this->checkMikrotik();

        $ports =
            $this->checkSquidPorts();


        $checks = collect([
            $database,
            $accessLog,
            $blacklist,
            $squid,
            $mikrotik,
        ])
        ->merge(
            collect($ports)
        )
        ->values();


        $summary = [
            'total' =>
                $checks->count(),

            'online' =>
                $checks
                    ->where(
                        'status',
                        'online'
                    )
                    ->count(),

            'warning' =>
                $checks
                    ->where(
                        'status',
                        'warning'
                    )
                    ->count(),

            'offline' =>
                $checks
                    ->where(
                        'status',
                        'offline'
                    )
                    ->count(),

            'local' =>
                $checks
                    ->whereIn(
                        'status',
                        [
                            'local',
                            'not_checked',
                        ]
                    )
                    ->count(),
        ];


        return [
            'checked_at' =>
                now(
                    'Asia/Jakarta'
                ),

            'summary' =>
                $summary,

            'database' =>
                $database,

            'accessLog' =>
                $accessLog,

            'blacklist' =>
                $blacklist,

            'squid' =>
                $squid,

            'mikrotik' =>
                $mikrotik,

            'ports' =>
                $ports,

            'checks' =>
                $checks,
        ];
    }


    /**
     * Database Laravel / MySQL.
     */
    private function checkDatabase(): array
    {
        $started =
            microtime(true);


        try {

            DB::connection()
                ->getPdo();


            DB::select(
                'SELECT 1'
            );


            $responseMs =
                round(
                    (
                        microtime(true)
                        -
                        $started
                    )
                    *
                    1000,
                    2
                );


            return [
                'key' =>
                    'database',

                'name' =>
                    'Database',

                'status' =>
                    'online',

                'message' =>
                    'Koneksi database berhasil.',

                'response_ms' =>
                    $responseMs,

                'meta' => [
                    'connection' =>
                        config(
                            'database.default'
                        ),

                    'database' =>
                        config(
                            'database.connections.'
                            .
                            config(
                                'database.default'
                            )
                            .
                            '.database'
                        ),
                ],
            ];


        } catch (Throwable $e) {

            return [
                'key' =>
                    'database',

                'name' =>
                    'Database',

                'status' =>
                    'offline',

                'message' =>
                    'Database tidak dapat diakses.',

                'response_ms' =>
                    null,

                'error' =>
                    $e->getMessage(),

                'meta' =>
                    [],
            ];
        }
    }


    /**
     * Squid access.log.
     */
    private function checkAccessLog(): array
    {
        $path =
            (string) config(
                'squid.access_log',
                ''
            );


        if ($path === '') {

            return [
                'key' =>
                    'access_log',

                'name' =>
                    'Squid Access Log',

                'status' =>
                    'warning',

                'message' =>
                    'Path access.log belum dikonfigurasi.',

                'meta' =>
                    [],
            ];
        }


        if (
            !file_exists(
                $path
            )
        ) {

            return [
                'key' =>
                    'access_log',

                'name' =>
                    'Squid Access Log',

                'status' =>
                    'warning',

                'message' =>
                    'File access.log belum tersedia.',

                'meta' => [
                    'path' =>
                        $path,
                ],
            ];
        }


        $size =
            @filesize(
                $path
            );


        $modified =
            @filemtime(
                $path
            );


        return [
            'key' =>
                'access_log',

            'name' =>
                'Squid Access Log',

            'status' =>
                is_readable(
                    $path
                )
                    ? 'online'
                    : 'warning',

            'message' =>
                is_readable(
                    $path
                )
                    ? 'File access.log tersedia dan dapat dibaca.'
                    : 'File tersedia tetapi tidak dapat dibaca.',

            'meta' => [
                'path' =>
                    $path,

                'size' =>
                    $size !== false
                        ? $size
                        : null,

                'modified_at' =>
                    $modified !== false
                        ? Carbon::createFromTimestamp(
                            $modified
                        )
                        ->timezone(
                            'Asia/Jakarta'
                        )
                        : null,
            ],
        ];
    }


    /**
     * blacklist.txt.
     */
    private function checkBlacklist(): array
    {
        try {

            $file =
                $this->blacklist
                    ->readCurrentFile();


            $exists =
                (bool) (
                    $file[
                        'exists'
                    ]
                    ?? false
                );


            $lines =
                collect(
                    $file[
                        'lines'
                    ]
                    ?? []
                );


            return [
                'key' =>
                    'blacklist',

                'name' =>
                    'Blacklist Squid',

                'status' =>
                    $exists
                        ? 'online'
                        : 'warning',

                'message' =>
                    $exists
                        ? 'File blacklist tersedia.'
                        : 'File blacklist belum tersedia.',

                'meta' => [
                    'path' =>
                        config(
                            'squid.blacklist_file'
                        ),

                    'line_count' =>
                        $lines->count(),
                ],
            ];


        } catch (Throwable $e) {

            return [
                'key' =>
                    'blacklist',

                'name' =>
                    'Blacklist Squid',

                'status' =>
                    'warning',

                'message' =>
                    'Status blacklist gagal dibaca.',

                'error' =>
                    $e->getMessage(),

                'meta' =>
                    [],
            ];
        }
    }


    /**
     * Squid control / environment.
     *
     * Tidak menjalankan parse/reconfigure.
     */
    private function checkSquidControl(): array
    {
        try {

            $status =
                $this->squidControl
                    ->status();


            $mode =
                strtolower(
                    (string) (
                        $status[
                            'mode'
                        ]
                        ??
                        config(
                            'squid.mode',
                            'local'
                        )
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | LOCAL
            |--------------------------------------------------------------------------
            */

            if (
                $mode === 'local'
            ) {

                return [
                    'key' =>
                        'squid',

                    'name' =>
                        'Squid Control',

                    'status' =>
                        'local',

                    'message' =>
                        'Squid berada pada mode LOCAL. Perintah production tidak dijalankan.',

                    'meta' => [
                        'mode' =>
                            'local',

                        'host' =>
                            config(
                                'squid.host'
                            ),

                        'reconfigure_enabled' =>
                            false,
                    ],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | PRODUCTION
            |--------------------------------------------------------------------------
            */

            $safety =
                $status[
                    'safety'
                ]
                ?? [];


            $canExecute =
                (bool) (
                    $safety[
                        'can_execute'
                    ]
                    ?? false
                );


            return [
                'key' =>
                    'squid',

                'name' =>
                    'Squid Control',

                'status' =>
                    $canExecute
                        ? 'online'
                        : 'warning',

                'message' =>
                    $canExecute
                        ? 'Environment Squid production siap.'
                        : 'Environment Squid production belum siap.',

                'meta' => [
                    'mode' =>
                        $mode,

                    'host' =>
                        config(
                            'squid.host'
                        ),

                    'reconfigure_enabled' =>
                        (bool) config(
                            'squid.reconfigure_enabled',
                            false
                        ),

                    'can_execute' =>
                        $canExecute,

                    'issues' =>
                        $safety[
                            'issues'
                        ]
                        ?? [],
                ],
            ];


        } catch (Throwable $e) {

            return [
                'key' =>
                    'squid',

                'name' =>
                    'Squid Control',

                'status' =>
                    'warning',

                'message' =>
                    'Status Squid gagal diperiksa.',

                'error' =>
                    $e->getMessage(),

                'meta' =>
                    [],
            ];
        }
    }


    /**
     * MikroTik API.
     */
    private function checkMikrotik(): array
    {
        try {

            $status =
                $this->mikrotik
                    ->status();


            $mode =
                strtolower(
                    (string) (
                        $status[
                            'mode'
                        ]
                        ?? 'local'
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | LOCAL
            |--------------------------------------------------------------------------
            */

            if (
                $mode === 'local'
            ) {

                return [
                    'key' =>
                        'mikrotik',

                    'name' =>
                        'MikroTik RouterOS',

                    'status' =>
                        'local',

                    'message' =>
                        'MikroTik berada pada mode LOCAL. Koneksi router tidak dilakukan.',

                    'meta' => [
                        'host' =>
                            $status[
                                'host'
                            ]
                            ?? null,

                        'port' =>
                            $status[
                                'port'
                            ]
                            ?? null,

                        'ssl' =>
                            $status[
                                'use_ssl'
                            ]
                            ?? false,

                        'device_sync_enabled' =>
                            $status[
                                'device_sync_enabled'
                            ]
                            ?? false,
                    ],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Production tetapi guard belum lolos.
            |--------------------------------------------------------------------------
            */

            if (
                !(
                    $status[
                        'can_connect'
                    ]
                    ?? false
                )
            ) {

                return [
                    'key' =>
                        'mikrotik',

                    'name' =>
                        'MikroTik RouterOS',

                    'status' =>
                        'warning',

                    'message' =>
                        'Konfigurasi MikroTik production belum mengizinkan koneksi.',

                    'meta' =>
                        $status,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Live connection test
            |--------------------------------------------------------------------------
            */

            $test =
                $this->mikrotik
                    ->testConnection();


            return [
                'key' =>
                    'mikrotik',

                'name' =>
                    'MikroTik RouterOS',

                'status' =>
                    (
                        $test[
                            'connected'
                        ]
                        ?? false
                    )
                        ? 'online'
                        : 'offline',

                'message' =>
                    $test[
                        'message'
                    ]
                    ??
                    'Status koneksi MikroTik diperiksa.',

                'meta' => [
                    'host' =>
                        $status[
                            'host'
                        ]
                        ?? null,

                    'port' =>
                        $status[
                            'port'
                        ]
                        ?? null,

                    'identity' =>
                        $test[
                            'identity'
                        ]
                        ?? null,
                ],
            ];


        } catch (Throwable $e) {

            return [
                'key' =>
                    'mikrotik',

                'name' =>
                    'MikroTik RouterOS',

                'status' =>
                    'offline',

                'message' =>
                    'Pemeriksaan MikroTik gagal.',

                'error' =>
                    $e->getMessage(),

                'meta' =>
                    [],
            ];
        }
    }


    /**
     * Port Squid.
     */
    private function checkSquidPorts(): array
    {
        $mode =
            strtolower(
                (string) config(
                    'squid.mode',
                    'local'
                )
            );


        $host =
            (string) config(
                'squid.host',
                '10.50.50.2'
            );


        $ports = [
            'squid_forward' => [
                'name' =>
                    'Squid Forward Proxy',

                'port' =>
                    (int) config(
                        'squid.ports.forward',
                        config(
                            'squid.forward_port',
                            3128
                        )
                    ),
            ],

            'squid_http' => [
                'name' =>
                    'Squid HTTP Intercept',

                'port' =>
                    (int) config(
                        'squid.ports.http_intercept',
                        config(
                            'squid.http_intercept_port',
                            3129
                        )
                    ),
            ],

            'squid_https' => [
                'name' =>
                    'Squid HTTPS Intercept',

                'port' =>
                    (int) config(
                        'squid.ports.https_intercept',
                        config(
                            'squid.https_intercept_port',
                            3130
                        )
                    ),
            ],
        ];


        $result =
            [];


        foreach (
            $ports
            as $key => $item
        ) {

            /*
            |--------------------------------------------------------------------------
            | LOCAL tidak melakukan TCP probe.
            |--------------------------------------------------------------------------
            */

            if (
                $mode === 'local'
            ) {

                $result[$key] = [
                    'key' =>
                        $key,

                    'name' =>
                        $item[
                            'name'
                        ],

                    'status' =>
                        'not_checked',

                    'message' =>
                        'Port tidak diperiksa pada mode LOCAL.',

                    'meta' => [
                        'host' =>
                            $host,

                        'port' =>
                            $item[
                                'port'
                            ],
                    ],
                ];


                continue;
            }


            $result[$key] =
                $this->probeTcpPort(
                    $key,
                    $item[
                        'name'
                    ],
                    $host,
                    $item[
                        'port'
                    ]
                );
        }


        return $result;
    }


    /**
     * TCP probe sederhana.
     */
    private function probeTcpPort(
        string $key,
        string $name,
        string $host,
        int $port
    ): array {
        $started =
            microtime(true);


        $errno =
            0;

        $error =
            '';


        $socket =
            @fsockopen(
                $host,
                $port,
                $errno,
                $error,
                1.5
            );


        $responseMs =
            round(
                (
                    microtime(true)
                    -
                    $started
                )
                *
                1000,
                2
            );


        if (
            is_resource(
                $socket
            )
        ) {

            fclose(
                $socket
            );


            return [
                'key' =>
                    $key,

                'name' =>
                    $name,

                'status' =>
                    'online',

                'message' =>
                    "Port {$port} dapat dijangkau.",

                'response_ms' =>
                    $responseMs,

                'meta' => [
                    'host' =>
                        $host,

                    'port' =>
                        $port,
                ],
            ];
        }


        return [
            'key' =>
                $key,

            'name' =>
                $name,

            'status' =>
                'offline',

            'message' =>
                "Port {$port} tidak dapat dijangkau.",

            'response_ms' =>
                $responseMs,

            'error' =>
                trim(
                    "{$errno} {$error}"
                ),

            'meta' => [
                'host' =>
                    $host,

                'port' =>
                    $port,
            ],
        ];
    }
}