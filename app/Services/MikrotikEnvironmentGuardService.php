<?php

namespace App\Services;

use RuntimeException;

class MikrotikEnvironmentGuardService
{
    /**
     * Periksa environment integrasi MikroTik.
     */
    public function inspect(): array
    {
        $mode = strtolower(
            trim(
                (string) config(
                    'mikrotik.mode',
                    'local'
                )
            )
        );

        $enabled = (bool) config(
            'mikrotik.enabled',
            false
        );

        $host = trim(
            (string) config(
                'mikrotik.host',
                ''
            )
        );

        $port = (int) config(
            'mikrotik.port',
            8729
        );

        $useSsl = (bool) config(
            'mikrotik.use_ssl',
            true
        );

        $username = trim(
            (string) config(
                'mikrotik.username',
                ''
            )
        );

        $password = (string) config(
            'mikrotik.password',
            ''
        );

        $allowRemote =
            (bool) config(
                'mikrotik.safety.allow_remote_connection',
                false
            );

        $requireUsername =
            (bool) config(
                'mikrotik.safety.require_username',
                true
            );

        $requirePassword =
            (bool) config(
                'mikrotik.safety.require_password',
                true
            );

        $requireSsl =
            (bool) config(
                'mikrotik.safety.require_ssl',
                true
            );


        /*
        |--------------------------------------------------------------------------
        | Valid Mode
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
                'can_connect' => false,

                'mode' => $mode,

                'enabled' => $enabled,

                'host' => $host,

                'port' => $port,

                'use_ssl' => $useSsl,

                'issues' => [
                    "MIKROTIK_MODE tidak valid: {$mode}",
                ],

                'message' =>
                'Environment MikroTik tidak valid.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | LOCAL MODE
        |--------------------------------------------------------------------------
        */

        if ($mode === 'local') {

            return [
                'safe' => true,

                'environment_valid' =>
                true,

                'can_connect' =>
                false,

                'mode' =>
                'local',

                'enabled' =>
                false,

                'host' =>
                $host,

                'port' =>
                $port,

                'use_ssl' =>
                $useSsl,

                'device_sync_enabled' =>
                false,

                'issues' =>
                [],

                'message' =>
                'Mode local aman. Laravel tidak akan menghubungi MikroTik.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCTION CHECK
        |--------------------------------------------------------------------------
        */

        $issues = [];


        if (!$enabled) {

            $issues[] =
                'MIKROTIK_ENABLED masih false.';
        }


        if (!$allowRemote) {

            $issues[] =
                'MIKROTIK_ALLOW_REMOTE_CONNECTION masih false.';
        }


        /*
        |--------------------------------------------------------------------------
        | Host
        |--------------------------------------------------------------------------
        */

        if ($host === '') {

            $issues[] =
                'MIKROTIK_HOST belum dikonfigurasi.';
        } elseif (
            filter_var(
                $host,
                FILTER_VALIDATE_IP
            ) === false
            &&
            filter_var(
                $host,
                FILTER_VALIDATE_DOMAIN,
                FILTER_FLAG_HOSTNAME
            ) === false
        ) {

            $issues[] =
                'MIKROTIK_HOST tidak valid.';
        }


        /*
        |--------------------------------------------------------------------------
        | Port
        |--------------------------------------------------------------------------
        */

        if (
            $port < 1 ||
            $port > 65535
        ) {

            $issues[] =
                'MIKROTIK_PORT tidak valid.';
        }


        /*
        |--------------------------------------------------------------------------
        | SSL
        |--------------------------------------------------------------------------
        */

        if (
            $requireSsl &&
            !$useSsl
        ) {

            $issues[] =
                'Production MikroTik diwajibkan menggunakan API SSL.';
        }


        /*
        |--------------------------------------------------------------------------
        | Username
        |--------------------------------------------------------------------------
        */

        if (
            $requireUsername &&
            $username === ''
        ) {

            $issues[] =
                'MIKROTIK_USERNAME belum dikonfigurasi.';
        }


        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        */

        if (
            $requirePassword &&
            trim($password) === ''
        ) {

            $issues[] =
                'MIKROTIK_PASSWORD belum dikonfigurasi.';
        }


        /*
        |--------------------------------------------------------------------------
        | Final
        |--------------------------------------------------------------------------
        */

        $valid =
            empty($issues);


        return [
            'safe' =>
            $valid,

            'environment_valid' =>
            $valid,

            'can_connect' =>
            $valid,

            'mode' =>
            'production',

            'enabled' =>
            $enabled,

            'host' =>
            $host,

            'port' =>
            $port,

            'use_ssl' =>
            $useSsl,

            'device_sync_enabled' =>
            (bool) config(
                'mikrotik.device_sync.enabled',
                false
            ),

            'issues' =>
            $issues,

            'message' =>
            $valid

                ? 'Environment MikroTik production valid.'

                : 'Environment MikroTik belum aman untuk melakukan koneksi.',
        ];
    }


    /**
     * Laravel boleh konek RouterOS?
     */
    public function canConnect(): bool
    {
        $result =
            $this->inspect();

        return (bool) (
            $result['can_connect']
            ?? false
        );
    }


    /**
     * Hentikan koneksi jika environment
     * belum aman.
     */
    public function assertCanConnect(): void
    {
        $result =
            $this->inspect();


        if (
            !(
                $result['can_connect']
                ?? false
            )
        ) {

            $issues =
                $result['issues']
                ?? [];


            $message =
                $result['message']
                ??
                'Koneksi MikroTik tidak diizinkan.';


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
