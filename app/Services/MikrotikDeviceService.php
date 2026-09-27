<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MikrotikDeviceService
{
    public function __construct(
        private MikrotikClientService $mikrotik
    ) {}


    /**
     * Ambil dan normalisasi perangkat
     * dari ARP + DHCP Lease.
     */
    public function collectDevices(): array
    {
        /*
        |--------------------------------------------------------------------------
        | LOCAL MODE
        |--------------------------------------------------------------------------
        */

        if ($this->mikrotik->isLocal()) {

            return [
                'success' =>
                true,

                'executed' =>
                false,

                'skipped' =>
                true,

                'arp_count' =>
                0,

                'dhcp_count' =>
                0,

                'device_count' =>
                0,

                'devices' =>
                collect(),

                'message' =>
                'Pengambilan perangkat dilewati karena MikroTik masih mode local.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | ARP
        |--------------------------------------------------------------------------
        */

        $arpResult =
            $this->mikrotik
            ->arp();


        /*
        |--------------------------------------------------------------------------
        | DHCP
        |--------------------------------------------------------------------------
        */

        $dhcpResult =
            $this->mikrotik
            ->dhcpLeases();


        if (
            !(
                $arpResult['success']
                ?? false
            )
        ) {

            return [
                'success' =>
                false,

                'executed' =>
                false,

                'skipped' =>
                false,

                'devices' =>
                collect(),

                'message' =>
                'Gagal mengambil ARP MikroTik.',

                'error' =>
                $arpResult['error']
                    ?? null,
            ];
        }


        if (
            !(
                $dhcpResult['success']
                ?? false
            )
        ) {

            return [
                'success' =>
                false,

                'executed' =>
                false,

                'skipped' =>
                false,

                'devices' =>
                collect(),

                'message' =>
                'Gagal mengambil DHCP Lease MikroTik.',

                'error' =>
                $dhcpResult['error']
                    ?? null,
            ];
        }


        $arpRows =
            collect(
                $arpResult['data']
                    ?? []
            );


        $dhcpRows =
            collect(
                $dhcpResult['data']
                    ?? []
            );


        /*
        |--------------------------------------------------------------------------
        | Device Map
        |--------------------------------------------------------------------------
        |
        | MAC address menjadi key utama.
        |
        */

        $devices = [];


        /*
        |--------------------------------------------------------------------------
        | ARP
        |--------------------------------------------------------------------------
        */

        foreach ($arpRows as $row) {

            $mac =
                $this->normalizeMac(
                    $row['mac-address']
                        ?? null
                );


            if ($mac === null) {
                continue;
            }


            $ip =
                $this->normalizeIp(
                    $row['address']
                        ?? null
                );


            $devices[$mac] = [

                'mac_address' =>
                $mac,

                'ip_address' =>
                $ip,

                'hostname' =>
                null,

                'interface' =>
                $row['interface']
                    ?? null,

                'segment' =>
                $this->resolveSegment(
                    $ip
                ),

                'dhcp_server' =>
                null,

                'lease_status' =>
                null,

                'source' =>
                'mikrotik',

                /*
                |--------------------------------------------------------------------------
                | Jika MAC ada di ARP table,
                | kita anggap terlihat oleh router.
                |--------------------------------------------------------------------------
                */

                'is_online' =>
                true,

                'first_seen_at' =>
                now(),

                'last_seen_at' =>
                now(),

                'raw_arp' =>
                $row,

                'raw_dhcp' =>
                null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | DHCP Lease
        |--------------------------------------------------------------------------
        */

        foreach ($dhcpRows as $row) {

            $mac =
                $this->normalizeMac(
                    $row['mac-address']
                        ??
                        $row['active-mac-address']
                        ??
                        null
                );


            if ($mac === null) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Prefer active address.
            |--------------------------------------------------------------------------
            */

            $ip =
                $this->normalizeIp(
                    $row['active-address']
                        ??
                        $row['address']
                        ??
                        null
                );


            $hostname =
                $this->cleanString(
                    $row['host-name']
                        ?? null
                );


            $leaseStatus =
                strtolower(
                    trim(
                        (string) (
                            $row['status']
                            ?? ''
                        )
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | DHCP bound berarti lease aktif.
            |--------------------------------------------------------------------------
            */

            $leaseOnline =
                $leaseStatus === 'bound';


            /*
            |--------------------------------------------------------------------------
            | Kalau MAC belum ada dari ARP.
            |--------------------------------------------------------------------------
            */

            if (
                !isset(
                    $devices[$mac]
                )
            ) {

                $devices[$mac] = [

                    'mac_address' =>
                    $mac,

                    'ip_address' =>
                    $ip,

                    'hostname' =>
                    $hostname,

                    'interface' =>
                    null,

                    'segment' =>
                    $this->resolveSegment(
                        $ip
                    ),

                    'dhcp_server' =>
                    $row['server']
                        ?? null,

                    'lease_status' =>
                    $leaseStatus
                        ?: null,

                    'source' =>
                    'mikrotik',

                    'is_online' =>
                    $leaseOnline,

                    'first_seen_at' =>
                    now(),

                    'last_seen_at' =>
                    $leaseOnline
                        ? now()
                        : null,

                    'raw_arp' =>
                    null,

                    'raw_dhcp' =>
                    $row,
                ];


                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Merge DHCP ke ARP
            |--------------------------------------------------------------------------
            */

            $device =
                $devices[$mac];


            /*
            |--------------------------------------------------------------------------
            | DHCP active-address lebih diprioritaskan.
            |--------------------------------------------------------------------------
            */

            if ($ip !== null) {
                $device['ip_address'] = $ip;
            }


            if ($hostname !== null) {
                $device['hostname'] = $hostname;
            }


            $device['dhcp_server'] =
                $row['server']
                ?? null;


            $device['lease_status'] =
                $leaseStatus
                ?: null;


            /*
            |--------------------------------------------------------------------------
            | ARP atau DHCP bound = online.
            |--------------------------------------------------------------------------
            */

            $device['is_online'] =
                (
                    $device['is_online']
                    ?? false
                )
                ||
                $leaseOnline;


            if (
                $device['is_online']
            ) {

                $device['last_seen_at'] = now();
            }


            /*
            |--------------------------------------------------------------------------
            | Re-resolve segment kalau IP berubah.
            |--------------------------------------------------------------------------
            */

            $device['segment'] =
                $this->resolveSegment(
                    $device['ip_address']
                        ?? null
                );


            $device['raw_dhcp'] =
                $row;


            $devices[$mac] =
                $device;
        }


        /*
        |--------------------------------------------------------------------------
        | Final Collection
        |--------------------------------------------------------------------------
        */

        $devices =
            collect(
                array_values(
                    $devices
                )
            )
            ->sortBy(
                function ($device) {

                    return sprintf(
                        '%-6s-%s',
                        (
                            $device['is_online']
                            ?? false
                        )
                            ? '0'
                            : '1',

                        $device['ip_address']
                            ?? '999.999.999.999'
                    );
                }
            )
            ->values();


        return [
            'success' =>
            true,

            'executed' =>
            true,

            'skipped' =>
            false,

            'arp_count' =>
            $arpRows->count(),

            'dhcp_count' =>
            $dhcpRows->count(),

            'device_count' =>
            $devices->count(),

            'devices' =>
            $devices,

            'message' =>
            'Data perangkat MikroTik berhasil dinormalisasi.',
        ];
    }

    /**
     * Sinkronisasi perangkat MikroTik
     * ke tabel devices.
     */
    public function syncToDatabase(): array
    {
        /*
    |--------------------------------------------------------------------------
    | Ambil data ARP + DHCP
    |--------------------------------------------------------------------------
    */

        $result =
            $this->collectDevices();


        /*
    |--------------------------------------------------------------------------
    | LOCAL MODE
    |--------------------------------------------------------------------------
    */

        if (
            $result['skipped']
            ?? false
        ) {

            return [
                'success' =>
                true,

                'executed' =>
                false,

                'skipped' =>
                true,

                'discovered' =>
                0,

                'inserted' =>
                0,

                'updated' =>
                0,

                'marked_offline' =>
                0,

                'message' =>
                'Sinkronisasi perangkat dilewati karena MikroTik masih mode local.',
            ];
        }


        /*
    |--------------------------------------------------------------------------
    | Gagal mengambil data router
    |--------------------------------------------------------------------------
    */

        if (
            !(
                $result['success']
                ?? false
            )
        ) {

            return [
                'success' =>
                false,

                'executed' =>
                false,

                'skipped' =>
                false,

                'discovered' =>
                0,

                'inserted' =>
                0,

                'updated' =>
                0,

                'marked_offline' =>
                0,

                'error' =>
                $result['error']
                    ?? null,

                'message' =>
                $result['message']
                    ??
                    'Gagal mengambil data perangkat MikroTik.',
            ];
        }


        $devices =
            collect(
                $result['devices']
                    ?? []
            );


        /*
    |--------------------------------------------------------------------------
    | Sesuaikan nama kolom migration
    |--------------------------------------------------------------------------
    |
    | Kita dukung:
    |
    | first_seen_at / first_seen
    | last_seen_at  / last_seen
    | is_online     / online
    |
    */

        $firstSeenColumn =
            Schema::hasColumn(
                'devices',
                'first_seen_at'
            )
            ? 'first_seen_at'
            : 'first_seen';


        $lastSeenColumn =
            Schema::hasColumn(
                'devices',
                'last_seen_at'
            )
            ? 'last_seen_at'
            : 'last_seen';


        $onlineColumn =
            Schema::hasColumn(
                'devices',
                'is_online'
            )
            ? 'is_online'
            : 'online';


        /*
    |--------------------------------------------------------------------------
    | Pastikan migration sesuai
    |--------------------------------------------------------------------------
    */

        foreach (
            [
                'mac_address',
                'ip_address',
                'hostname',
                'interface',
                'segment',
                'source',
                $firstSeenColumn,
                $lastSeenColumn,
                $onlineColumn,
            ]
            as $requiredColumn
        ) {

            if (
                !Schema::hasColumn(
                    'devices',
                    $requiredColumn
                )
            ) {

                return [
                    'success' =>
                    false,

                    'executed' =>
                    false,

                    'skipped' =>
                    false,

                    'message' =>
                    "Kolom devices.{$requiredColumn} tidak ditemukan.",
                ];
            }
        }


        $inserted = 0;
        $updated = 0;
        $markedOffline = 0;

        $seenMacs = [];

        $now = now();


        try {

            DB::transaction(
                function () use (
                    $devices,
                    &$inserted,
                    &$updated,
                    &$seenMacs,
                    &$markedOffline,
                    $firstSeenColumn,
                    $lastSeenColumn,
                    $onlineColumn,
                    $now
                ) {

                    /*
                |--------------------------------------------------------------------------
                | Upsert setiap perangkat
                |--------------------------------------------------------------------------
                */

                    foreach (
                        $devices
                        as $item
                    ) {

                        $mac =
                            $item['mac_address']
                            ?? null;


                        if (!$mac) {
                            continue;
                        }


                        $seenMacs[] =
                            $mac;


                        $device =
                            Device::query()
                            ->where(
                                'mac_address',
                                $mac
                            )
                            ->first();


                        /*
                    |--------------------------------------------------------------------------
                    | Data otomatis dari MikroTik
                    |--------------------------------------------------------------------------
                    */

                        $automaticData = [

                            'mac_address' =>
                            $mac,

                            'ip_address' =>
                            $item['ip_address']
                                ?? null,

                            'interface' =>
                            $item['interface']
                                ?? null,

                            'segment' =>
                            $item['segment']
                                ?? null,

                            'source' =>
                            'mikrotik',

                            $onlineColumn =>
                            (bool) (
                                $item['is_online']
                                ?? false
                            ),

                            $lastSeenColumn => (
                                $item['is_online']
                                ?? false
                            )
                                ? $now
                                : (
                                    $device
                                    ?->{$lastSeenColumn}
                                    ?? null
                                ),
                        ];


                        /*
                    |--------------------------------------------------------------------------
                    | Jangan hapus hostname lama
                    | kalau MikroTik tidak mengirim hostname.
                    |--------------------------------------------------------------------------
                    */

                        if (
                            !empty($item['hostname'])
                        ) {

                            $automaticData['hostname'] =
                                $item['hostname'];
                        }


                        /*
                    |--------------------------------------------------------------------------
                    | Perangkat baru
                    |--------------------------------------------------------------------------
                    */

                        if (!$device) {

                            $device =
                                new Device();


                            $automaticData[$firstSeenColumn] =
                                $now;


                            /*
                        |--------------------------------------------------------------------------
                        | Device baru yang terlihat
                        |--------------------------------------------------------------------------
                        */

                            if (
                                empty($automaticData[$lastSeenColumn])
                            ) {

                                $automaticData[$lastSeenColumn] =
                                    $now;
                            }


                            /*
                        |--------------------------------------------------------------------------
                        | forceFill dipakai agar sync
                        | tidak bergantung pada $fillable.
                        |--------------------------------------------------------------------------
                        */

                            $device
                                ->forceFill(
                                    $automaticData
                                )
                                ->save();


                            $inserted++;

                            continue;
                        }


                        /*
                    |--------------------------------------------------------------------------
                    | Existing device
                    |--------------------------------------------------------------------------
                    |
                    | device_name
                    | device_type
                    | operating_system
                    | location
                    | owner_name
                    | owner_type
                    |
                    | TIDAK disentuh.
                    |
                    */

                        $device
                            ->forceFill(
                                $automaticData
                            )
                            ->save();


                        $updated++;
                    }


                    /*
                |--------------------------------------------------------------------------
                | Tandai OFFLINE
                |--------------------------------------------------------------------------
                |
                | Device yang tidak terlihat lagi tidak langsung offline.
                | Tunggu sesuai:
                |
                | MIKROTIK_OFFLINE_AFTER_MINUTES
                |
                */

                    $offlineAfter =
                        max(
                            1,
                            (int) config(
                                'mikrotik.device_sync.offline_after_minutes',
                                5
                            )
                        );


                    $offlineThreshold =
                        now()->subMinutes(
                            $offlineAfter
                        );


                    $offlineQuery =
                        Device::query()
                        ->where(
                            'source',
                            'mikrotik'
                        )
                        ->where(
                            $onlineColumn,
                            true
                        )
                        ->where(
                            function ($query) use (
                                $lastSeenColumn,
                                $offlineThreshold
                            ) {

                                $query
                                    ->whereNull(
                                        $lastSeenColumn
                                    )
                                    ->orWhere(
                                        $lastSeenColumn,
                                        '<=',
                                        $offlineThreshold
                                    );
                            }
                        );


                    /*
                |--------------------------------------------------------------------------
                | Device yang baru terlihat jangan pernah
                | ikut ditandai offline.
                |--------------------------------------------------------------------------
                */

                    if (
                        !empty($seenMacs)
                    ) {

                        $offlineQuery
                            ->whereNotIn(
                                'mac_address',
                                array_unique(
                                    $seenMacs
                                )
                            );
                    }


                    $markedOffline =
                        $offlineQuery
                        ->update([
                            $onlineColumn =>
                            false,
                        ]);
                }
            );


            return [
                'success' =>
                true,

                'executed' =>
                true,

                'skipped' =>
                false,

                'arp_count' =>
                (int) (
                    $result['arp_count']
                    ?? 0
                ),

                'dhcp_count' =>
                (int) (
                    $result['dhcp_count']
                    ?? 0
                ),

                'discovered' =>
                $devices->count(),

                'inserted' =>
                $inserted,

                'updated' =>
                $updated,

                'marked_offline' =>
                $markedOffline,

                'online_total' =>
                Device::query()
                    ->where(
                        $onlineColumn,
                        true
                    )
                    ->count(),

                'total_devices' =>
                Device::count(),

                'message' =>
                'Sinkronisasi perangkat MikroTik berhasil.',
            ];
        } catch (Throwable $e) {

            return [
                'success' =>
                false,

                'executed' =>
                false,

                'skipped' =>
                false,

                'discovered' =>
                $devices->count(),

                'inserted' =>
                $inserted,

                'updated' =>
                $updated,

                'marked_offline' =>
                $markedOffline,

                'error' =>
                $e->getMessage(),

                'message' =>
                'Sinkronisasi perangkat MikroTik ke database gagal.',
            ];
        }
    }
    /**
     * Cari nama segmen berdasarkan IP.
     */
    public function resolveSegment(
        ?string $ip
    ): ?string {
        if (
            $ip === null
            ||
            filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4
            ) === false
        ) {

            return null;
        }


        foreach (
            config(
                'mikrotik.segments',
                []
            )
            as $segment
        ) {

            $cidr =
                $segment['cidr']
                ?? null;


            if (
                $cidr
                &&
                $this->ipInCidr(
                    $ip,
                    $cidr
                )
            ) {

                return $segment['name']
                    ?? $cidr;
            }
        }


        return 'Lainnya';
    }


    /**
     * Normalisasi MAC address.
     */
    private function normalizeMac(
        ?string $mac
    ): ?string {
        if ($mac === null) {
            return null;
        }


        $mac =
            strtoupper(
                trim($mac)
            );


        if ($mac === '') {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Validasi MAC.
        |--------------------------------------------------------------------------
        */

        if (
            filter_var(
                $mac,
                FILTER_VALIDATE_MAC
            ) === false
        ) {

            return null;
        }


        return str_replace(
            '-',
            ':',
            $mac
        );
    }


    /**
     * Normalisasi IPv4.
     */
    private function normalizeIp(
        ?string $ip
    ): ?string {
        if ($ip === null) {
            return null;
        }


        $ip =
            trim($ip);


        if (
            filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4
            ) === false
        ) {

            return null;
        }


        return $ip;
    }


    /**
     * Bersihkan string RouterOS.
     */
    private function cleanString(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }


        $value =
            trim($value);


        return $value !== ''
            ? $value
            : null;
    }


    /**
     * Check IP berada di CIDR.
     */
    private function ipInCidr(
        string $ip,
        string $cidr
    ): bool {
        if (
            !str_contains(
                $cidr,
                '/'
            )
        ) {

            return false;
        }


        [
            $network,
            $prefix
        ] =
            explode(
                '/',
                $cidr,
                2
            );


        $ipLong =
            ip2long($ip);

        $networkLong =
            ip2long($network);


        if (
            $ipLong === false
            ||
            $networkLong === false
        ) {

            return false;
        }


        $prefix =
            (int) $prefix;


        if (
            $prefix < 0
            ||
            $prefix > 32
        ) {

            return false;
        }


        $mask =
            $prefix === 0

            ? 0

            : (
                -1
                <<
                (
                    32 -
                    $prefix
                )
            );


        return (
            $ipLong
            &
            $mask
        )
            ===
            (
                $networkLong
                &
                $mask
            );
    }
}
