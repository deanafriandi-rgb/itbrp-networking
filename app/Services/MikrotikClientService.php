<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use RuntimeException;
use Throwable;

class MikrotikClientService
{
    public function __construct(
        private MikrotikEnvironmentGuardService $guard
    ) {
    }


    /**
     * Informasi konfigurasi MikroTik
     * tanpa melakukan koneksi.
     */
    public function status(): array
    {
        $safety =
            $this->guard->inspect();

        return [
            'mode' =>
                config(
                    'mikrotik.mode',
                    'local'
                ),

            'enabled' =>
                (bool) config(
                    'mikrotik.enabled',
                    false
                ),

            'host' =>
                config(
                    'mikrotik.host'
                ),

            'port' =>
                (int) config(
                    'mikrotik.port',
                    8729
                ),

            'use_ssl' =>
                (bool) config(
                    'mikrotik.use_ssl',
                    true
                ),

            'device_sync_enabled' =>
                (bool) config(
                    'mikrotik.device_sync.enabled',
                    false
                ),

            'can_connect' =>
                $safety['can_connect']
                ?? false,

            'safety' =>
                $safety,
        ];
    }


    /**
     * Apakah kita sedang menggunakan
     * mode local.
     */
    public function isLocal(): bool
    {
        return strtolower(
            (string) config(
                'mikrotik.mode',
                'local'
            )
        ) === 'local';
    }


    /**
     * Membuat client RouterOS.
     *
     * Method ini TIDAK akan menghubungi router
     * jika safety guard belum lolos.
     */
    public function client(): Client
    {
        /*
        |--------------------------------------------------------------------------
        | Safety Gate
        |--------------------------------------------------------------------------
        */

        $this->guard
            ->assertCanConnect();


        $host = trim(
            (string) config(
                'mikrotik.host'
            )
        );

        $username = trim(
            (string) config(
                'mikrotik.username'
            )
        );

        $password =
            (string) config(
                'mikrotik.password'
            );


        /*
        |--------------------------------------------------------------------------
        | Config RouterOS
        |--------------------------------------------------------------------------
        */

        $config = new Config([
            'host' =>
                $host,

            'user' =>
                $username,

            'pass' =>
                $password,

            'port' =>
                (int) config(
                    'mikrotik.port',
                    8729
                ),

            'ssl' =>
                (bool) config(
                    'mikrotik.use_ssl',
                    true
                ),

            'timeout' =>
                (int) config(
                    'mikrotik.timeout',
                    5
                ),

            'socket_timeout' =>
                10,

            'attempts' =>
                (int) config(
                    'mikrotik.attempts',
                    2
                ),
        ]);


        return new Client(
            $config
        );
    }


    /**
     * Jalankan RouterOS query.
     */
    public function query(
        string $endpoint
    ): array {
        /*
        |--------------------------------------------------------------------------
        | LOCAL MODE
        |--------------------------------------------------------------------------
        */

        if ($this->isLocal()) {

            return [
                'success' =>
                    true,

                'executed' =>
                    false,

                'skipped' =>
                    true,

                'endpoint' =>
                    $endpoint,

                'data' =>
                    [],

                'message' =>
                    'Query MikroTik dilewati karena masih mode local.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCTION
        |--------------------------------------------------------------------------
        */

        try {

            $client =
                $this->client();


            $query =
                new Query(
                    $endpoint
                );


            $data =
                $client
                    ->query($query)
                    ->read();


            return [
                'success' =>
                    true,

                'executed' =>
                    true,

                'skipped' =>
                    false,

                'endpoint' =>
                    $endpoint,

                'data' =>
                    is_array($data)
                        ? $data
                        : [],

                'message' =>
                    'Query MikroTik berhasil.',
            ];


        } catch (Throwable $e) {

            return [
                'success' =>
                    false,

                'executed' =>
                    false,

                'skipped' =>
                    false,

                'endpoint' =>
                    $endpoint,

                'data' =>
                    [],

                'error' =>
                    $e->getMessage(),

                'message' =>
                    'Gagal menjalankan query MikroTik.',
            ];
        }
    }


    /**
     * ARP Table.
     */
    public function arp(): array
    {
        return $this->query(
            '/ip/arp/print'
        );
    }


    /**
     * DHCP Lease.
     */
    public function dhcpLeases(): array
    {
        return $this->query(
            '/ip/dhcp-server/lease/print'
        );
    }


    /**
     * Test koneksi sederhana.
     */
    public function testConnection(): array
    {
        if ($this->isLocal()) {

            return [
                'success' =>
                    true,

                'connected' =>
                    false,

                'skipped' =>
                    true,

                'message' =>
                    'Test koneksi MikroTik dilewati karena masih mode local.',
            ];
        }


        try {

            /*
            |--------------------------------------------------------------------------
            | Identity RouterOS
            |--------------------------------------------------------------------------
            */

            $response =
                $this->query(
                    '/system/identity/print'
                );


            if (
                !(
                    $response[
                        'success'
                    ]
                    ?? false
                )
            ) {

                throw new RuntimeException(
                    $response[
                        'error'
                    ]
                    ??
                    'Router tidak merespons.'
                );
            }


            $identity =
                $response['data'][0]['name']
                ?? 'MikroTik';


            return [
                'success' =>
                    true,

                'connected' =>
                    true,

                'skipped' =>
                    false,

                'identity' =>
                    $identity,

                'message' =>
                    "Terhubung ke RouterOS {$identity}.",
            ];


        } catch (Throwable $e) {

            return [
                'success' =>
                    false,

                'connected' =>
                    false,

                'skipped' =>
                    false,

                'error' =>
                    $e->getMessage(),

                'message' =>
                    'Koneksi ke MikroTik gagal.',
            ];
        }
    }
}