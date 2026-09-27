<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlacklistDomain;
use App\Services\SquidBlacklistService;
use App\Services\SquidControlService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class BlacklistController extends Controller
{
    /**
     * Tampilkan halaman blacklist.
     */
    public function index(
        Request $request,
        SquidBlacklistService $blacklist,
        SquidControlService $control
    ) {
        $query = BlacklistDomain::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                $request->search
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
                        'notes',
                        'like',
                        "%{$search}%"
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status aktif
        |--------------------------------------------------------------------------
        */

        if ($request->status === 'active') {

            $query->where(
                'is_active',
                true
            );

        } elseif (
            $request->status === 'inactive'
        ) {

            $query->where(
                'is_active',
                false
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status sinkronisasi
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'sync_status'
            )
        ) {

            $query->where(
                'sync_status',
                $request->sync_status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $domains = $query
            ->orderByDesc('is_active')
            ->orderBy('domain')
            ->paginate(20)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Summary
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

            'synced' =>
                BlacklistDomain::where(
                    'sync_status',
                    BlacklistDomain::SYNC_SYNCED
                )->count(),

            'pending' =>
                BlacklistDomain::where(
                    'sync_status',
                    BlacklistDomain::SYNC_PENDING
                )->count(),

            'error' =>
                BlacklistDomain::where(
                    'sync_status',
                    BlacklistDomain::SYNC_ERROR
                )->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | File blacklist saat ini
        |--------------------------------------------------------------------------
        */

        $currentFile =
            $blacklist->readCurrentFile();


        /*
        |--------------------------------------------------------------------------
        | Squid Control Status
        |--------------------------------------------------------------------------
        */

        $controlStatus =
            $control->status();


        return view(
            'pages.admin.blacklist',
            compact(
                'domains',
                'summary',
                'currentFile',
                'controlStatus'
            )
        );
    }


    /**
     * Tambah domain blacklist.
     */
    public function store(
        Request $request,
        SquidBlacklistService $blacklist,
        SquidControlService $control
    ) {
        $validated =
            $this->validateDomain(
                $request
            );


        BlacklistDomain::create([

            'domain' =>
                strtolower(
                    trim(
                        $validated['domain']
                    )
                ),

            'category' =>
                $validated['category']
                ?? null,

            'include_subdomains' =>
                $request->boolean(
                    'include_subdomains'
                ),

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),

            'source' =>
                'manual',

            'sync_status' =>
                BlacklistDomain::SYNC_PENDING,

            'synced_at' =>
                null,

            'notes' =>
                $validated['notes']
                ?? null,
        ]);


        return $this->syncAndRedirect(
            $blacklist,
            $control,
            'Domain berhasil ditambahkan.'
        );
    }


    /**
     * Update domain blacklist.
     */
    public function update(
        Request $request,
        BlacklistDomain $blacklistDomain,
        SquidBlacklistService $blacklist,
        SquidControlService $control
    ) {
        $validated =
            $this->validateDomain(
                $request,
                $blacklistDomain
            );


        $blacklistDomain->update([

            'domain' =>
                strtolower(
                    trim(
                        $validated['domain']
                    )
                ),

            'category' =>
                $validated['category']
                ?? null,

            'include_subdomains' =>
                $request->boolean(
                    'include_subdomains'
                ),

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),

            'sync_status' =>
                BlacklistDomain::SYNC_PENDING,

            'synced_at' =>
                null,

            'notes' =>
                $validated['notes']
                ?? null,
        ]);


        return $this->syncAndRedirect(
            $blacklist,
            $control,
            'Domain berhasil diperbarui.'
        );
    }


    /**
     * Aktif / nonaktif domain.
     */
    public function toggle(
        BlacklistDomain $blacklistDomain,
        SquidBlacklistService $blacklist,
        SquidControlService $control
    ) {
        $blacklistDomain->update([

            'is_active' =>
                !$blacklistDomain
                    ->is_active,

            'sync_status' =>
                BlacklistDomain::SYNC_PENDING,

            'synced_at' =>
                null,
        ]);


        return $this->syncAndRedirect(
            $blacklist,
            $control,

            $blacklistDomain->is_active

                ? 'Domain berhasil diaktifkan.'

                : 'Domain berhasil dinonaktifkan.'
        );
    }


    /**
     * Hapus domain.
     */
    public function destroy(
        BlacklistDomain $blacklistDomain,
        SquidBlacklistService $blacklist,
        SquidControlService $control
    ) {
        $domain =
            $blacklistDomain->domain;


        $blacklistDomain->delete();


        return $this->syncAndRedirect(
            $blacklist,
            $control,
            "Domain {$domain} berhasil dihapus."
        );
    }


    /**
     * Sinkronisasi manual.
     */
    public function sync(
        SquidBlacklistService $blacklist,
        SquidControlService $control
    ) {
        return $this->syncAndRedirect(
            $blacklist,
            $control,
            'Blacklist berhasil disinkronkan.'
        );
    }


    /**
     * Validasi input domain.
     */
    private function validateDomain(
        Request $request,
        ?BlacklistDomain $domain = null
    ): array {
        return $request->validate([

            'domain' => [

                'required',
                'string',
                'max:255',

                Rule::unique(
                    'blacklist_domains',
                    'domain'
                )->ignore(
                    $domain?->id
                ),

                function (
                    $attribute,
                    $value,
                    $fail
                ) {

                    $value =
                        strtolower(
                            trim($value)
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Jangan masukkan URL penuh
                    |--------------------------------------------------------------------------
                    */

                    if (
                        str_contains(
                            $value,
                            '://'
                        )
                        ||
                        str_contains(
                            $value,
                            '/'
                        )
                    ) {

                        $fail(
                            'Masukkan domain saja, contoh facebook.com tanpa http:// atau path.'
                        );

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Bersihkan wildcard / titik depan
                    |--------------------------------------------------------------------------
                    */

                    if (
                        str_starts_with(
                            $value,
                            '*.'
                        )
                    ) {

                        $value =
                            substr(
                                $value,
                                2
                            );
                    }


                    $value =
                        ltrim(
                            $value,
                            '.'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Validasi hostname
                    |--------------------------------------------------------------------------
                    */

                    if (
                        filter_var(
                            $value,
                            FILTER_VALIDATE_DOMAIN,
                            FILTER_FLAG_HOSTNAME
                        )
                        === false
                    ) {

                        $fail(
                            'Format domain tidak valid.'
                        );
                    }
                },
            ],


            'category' => [
                'nullable',
                'string',
                'max:100',
            ],


            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);
    }


    /**
     * DB → blacklist.txt → Squid.
     */
    private function syncAndRedirect(
        SquidBlacklistService $blacklist,
        SquidControlService $control,
        string $successMessage
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | Database → blacklist.txt
            |--------------------------------------------------------------------------
            */

            $blacklist->sync();


            /*
            |--------------------------------------------------------------------------
            | blacklist.txt → Squid
            |--------------------------------------------------------------------------
            */

            $apply =
                $control->apply();


            /*
            |--------------------------------------------------------------------------
            | LOCAL MODE
            |--------------------------------------------------------------------------
            */

            if (
                config('squid.mode')
                === 'local'
            ) {

                return redirect()
                    ->route(
                        'admin.blacklist.index'
                    )
                    ->with(
                        'success',
                        $successMessage
                        .
                        ' File blacklist lokal juga sudah diperbarui.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | PRODUCTION SUCCESS
            |--------------------------------------------------------------------------
            */

            if (
                $apply['success']
                ?? false
            ) {

                return redirect()
                    ->route(
                        'admin.blacklist.index'
                    )
                    ->with(
                        'success',
                        $successMessage
                        .
                        ' Squid berhasil direconfigure.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | PRODUCTION APPLY FAILED
            |--------------------------------------------------------------------------
            */

            BlacklistDomain::query()
                ->update([
                    'sync_status' =>
                        BlacklistDomain::SYNC_ERROR,
                ]);


            return redirect()
                ->route(
                    'admin.blacklist.index'
                )
                ->with(
                    'warning',
                    $successMessage
                    .
                    ' File berhasil diperbarui, tetapi Squid belum berhasil menerapkan konfigurasi. '
                    .
                    (
                        $apply[
                            'message'
                        ]
                        ?? ''
                    )
                );


        } catch (Throwable $e) {

            return redirect()
                ->route(
                    'admin.blacklist.index'
                )
                ->withInput()
                ->with(
                    'error',
                    'Sinkronisasi blacklist gagal: '
                    .
                    $e->getMessage()
                );
        }
    }
}