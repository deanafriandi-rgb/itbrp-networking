<?php

namespace App\Console\Commands;

use App\Services\SquidBlacklistService;
use App\Services\SquidControlService;
use Illuminate\Console\Command;
use Throwable;

class SyncSquidBlacklist extends Command
{
    /**
     * Nama command Artisan.
     */
    protected $signature = 'squid:blacklist-sync
                            {--preview : Tampilkan blacklist tanpa menulis file}
                            {--no-reconfigure : Sinkronkan file tanpa menjalankan reconfigure Squid}';


    /**
     * Deskripsi command.
     */
    protected $description =
        'Sinkronisasi blacklist database Laravel ke file blacklist Squid dan terapkan konfigurasi secara aman';


    /**
     * Jalankan command.
     */
    public function handle(
        SquidBlacklistService $blacklist,
        SquidControlService $control
    ): int {
        $this->newLine();

        $this->components->info(
            'SQUID BLACKLIST SYNCHRONIZATION'
        );

        $this->newLine();


        /*
        |--------------------------------------------------------------------------
        | Informasi lingkungan
        |--------------------------------------------------------------------------
        */

        $status = $control->status();

        $this->table(
            [
                'Konfigurasi',
                'Nilai',
            ],
            [
                [
                    'Mode',
                    strtoupper(
                        (string) $status['mode']
                    ),
                ],

                [
                    'Squid Host',
                    (string) $status['host'],
                ],

                [
                    'Blacklist File',
                    (string) $status[
                        'blacklist_file'
                    ],
                ],

                [
                    'Reconfigure',
                    $status[
                        'reconfigure_enabled'
                    ]
                        ? 'ENABLED'
                        : 'DISABLED',
                ],
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | PREVIEW
        |--------------------------------------------------------------------------
        */

        if ($this->option('preview')) {

            return $this->handlePreview(
                $blacklist
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SYNCHRONIZE FILE
        |--------------------------------------------------------------------------
        */

        try {

            $this->newLine();

            $this->components->task(
                'Sinkronisasi database ke blacklist.txt',
                function () use (
                    $blacklist,
                    &$syncResult
                ) {

                    $syncResult =
                        $blacklist->sync();

                    return true;
                }
            );

        } catch (Throwable $e) {

            $this->newLine();

            $this->components->error(
                'Sinkronisasi blacklist gagal.'
            );

            $this->line(
                $e->getMessage()
            );

            return self::FAILURE;
        }


        /*
        |--------------------------------------------------------------------------
        | HASIL FILE
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->table(
            [
                'Hasil Sinkronisasi',
                'Nilai',
            ],
            [
                [
                    'Domain Aktif',
                    $syncResult[
                        'domain_count'
                    ] ?? 0,
                ],

                [
                    'Baris Blacklist',
                    $syncResult[
                        'line_count'
                    ] ?? 0,
                ],

                [
                    'Bytes Ditulis',
                    $syncResult[
                        'bytes_written'
                    ] ?? 0,
                ],

                [
                    'File',
                    $syncResult[
                        'path'
                    ] ?? '-',
                ],
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | --no-reconfigure
        |--------------------------------------------------------------------------
        */

        if (
            $this->option(
                'no-reconfigure'
            )
        ) {

            $this->newLine();

            $this->components->warn(
                'File blacklist berhasil disinkronkan, tetapi proses apply Squid dilewati karena opsi --no-reconfigure digunakan.'
            );

            return self::SUCCESS;
        }


        /*
        |--------------------------------------------------------------------------
        | APPLY SQUID
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->components->info(
            'Validasi dan penerapan konfigurasi Squid...'
        );


        try {

            $applyResult =
                $control->apply();

        } catch (Throwable $e) {

            $this->newLine();

            $this->components->error(
                'Terjadi error saat menerapkan konfigurasi Squid.'
            );

            $this->line(
                $e->getMessage()
            );

            return self::FAILURE;
        }


        /*
        |--------------------------------------------------------------------------
        | LOCAL MODE / SKIPPED
        |--------------------------------------------------------------------------
        */

        if (
            $applyResult[
                'skipped'
            ] ?? false
        ) {

            $this->newLine();

            $this->components->warn(
                $applyResult[
                    'message'
                ]
                ??
                'Penerapan Squid dilewati.'
            );


            $this->newLine();

            $this->components->info(
                'Blacklist berhasil disinkronkan.'
            );


            return self::SUCCESS;
        }


        /*
        |--------------------------------------------------------------------------
        | APPLY FAILED
        |--------------------------------------------------------------------------
        */

        if (
            !(
                $applyResult[
                    'success'
                ]
                ?? false
            )
        ) {

            $this->newLine();

            $this->components->error(
                $applyResult[
                    'message'
                ]
                ??
                'Penerapan konfigurasi Squid gagal.'
            );


            /*
            |--------------------------------------------------------------------------
            | Error Output
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $applyResult[
                        'error_output'
                    ]
                )
            ) {

                $this->newLine();

                $this->line(
                    '<fg=red>' .
                    $applyResult[
                        'error_output'
                    ] .
                    '</>'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Parse Result
            |--------------------------------------------------------------------------
            */

            if (
                isset(
                    $applyResult[
                        'parse_result'
                    ]
                )
            ) {

                $parse =
                    $applyResult[
                        'parse_result'
                    ];

                $this->newLine();

                $this->table(
                    [
                        'Validasi Squid',
                        'Nilai',
                    ],
                    [
                        [
                            'Success',
                            (
                                $parse[
                                    'success'
                                ]
                                ?? false
                            )
                                ? 'YES'
                                : 'NO',
                        ],

                        [
                            'Exit Code',
                            $parse[
                                'exit_code'
                            ]
                                ?? '-',
                        ],

                        [
                            'Command',
                            $parse[
                                'command'
                            ]
                                ?? '-',
                        ],
                    ]
                );
            }


            return self::FAILURE;
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCTION SUCCESS
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->components->success(
            'Blacklist berhasil disinkronkan dan konfigurasi Squid berhasil diterapkan.'
        );


        $this->newLine();

        $this->table(
            [
                'Squid',
                'Nilai',
            ],
            [
                [
                    'Mode',
                    strtoupper(
                        (string) (
                            $applyResult[
                                'mode'
                            ]
                            ?? '-'
                        )
                    ),
                ],

                [
                    'Action',
                    $applyResult[
                        'action'
                    ]
                        ?? '-',
                ],

                [
                    'Command',
                    $applyResult[
                        'command'
                    ]
                        ?? '-',
                ],

                [
                    'Exit Code',
                    $applyResult[
                        'exit_code'
                    ]
                        ?? '-',
                ],
            ]
        );


        return self::SUCCESS;
    }


    /**
     * Preview blacklist tanpa melakukan perubahan.
     */
    private function handlePreview(
        SquidBlacklistService $blacklist
    ): int {
        try {

            $preview =
                $blacklist->preview();

        } catch (Throwable $e) {

            $this->components->error(
                'Preview blacklist gagal.'
            );

            $this->line(
                $e->getMessage()
            );

            return self::FAILURE;
        }


        $this->newLine();

        $this->components->info(
            'BLACKLIST PREVIEW'
        );


        $this->table(
            [
                'Informasi',
                'Nilai',
            ],
            [
                [
                    'Mode',
                    strtoupper(
                        (string) (
                            $preview[
                                'mode'
                            ]
                            ?? '-'
                        )
                    ),
                ],

                [
                    'Domain Aktif',
                    $preview[
                        'domain_count'
                    ]
                        ?? 0,
                ],

                [
                    'Total Baris',
                    $preview[
                        'line_count'
                    ]
                        ?? 0,
                ],

                [
                    'Target File',
                    $preview[
                        'path'
                    ]
                        ?? '-',
                ],
            ]
        );


        $this->newLine();


        if (
            empty(
                $preview['lines']
            )
        ) {

            $this->components->warn(
                'Belum ada domain blacklist aktif.'
            );

            return self::SUCCESS;
        }


        foreach (
            $preview['lines']
            as $line
        ) {

            $this->line(
                '  • ' . $line
            );
        }


        $this->newLine();

        $this->components->info(
            'Preview selesai. Tidak ada file yang diubah.'
        );


        return self::SUCCESS;
    }
}