<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class DeviceController extends Controller
{
    /**
     * Halaman monitoring perangkat (read-only).
     */
    public function index(Request $request)
    {
        $onlineColumn =
            Schema::hasColumn(
                'devices',
                'is_online'
            )
            ? 'is_online'
            : 'online';

        $lastSeenColumn =
            Schema::hasColumn(
                'devices',
                'last_seen_at'
            )
            ? 'last_seen_at'
            : 'last_seen';


        /*
        |--------------------------------------------------------------------------
        | Filter perangkat
        |--------------------------------------------------------------------------
        */

        $query =
            Device::query();


        if ($request->filled('search')) {

            $search =
                trim(
                    (string) $request->search
                );

            $query->where(
                function ($q) use ($search) {

                    $q->where(
                        'device_name',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'hostname',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'owner_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'ip_address',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'mac_address',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }


        if ($request->status === 'online') {

            $query->where(
                $onlineColumn,
                true
            );
        } elseif ($request->status === 'offline') {

            $query->where(
                $onlineColumn,
                false
            );
        }


        if ($request->filled('owner_type')) {

            $query->where(
                'owner_type',
                $request->owner_type
            );
        }


        if ($request->filled('segment')) {

            $query->where(
                'segment',
                $request->segment
            );
        }


        $devices =
            $query
            ->orderByDesc(
                $onlineColumn
            )
            ->orderByDesc(
                $lastSeenColumn
            )
            ->paginate(20)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Summary global
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total' =>
            Device::count(),

            'online' =>
            Device::where(
                $onlineColumn,
                true
            )->count(),

            'offline' =>
            Device::where(
                $onlineColumn,
                false
            )->count(),

            'staff' =>
            Device::whereIn(
                'owner_type',
                [
                    'dosen',
                    'staff',
                    'dosen_staff',
                ]
            )->count(),

            'student' =>
            Device::where(
                'owner_type',
                'mahasiswa'
            )->count(),

            'guest' =>
            Device::where(
                'owner_type',
                'tamu'
            )->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Distribusi owner type
        |--------------------------------------------------------------------------
        */

        $ownerDistribution =
            Device::query()
            ->selectRaw(
                "
                    COALESCE(
                        NULLIF(owner_type, ''),
                        'belum_diklasifikasikan'
                    ) AS owner_type,
                    COUNT(*) AS total
                    "
            )
            ->groupBy('owner_type')
            ->orderByDesc('total')
            ->get()
            ->map(
                function ($row) {

                    return [
                        'owner_type' =>
                        $row->owner_type,

                        'total' =>
                        (int) $row->total,
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Distribusi segmen
        |--------------------------------------------------------------------------
        */

        $segmentDistribution =
            Device::query()
            ->selectRaw(
                "
                    COALESCE(
                        NULLIF(segment, ''),
                        'Belum Diketahui'
                    ) AS segment_name,
                    COUNT(*) AS total
                    "
            )
            ->groupBy('segment_name')
            ->orderByDesc('total')
            ->get()
            ->map(
                function ($row) {

                    return [
                        'segment' =>
                        $row->segment_name,

                        'total' =>
                        (int) $row->total,
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Pilihan filter
        |--------------------------------------------------------------------------
        */

        $segments =
            Device::query()
            ->whereNotNull('segment')
            ->where('segment', '!=', '')
            ->distinct()
            ->orderBy('segment')
            ->pluck('segment');


        $ownerTypes =
            Device::query()
            ->whereNotNull('owner_type')
            ->where('owner_type', '!=', '')
            ->distinct()
            ->orderBy('owner_type')
            ->pluck('owner_type');


        /*
        |--------------------------------------------------------------------------
        | Trafik 24 jam berdasarkan IP perangkat saat ini
        |--------------------------------------------------------------------------
        |
        | Catatan:
        | Nilai ini mengikuti IP yang tersimpan saat halaman dibuka.
        | Jika IP berubah, histori lama tidak otomatis mengikuti MAC address.
        |
        */

        $from =
            Carbon::now()
            ->subHours(24);

        $displayedIps =
            $devices
            ->getCollection()
            ->pluck('ip_address')
            ->filter()
            ->unique()
            ->values();


        $usageByIp =
            $displayedIps->isEmpty()
            ? collect()
            : AccessLog::query()
            ->selectRaw(
                '
                        client_ip,
                        COALESCE(SUM(bytes), 0) AS total_bytes,
                        COUNT(*) AS total_request
                        '
            )
            ->where(
                'logged_at',
                '>=',
                $from
            )
            ->whereIn(
                'client_ip',
                $displayedIps
            )
            ->groupBy('client_ip')
            ->get()
            ->keyBy('client_ip');


        /*
        |--------------------------------------------------------------------------
        | Perangkat terbaru
        |--------------------------------------------------------------------------
        */

        $latestDevices =
            Device::query()
            ->orderByDesc(
                $lastSeenColumn
            )
            ->orderByDesc('id')
            ->limit(5)
            ->get();


        return view(
            'pages.monitoring.devices',
            compact(
                'devices',
                'summary',
                'ownerDistribution',
                'segmentDistribution',
                'segments',
                'ownerTypes',
                'usageByIp',
                'latestDevices',
                'onlineColumn',
                'lastSeenColumn'
            )
        );
    }
}
