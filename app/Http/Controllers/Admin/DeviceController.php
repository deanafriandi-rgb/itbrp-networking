<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\MikrotikClientService;
use App\Services\MikrotikDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DeviceController extends Controller
{
    /**
     * Tampilkan halaman perangkat.
     */
    public function index(
        Request $request,
        MikrotikClientService $mikrotik
    ) {
        /*
        |--------------------------------------------------------------------------
        | Deteksi nama kolom status online
        |--------------------------------------------------------------------------
        */

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
        | Query
        |--------------------------------------------------------------------------
        */

        $query =
            Device::query();


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'search'
            )
        ) {

            $search =
                trim(
                    $request->search
                );


            $query->where(
                function ($q) use ($search) {

                    $q->where(
                        'ip_address',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'mac_address',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'hostname',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'device_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'owner_name',
                        'like',
                        "%{$search}%"
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $request->status
            === 'online'
        ) {

            $query->where(
                $onlineColumn,
                true
            );

        } elseif (
            $request->status
            === 'offline'
        ) {

            $query->where(
                $onlineColumn,
                false
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Segment
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'segment'
            )
        ) {

            $query->where(
                'segment',
                $request->segment
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Interface
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'interface'
            )
        ) {

            $query->where(
                'interface',
                $request->interface
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Source
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'source'
            )
        ) {

            $query->where(
                'source',
                $request->source
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        |
        | Online ditampilkan lebih dulu,
        | kemudian last seen terbaru.
        |
        */

        $devices =
            $query
                ->orderByDesc(
                    $onlineColumn
                )
                ->orderByDesc(
                    $lastSeenColumn
                )
                ->paginate(25)
                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Summary
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

            'segments' =>
                Device::whereNotNull(
                    'segment'
                )
                ->where(
                    'segment',
                    '!=',
                    ''
                )
                ->distinct()
                ->count(
                    'segment'
                ),

            'interfaces' =>
                Device::whereNotNull(
                    'interface'
                )
                ->where(
                    'interface',
                    '!=',
                    ''
                )
                ->distinct()
                ->count(
                    'interface'
                ),

            'latest_seen' =>
                Device::max(
                    $lastSeenColumn
                ),
        ];


        /*
        |--------------------------------------------------------------------------
        | Filter options
        |--------------------------------------------------------------------------
        */

        $segments =
            Device::query()
                ->whereNotNull(
                    'segment'
                )
                ->where(
                    'segment',
                    '!=',
                    ''
                )
                ->distinct()
                ->orderBy(
                    'segment'
                )
                ->pluck(
                    'segment'
                );


        /*
        |--------------------------------------------------------------------------
        | Tambahkan mapping campus meskipun DB masih kosong
        |--------------------------------------------------------------------------
        */

        $configuredSegments =
            collect(
                config(
                    'mikrotik.segments',
                    []
                )
            )
            ->pluck(
                'name'
            )
            ->filter();


        $segments =
            $segments
                ->merge(
                    $configuredSegments
                )
                ->unique()
                ->sort()
                ->values();


        $interfaces =
            Device::query()
                ->whereNotNull(
                    'interface'
                )
                ->where(
                    'interface',
                    '!=',
                    ''
                )
                ->distinct()
                ->orderBy(
                    'interface'
                )
                ->pluck(
                    'interface'
                );


        /*
        |--------------------------------------------------------------------------
        | Status integrasi MikroTik
        |--------------------------------------------------------------------------
        */

        $mikrotikStatus =
            $mikrotik->status();


        return view(
            'pages.admin.devices',
            compact(
                'devices',
                'summary',
                'segments',
                'interfaces',
                'mikrotikStatus',
                'onlineColumn',
                'lastSeenColumn'
            )
        );
    }


    /**
     * Sinkronisasi perangkat secara manual.
     */
    public function sync(
        MikrotikDeviceService $devices
    ) {
        try {

            $result =
                $devices
                    ->syncToDatabase();


            /*
            |--------------------------------------------------------------------------
            | LOCAL MODE
            |--------------------------------------------------------------------------
            */

            if (
                $result['skipped']
                ?? false
            ) {

                return redirect()
                    ->route(
                        'admin.devices.index'
                    )
                    ->with(
                        'warning',
                        $result['message']
                        ??
                        'Sinkronisasi MikroTik dilewati.'
                    );
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

                return redirect()
                    ->route(
                        'admin.devices.index'
                    )
                    ->with(
                        'error',
                        (
                            $result[
                                'message'
                            ]
                            ??
                            'Sinkronisasi perangkat gagal.'
                        )
                        .
                        (
                            !empty(
                                $result[
                                    'error'
                                ]
                            )
                                ? ' '
                                .
                                $result[
                                    'error'
                                ]
                                : ''
                        )
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            $message =
                sprintf(
                    'Sinkronisasi berhasil. %d perangkat ditemukan, %d baru, %d diperbarui, %d menjadi offline.',
                    (int) (
                        $result[
                            'discovered'
                        ]
                        ?? 0
                    ),
                    (int) (
                        $result[
                            'inserted'
                        ]
                        ?? 0
                    ),
                    (int) (
                        $result[
                            'updated'
                        ]
                        ?? 0
                    ),
                    (int) (
                        $result[
                            'marked_offline'
                        ]
                        ?? 0
                    )
                );


            return redirect()
                ->route(
                    'admin.devices.index'
                )
                ->with(
                    'success',
                    $message
                );


        } catch (Throwable $e) {

            return redirect()
                ->route(
                    'admin.devices.index'
                )
                ->with(
                    'error',
                    'Sinkronisasi perangkat gagal: '
                    .
                    $e->getMessage()
                );
        }
    }
}