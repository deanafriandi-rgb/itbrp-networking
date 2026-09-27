<?php

namespace App\Services;

use App\Models\BlacklistDomain;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;

class SquidBlacklistService
{
    /**
     * Mengambil lokasi blacklist dari config/squid.php.
     */
    public function getBlacklistPath(): string
    {
        $path = (string) config(
            'squid.blacklist_file'
        );

        if (trim($path) === '') {
            throw new RuntimeException(
                'Path blacklist Squid belum dikonfigurasi.'
            );
        }

        return $path;
    }


    /**
     * Mengambil domain blacklist aktif dari database,
     * kemudian mengubahnya menjadi format file Squid.
     */
    public function buildBlacklist(): array
    {
        $domains = BlacklistDomain::query()
            ->where('is_active', true)
            ->orderBy('domain')
            ->get();

        $lines = [];
        $invalidDomains = [];

        foreach ($domains as $item) {

            $domain = $this->normalizeDomain(
                $item->domain
            );

            if ($domain === null) {

                $invalidDomains[] =
                    (string) $item->domain;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Domain utama
            |--------------------------------------------------------------------------
            |
            | Contoh:
            | facebook.com
            |
            */

            $lines[] = $domain;


            /*
            |--------------------------------------------------------------------------
            | Subdomain
            |--------------------------------------------------------------------------
            |
            | Jika include_subdomains = true:
            |
            | facebook.com
            | .facebook.com
            |
            | Dengan begitu subdomain seperti:
            | www.facebook.com
            | m.facebook.com
            |
            | ikut masuk blacklist.
            |
            */

            if ($item->include_subdomains) {
                $lines[] = '.' . $domain;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Jangan sinkron jika ada domain tidak valid
        |--------------------------------------------------------------------------
        */

        if (!empty($invalidDomains)) {

            throw new RuntimeException(
                'Domain blacklist tidak valid: ' .
                    implode(', ', $invalidDomains)
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Hilangkan duplikasi
        |--------------------------------------------------------------------------
        */

        $lines = array_values(
            array_unique($lines)
        );


        /*
        |--------------------------------------------------------------------------
        | Urutkan agar blacklist.txt rapi
        |--------------------------------------------------------------------------
        */

        sort(
            $lines,
            SORT_NATURAL |
                SORT_FLAG_CASE
        );


        /*
        |--------------------------------------------------------------------------
        | Bentuk content file
        |--------------------------------------------------------------------------
        */

        $content = '';

        if (!empty($lines)) {
            $content =
                implode(PHP_EOL, $lines)
                . PHP_EOL;
        }


        return [
            'domains' => $domains,

            'lines' => $lines,

            'content' => $content,

            'domain_count' =>
            $domains->count(),

            'line_count' =>
            count($lines),
        ];
    }


    /**
     * Preview blacklist tanpa menulis file.
     *
     * Cocok untuk testing.
     */
    public function preview(): array
    {
        $blacklist =
            $this->buildBlacklist();

        return [
            'mode' =>
            config('squid.mode'),

            'path' =>
            $this->getBlacklistPath(),

            'domain_count' =>
            $blacklist['domain_count'],

            'line_count' =>
            $blacklist['line_count'],

            'lines' =>
            $blacklist['lines'],

            'content' =>
            $blacklist['content'],
        ];
    }


    /**
     * Sinkronisasi database blacklist_domains
     * ke file blacklist Squid.
     *
     * LOCAL:
     * storage/app/squid/blacklist.txt
     *
     * PRODUCTION:
     * /etc/squid/blacklist.txt
     */
    public function sync(): array
    {
        try {

            $blacklist =
                $this->buildBlacklist();

            $path =
                $this->getBlacklistPath();

            $directory =
                dirname($path);


            /*
            |--------------------------------------------------------------------------
            | Pastikan directory tersedia
            |--------------------------------------------------------------------------
            */

            if (!File::isDirectory($directory)) {

                /*
                |--------------------------------------------------------------------------
                | Local boleh membuat directory otomatis
                |--------------------------------------------------------------------------
                */

                if (
                    config('squid.mode')
                    === 'local'
                ) {

                    File::ensureDirectoryExists(
                        $directory
                    );
                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Production tidak membuat /etc/squid secara otomatis.
                    |--------------------------------------------------------------------------
                    |
                    | Folder sistem harus memang sudah tersedia.
                    |
                    */

                    throw new RuntimeException(
                        "Directory blacklist tidak ditemukan: {$directory}"
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Pastikan directory dapat ditulis
            |--------------------------------------------------------------------------
            */

            if (!is_writable($directory)) {

                throw new RuntimeException(
                    "Directory blacklist tidak dapat ditulis: {$directory}"
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Tulis blacklist dengan lock
            |--------------------------------------------------------------------------
            */

            $result = file_put_contents(
                $path,
                $blacklist['content'],
                LOCK_EX
            );


            if ($result === false) {

                throw new RuntimeException(
                    "Gagal menulis blacklist ke: {$path}"
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Tandai seluruh record sudah sinkron
            |--------------------------------------------------------------------------
            |
            | Record nonaktif juga dianggap tersinkron,
            | karena tidak lagi dimasukkan ke blacklist.txt.
            |
            */

            BlacklistDomain::query()
                ->update([
                    'sync_status' => 'synced',
                    'synced_at' => now(),
                ]);


            return [
                'success' => true,

                'mode' =>
                config('squid.mode'),

                'path' =>
                $path,

                'domain_count' =>
                $blacklist['domain_count'],

                'line_count' =>
                $blacklist['line_count'],

                'bytes_written' =>
                (int) $result,

                'synced_at' =>
                now(),
            ];
        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Tandai error sinkronisasi
            |--------------------------------------------------------------------------
            */

            try {

                BlacklistDomain::query()
                    ->update([
                        'sync_status' => 'error',
                    ]);
            } catch (Throwable) {
                // Jangan menutupi exception utama.
            }


            throw $e;
        }
    }


    /**
     * Membaca blacklist.txt yang sedang aktif.
     */
    public function readCurrentFile(): array
    {
        $path =
            $this->getBlacklistPath();

        if (!File::exists($path)) {

            return [
                'exists' => false,
                'path' => $path,
                'lines' => [],
                'content' => '',
            ];
        }

        $content =
            File::get($path);

        $lines = preg_split(
            '/\r\n|\r|\n/',
            $content
        );

        $lines = collect($lines)
            ->map(
                fn($line) =>
                trim($line)
            )
            ->filter(
                fn($line) =>
                $line !== ''
            )
            ->values()
            ->all();


        return [
            'exists' => true,

            'path' => $path,

            'lines' => $lines,

            'content' => $content,
        ];
    }


    /**
     * Normalisasi domain.
     *
     * Input yang masih seperti:
     *
     * https://facebook.com/
     * www.facebook.com/path
     * *.facebook.com
     * .facebook.com
     *
     * akan dibersihkan menjadi hostname.
     */
    private function normalizeDomain(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $domain = strtolower(
            trim($value)
        );


        if ($domain === '') {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Hilangkan wildcard depan
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $domain,
                '*.'
            )
        ) {
            $domain =
                substr($domain, 2);
        }


        /*
        |--------------------------------------------------------------------------
        | Hilangkan titik depan
        |--------------------------------------------------------------------------
        */

        $domain =
            ltrim($domain, '.');


        /*
        |--------------------------------------------------------------------------
        | Jika input URL, ambil hostname
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $domain,
                'http://'
            )
            ||
            str_starts_with(
                $domain,
                'https://'
            )
        ) {

            $host =
                parse_url(
                    $domain,
                    PHP_URL_HOST
                );

            if ($host === null) {
                return null;
            }

            $domain =
                strtolower(
                    trim($host)
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Hapus path jika user memasukkan domain/path
        |--------------------------------------------------------------------------
        */

        if (str_contains($domain, '/')) {

            $domain =
                explode(
                    '/',
                    $domain,
                    2
                )[0];
        }


        /*
        |--------------------------------------------------------------------------
        | Hapus port
        |--------------------------------------------------------------------------
        |
        | example.com:443
        |
        */

        $domain = preg_replace(
            '/:\d+$/',
            '',
            $domain
        );


        $domain =
            trim(
                (string) $domain,
                ". \t\n\r\0\x0B"
            );


        if ($domain === '') {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Validasi hostname
        |--------------------------------------------------------------------------
        */

        if (
            filter_var(
                $domain,
                FILTER_VALIDATE_DOMAIN,
                FILTER_FLAG_HOSTNAME
            ) === false
        ) {
            return null;
        }


        return $domain;
    }
}
