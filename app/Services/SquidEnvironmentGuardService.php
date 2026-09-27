<?php

namespace App\Services;

use RuntimeException;

class SquidEnvironmentGuardService
{
    /**
     * Pemeriksaan environment Squid.
     */
    public function inspect(): array
    {
        $mode = strtolower(
            trim(
                (string) config(
                    'squid.mode',
                    'local'
                )
            )
        );

        $binary = trim(
            (string) config(
                'squid.commands.binary',
                'squid'
            )
        );

        $accessLog = trim(
            (string) config(
                'squid.access_log',
                ''
            )
        );

        $blacklistFile = trim(
            (string) config(
                'squid.blacklist_file',
                ''
            )
        );

        $allowProductionCommands =
            (bool) config(
                'squid.safety.allow_production_commands',
                false
            );

        $requireLinux =
            (bool) config(
                'squid.safety.require_linux',
                true
            );

        $requireAbsoluteBinary =
            (bool) config(
                'squid.safety.require_absolute_binary',
                true
            );

        $requireExecutableBinary =
            (bool) config(
                'squid.safety.require_executable_binary',
                true
            );


        /*
        |--------------------------------------------------------------------------
        | Mode valid?
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $mode,
                [
                    'local',
                    'production',
                ],
                true
            )
        ) {

            return [
                'safe' => false,
                'environment_valid' => false,
                'can_execute' => false,

                'mode' => $mode,

                'os_family' =>
                PHP_OS_FAMILY,

                'binary' =>
                $binary,

                'issues' => [
                    "SQUID_MODE tidak valid: {$mode}",
                ],

                'message' =>
                'Environment Squid tidak valid.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | LOCAL MODE
        |--------------------------------------------------------------------------
        |
        | Local dianggap environment yang valid,
        | tetapi TIDAK diperbolehkan mengeksekusi
        | command Squid production.
        |
        */

        if ($mode === 'local') {

            return [
                'safe' => true,

                'environment_valid' =>
                true,

                'can_execute' =>
                false,

                'mode' =>
                'local',

                'os_family' =>
                PHP_OS_FAMILY,

                'binary' =>
                $binary,

                'access_log' =>
                $accessLog,

                'blacklist_file' =>
                $blacklistFile,

                'allow_production_commands' =>
                $allowProductionCommands,

                'issues' => [],

                'message' =>
                'Mode local aman. Command Squid production tidak akan dijalankan.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCTION VALIDATION
        |--------------------------------------------------------------------------
        */

        $issues = [];


        /*
        |--------------------------------------------------------------------------
        | Linux Check
        |--------------------------------------------------------------------------
        */

        if (
            $requireLinux &&
            PHP_OS_FAMILY !== 'Linux'
        ) {

            $issues[] =
                'Mode production hanya boleh menjalankan command Squid pada Linux.';
        }


        /*
        |--------------------------------------------------------------------------
        | Explicit Permission
        |--------------------------------------------------------------------------
        */

        if (!$allowProductionCommands) {

            $issues[] =
                'SQUID_ALLOW_PRODUCTION_COMMANDS masih false.';
        }


        /*
        |--------------------------------------------------------------------------
        | Binary
        |--------------------------------------------------------------------------
        */

        if ($binary === '') {

            $issues[] =
                'Binary Squid belum dikonfigurasi.';
        } else {

            if (
                $requireAbsoluteBinary &&
                !str_starts_with(
                    $binary,
                    '/'
                )
            ) {

                $issues[] =
                    'Binary Squid production harus menggunakan absolute path Linux, contoh /usr/sbin/squid.';
            }


            if (
                $requireExecutableBinary &&
                str_starts_with(
                    $binary,
                    '/'
                )
            ) {

                if (!is_file($binary)) {

                    $issues[] =
                        "Binary Squid tidak ditemukan: {$binary}";
                } elseif (!is_executable($binary)) {

                    $issues[] =
                        "Binary Squid tidak executable: {$binary}";
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Production Path Check
        |--------------------------------------------------------------------------
        */

        if (
            $accessLog === '' ||
            !str_starts_with(
                $accessLog,
                '/'
            )
        ) {

            $issues[] =
                'SQUID_ACCESS_LOG production harus menggunakan absolute path Linux.';
        }


        if (
            $blacklistFile === '' ||
            !str_starts_with(
                $blacklistFile,
                '/'
            )
        ) {

            $issues[] =
                'SQUID_BLACKLIST_FILE production harus menggunakan absolute path Linux.';
        }


        /*
        |--------------------------------------------------------------------------
        | Final Result
        |--------------------------------------------------------------------------
        */

        $valid =
            empty($issues);


        return [
            'safe' =>
            $valid,

            'environment_valid' =>
            $valid,

            'can_execute' =>
            $valid,

            'mode' =>
            'production',

            'os_family' =>
            PHP_OS_FAMILY,

            'binary' =>
            $binary,

            'access_log' =>
            $accessLog,

            'blacklist_file' =>
            $blacklistFile,

            'allow_production_commands' =>
            $allowProductionCommands,

            'issues' =>
            $issues,

            'message' =>
            $valid

                ? 'Environment production Squid valid.'

                : 'Environment production Squid belum aman untuk menjalankan command.',
        ];
    }


    /**
     * Apakah command Squid boleh dijalankan.
     */
    public function canExecute(): bool
    {
        $result =
            $this->inspect();

        return (bool) (
            $result['can_execute']
            ?? false
        );
    }


    /**
     * Menghentikan proses jika environment
     * production tidak aman.
     */
    public function assertCanExecute(): void
    {
        $result =
            $this->inspect();


        if (
            !(
                $result['can_execute']
                ?? false
            )
        ) {

            $issues =
                $result['issues']
                ?? [];


            $message =
                $result['message']
                ??
                'Environment Squid tidak aman.';


            if (!empty($issues)) {

                $message .=
                    ' ' .
                    implode(
                        ' ',
                        $issues
                    );
            }


            throw new RuntimeException(
                $message
            );
        }
    }
}
