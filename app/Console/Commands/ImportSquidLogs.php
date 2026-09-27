<?php

namespace App\Console\Commands;

use App\Services\SquidLogImportService;
use Illuminate\Console\Command;
use Throwable;

class ImportSquidLogs extends Command
{
    /**
     * Contoh:
     *
     * php artisan squid:import
     * php artisan squid:import --reset
     */
    protected $signature = 'squid:import
                            {--reset : Reset posisi importer sebelum membaca log}';

    protected $description =
    'Import Squid access.log ke database Laravel';

    public function handle(
        SquidLogImportService $importer
    ): int {
        try {

            if ($this->option('reset')) {
                $this->warn(
                    'Reset posisi importer...'
                );

                $importer->reset();
            }

            $this->info(
                'Membaca Squid access.log...'
            );

            $result = $importer->import();

            $this->newLine();

            $this->table(
                [
                    'Item',
                    'Hasil',
                ],
                [
                    [
                        'File',
                        $result['file'],
                    ],
                    [
                        'Inode',
                        $result['inode'],
                    ],
                    [
                        'Imported',
                        number_format(
                            $result['imported']
                        ),
                    ],
                    [
                        'Ignored',
                        number_format(
                            $result['ignored']
                        ),
                    ],
                    [
                        'Offset',
                        number_format(
                            $result['offset']
                        ),
                    ],
                ]
            );

            $this->newLine();

            $this->info(
                'Import Squid selesai.'
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {

            $this->error(
                $exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}
