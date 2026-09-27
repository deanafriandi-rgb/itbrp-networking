<?php

namespace App\Console\Commands;

use App\Services\MikrotikDeviceService;
use Illuminate\Console\Command;
use Throwable;

class SyncMikrotikDevices extends Command
{
    /**
     * Command.
     */
    protected $signature =
        'mikrotik:devices-sync';


    /**
     * Description.
     */
    protected $description =
        'Sinkronisasi perangkat dari ARP dan DHCP MikroTik ke database Laravel';


    /**
     * Execute.
     */
    public function handle(
        MikrotikDeviceService $devices
    ): int {
        $this->newLine();

        $this->components->info(
            'MIKROTIK DEVICE SYNCHRONIZATION'
        );

        $this->newLine();


        try {

            $result =
                $devices
                    ->syncToDatabase();


        } catch (Throwable $e) {

            $this->components->error(
                'Sinkronisasi perangkat gagal.'
            );

            $this->line(
                $e->getMessage()
            );

            return self::FAILURE;
        }


        /*
        |--------------------------------------------------------------------------
        | LOCAL MODE
        |--------------------------------------------------------------------------
        */

        if (
            $result['skipped']
            ?? false
        ) {

            $this->components->warn(
                $result['message']
                ??
                'Sinkronisasi dilewati.'
            );


            $this->newLine();


            $this->table(
                [
                    'Informasi',
                    'Nilai',
                ],
                [
                    [
                        'Mode',
                        strtoupper(
                            (string) config(
                                'mikrotik.mode',
                                'local'
                            )
                        ),
                    ],

                    [
                        'Router',
                        (string) config(
                            'mikrotik.host',
                            '-'
                        ),
                    ],

                    [
                        'Status',
                        'SKIPPED',
                    ],
                ]
            );


            return self::SUCCESS;
        }


        /*
        |--------------------------------------------------------------------------
        | FAILED
        |--------------------------------------------------------------------------
        */

        if (
            !(
                $result['success']
                ?? false
            )
        ) {

            $this->components->error(
                $result['message']
                ??
                'Sinkronisasi perangkat gagal.'
            );


            if (
                !empty(
                    $result['error']
                )
            ) {

                $this->newLine();

                $this->line(
                    '<fg=red>'
                    .
                    $result['error']
                    .
                    '</>'
                );
            }


            return self::FAILURE;
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        $this->components->success(
            'Sinkronisasi perangkat selesai.'
        );


        $this->newLine();


        $this->table(
            [
                'Hasil',
                'Jumlah',
            ],
            [
                [
                    'ARP',
                    $result[
                        'arp_count'
                    ]
                    ?? 0,
                ],

                [
                    'DHCP Lease',
                    $result[
                        'dhcp_count'
                    ]
                    ?? 0,
                ],

                [
                    'Perangkat Ditemukan',
                    $result[
                        'discovered'
                    ]
                    ?? 0,
                ],

                [
                    'Perangkat Baru',
                    $result[
                        'inserted'
                    ]
                    ?? 0,
                ],

                [
                    'Perangkat Diperbarui',
                    $result[
                        'updated'
                    ]
                    ?? 0,
                ],

                [
                    'Menjadi Offline',
                    $result[
                        'marked_offline'
                    ]
                    ?? 0,
                ],

                [
                    'Online Saat Ini',
                    $result[
                        'online_total'
                    ]
                    ?? 0,
                ],

                [
                    'Total Database',
                    $result[
                        'total_devices'
                    ]
                    ?? 0,
                ],
            ]
        );


        return self::SUCCESS;
    }
}