<?php

namespace App\Services;

use App\Models\AccessLog;
use App\Models\LogImportState;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SquidLogImportService
{
    public function __construct(
        private SquidLogParserService $parser
    ) {}

    /**
     * Import data baru dari access.log.
     */
    public function import(
        ?string $path = null
    ): array {
        $path = $path
            ?: config('squid.access_log');

        if (!$path) {
            throw new RuntimeException(
                'Path Squid access.log belum dikonfigurasi.'
            );
        }

        if (!is_file($path)) {
            throw new RuntimeException(
                "File Squid access.log tidak ditemukan: {$path}"
            );
        }

        if (!is_readable($path)) {
            throw new RuntimeException(
                "File Squid access.log tidak dapat dibaca: {$path}"
            );
        }

        clearstatcache(true, $path);

        $stat = stat($path);

        if (!$stat) {
            throw new RuntimeException(
                'Gagal membaca informasi file access.log.'
            );
        }

        $inode = (string) ($stat['ino'] ?? '');
        $fileSize = (int) ($stat['size'] ?? 0);
        $modifiedAt = (int) ($stat['mtime'] ?? time());

        $state = LogImportState::firstOrCreate(
            [
                'log_file' => $path,
            ],
            [
                'file_inode' => $inode,
                'byte_offset' => 0,
                'file_size' => $fileSize,
                'last_status' => 'READY',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Detect log rotation
        |--------------------------------------------------------------------------
        |
        | Kalau inode berubah atau file menjadi lebih kecil dari offset terakhir,
        | kita mulai membaca file baru dari offset 0.
        |
        */

        if (
            ($state->file_inode && $state->file_inode !== $inode) ||
            $fileSize < (int) $state->byte_offset
        ) {
            $state->update([
                'file_inode' => $inode,
                'byte_offset' => 0,
                'file_size' => $fileSize,
            ]);
        }

        $handle = fopen($path, 'rb');

        if (!$handle) {
            throw new RuntimeException(
                'Gagal membuka file access.log.'
            );
        }

        $offset = (int) $state->byte_offset;

        if ($offset > 0) {
            fseek($handle, $offset);
        }

        $imported = 0;
        $ignored = 0;
        $invalid = 0;

        $batch = [];

        $batchSize = 500;

        try {
            while (!feof($handle)) {
                /*
                |--------------------------------------------------------------------------
                | Posisi AWAL baris
                |--------------------------------------------------------------------------
                */

                $lineOffset = ftell($handle);

                $line = fgets($handle);

                if ($line === false) {
                    break;
                }

                /*
                |--------------------------------------------------------------------------
                | Jangan proses baris terakhir yang belum selesai
                |--------------------------------------------------------------------------
                */

                if (
                    !str_ends_with($line, "\n") &&
                    !feof($handle)
                ) {
                    fseek($handle, $lineOffset);
                    break;
                }

                $parsed = $this->parser->parse($line);

                /*
                |--------------------------------------------------------------------------
                | Parser menghasilkan null:
                |
                | - IP Netwatch
                | - baris kosong
                | - format tidak valid
                |--------------------------------------------------------------------------
                */

                if ($parsed === null) {
                    $ignored++;
                    continue;
                }

                $parsed['source_file'] = $path;
                $parsed['source_inode'] = $inode;
                $parsed['source_offset'] = $lineOffset;

                $parsed['created_at'] = now();
                $parsed['updated_at'] = now();

                $batch[] = $parsed;

                if (count($batch) >= $batchSize) {
                    $imported += $this->insertBatch($batch);

                    $batch = [];
                }
            }

            if (!empty($batch)) {
                $imported += $this->insertBatch($batch);
            }

            /*
            |--------------------------------------------------------------------------
            | Offset TERAKHIR
            |--------------------------------------------------------------------------
            */

            $finalOffset = ftell($handle);

            $state->update([
                'file_inode' => $inode,
                'byte_offset' => $finalOffset,
                'file_size' => $fileSize,

                'file_modified_at' => date(
                    'Y-m-d H:i:s',
                    $modifiedAt
                ),

                'last_import_at' => now(),

                'last_status' => 'SUCCESS',

                'last_error' => null,
            ]);

            return [
                'file' => $path,
                'inode' => $inode,
                'imported' => $imported,
                'ignored' => $ignored,
                'invalid' => $invalid,
                'offset' => $finalOffset,
            ];
        } catch (Throwable $exception) {

            $state->update([
                'last_status' => 'FAILED',
                'last_error' => $exception->getMessage(),
                'last_import_at' => now(),
            ]);

            throw $exception;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Insert batch dengan proteksi duplicate.
     */
    private function insertBatch(
        array $rows
    ): int {
        if (empty($rows)) {
            return 0;
        }

        return DB::table('access_logs')
            ->insertOrIgnore($rows);
    }

    /**
     * Reset posisi importer.
     */
    public function reset(
        ?string $path = null
    ): void {
        $path = $path
            ?: config('squid.access_log');

        LogImportState::where(
            'log_file',
            $path
        )->delete();
    }
}
