<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\BlacklistDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BlacklistController extends Controller
{
    /**
     * Halaman monitoring Filter & Blacklist (read-only).
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Query blacklist
        |--------------------------------------------------------------------------
        */

        $query =
            BlacklistDomain::query();


        if ($request->filled('search')) {

            $search =
                trim(
                    (string) $request->search
                );

            $query->where(
                function ($q) use ($search) {

                    $q->where(
                        'domain',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'category',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'source',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'notes',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }


        if ($request->filled('category')) {

            $query->where(
                'category',
                $request->category
            );
        }


        if ($request->status === 'active') {

            $query->where(
                'is_active',
                true
            );
        } elseif ($request->status === 'inactive') {

            $query->where(
                'is_active',
                false
            );
        }


        if ($request->filled('sync_status')) {

            $query->where(
                'sync_status',
                $request->sync_status
            );
        }


        $domains =
            $query
            ->orderByDesc('is_active')
            ->orderBy('domain')
            ->paginate(20)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Summary blacklist
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total' =>
            BlacklistDomain::count(),

            'active' =>
            BlacklistDomain::where(
                'is_active',
                true
            )->count(),

            'inactive' =>
            BlacklistDomain::where(
                'is_active',
                false
            )->count(),

            'categories' =>
            BlacklistDomain::query()
                ->where(
                    'is_active',
                    true
                )
                ->whereNotNull('category')
                ->where(
                    'category',
                    '!=',
                    ''
                )
                ->distinct()
                ->count('category'),

            'synced' =>
            BlacklistDomain::where(
                'sync_status',
                'synced'
            )->count(),

            'pending' =>
            BlacklistDomain::where(
                'sync_status',
                'pending'
            )->count(),

            'error' =>
            BlacklistDomain::where(
                'sync_status',
                'error'
            )->count(),

            'include_subdomains' =>
            BlacklistDomain::where(
                'include_subdomains',
                true
            )->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Filter options
        |--------------------------------------------------------------------------
        */

        $categories =
            BlacklistDomain::query()
            ->whereNotNull('category')
            ->where(
                'category',
                '!=',
                ''
            )
            ->distinct()
            ->orderBy('category')
            ->pluck('category');


        /*
        |--------------------------------------------------------------------------
        | Blocked request 24 jam
        |--------------------------------------------------------------------------
        */

        $to =
            Carbon::now();

        $from =
            $to->copy()
            ->subHours(24);


        $blocked24 =
            AccessLog::query()
            ->whereBetween(
                'logged_at',
                [
                    $from,
                    $to,
                ]
            )
            ->where(
                'category',
                'BLOCKED'
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Trend blocked request per jam
        |--------------------------------------------------------------------------
        */

        $rows =
            AccessLog::query()
            ->selectRaw(
                "
                    DATE_FORMAT(
                        logged_at,
                        '%Y-%m-%d %H:00:00'
                    ) AS hour,
                    COUNT(*) AS blocked
                    "
            )
            ->whereBetween(
                'logged_at',
                [
                    $from,
                    $to,
                ]
            )
            ->where(
                'category',
                'BLOCKED'
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');


        $blockedTrend =
            collect();

        $cursor =
            $from->copy()
            ->startOfHour();

        $end =
            $to->copy()
            ->startOfHour();


        while ($cursor <= $end) {

            $key =
                $cursor->format(
                    'Y-m-d H:00:00'
                );

            $row =
                $rows->get($key);


            $blockedTrend->push([
                'hour' =>
                $cursor->format(
                    'H:00'
                ),

                'blocked' =>
                $row
                    ? (int) $row->blocked
                    : 0,
            ]);


            $cursor->addHour();
        }


        /*
        |--------------------------------------------------------------------------
        | Informasi policy read-only
        |--------------------------------------------------------------------------
        */

        $policyInfo = [
            'squid_mode' =>
            strtoupper(
                (string) config(
                    'squid.mode',
                    'local'
                )
            ),

            'https_port' =>
            (int) config(
                'squid.ports.https_intercept',
                config(
                    'squid.https_intercept_port',
                    3130
                )
            ),

            'https_method' =>
            'SNI / ssl::server_name',

            'full_tls_decrypt' =>
            false,
        ];


        return view(
            'pages.monitoring.blacklist',
            compact(
                'domains',
                'summary',
                'categories',
                'blocked24',
                'blockedTrend',
                'policyInfo'
            )
        );
    }
}
