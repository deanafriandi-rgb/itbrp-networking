<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /**
     * Halaman profil pengembang monitoring (read-only).
     */
    public function index()
    {
        $profile = [
            'name' => 'Dian Afriandi',
            'role' => 'Mahasiswa Program Studi Informatika',
            'institution' => 'Institut Teknologi dan Bisnis Riau Pesisir',
            'study_program' => 'Informatika',
            'year' => '2026',
        ];

        $system = [
            'name' => 'ITBRP Network Monitoring',

            'description' => 'ITBRP Network Monitoring dikembangkan sebagai dashboard pemantauan untuk membantu menampilkan aktivitas akses internet, client, filtering domain, cache Squid Proxy, perangkat jaringan, statistik, serta status layanan jaringan dalam satu antarmuka.',

            'features' => [
                [
                    'title' => 'Squid Proxy',
                    'description' => 'Filtering, access log, cache HIT/MISS, dan monitoring aktivitas akses internet.',
                ],
                [
                    'title' => 'Laravel',
                    'description' => 'Dashboard, parser log, statistik, monitoring perangkat, blacklist, dan status sistem.',
                ],
                [
                    'title' => 'MikroTik',
                    'description' => 'Multi-WAN, policy routing, informasi perangkat, serta mekanisme fail-open.',
                ],
                [
                    'title' => 'Multi-WAN',
                    'description' => 'Infrastruktur existing tetap dipertahankan dengan integrasi Squid sebagai layanan tambahan.',
                ],
            ],
        ];

        $academic = [
            'project' => 'ITBRP Network Monitoring',
            'focus' => 'Squid Proxy • Filtering • Monitoring • Caching • MikroTik • Laravel • Multi-WAN',
            'research_title' => 'Implementasi Squid Proxy untuk Filtering dan Monitoring Akses Internet pada Jaringan Multi-WAN di Institut Teknologi dan Bisnis Riau Pesisir',
        ];

        $contributions = [
            [
                'number' => '1',
                'title' => 'Perancangan',
                'description' => 'Merancang arsitektur integrasi Squid Proxy, MikroTik, jaringan Multi-WAN, dan dashboard Laravel.',
            ],
            [
                'number' => '2',
                'title' => 'Implementasi',
                'description' => 'Menerapkan filtering HTTP/HTTPS, caching, access logging, dan mekanisme fail-open.',
            ],
            [
                'number' => '3',
                'title' => 'Dashboard',
                'description' => 'Membangun antarmuka admin dan monitoring read-only untuk lingkungan kampus.',
            ],
            [
                'number' => '4',
                'title' => 'Pengujian',
                'description' => 'Melakukan validasi access log, cache HIT/MISS, response time, perangkat, dan ketersediaan layanan.',
            ],
        ];

        return view(
            'pages.monitoring.profile',
            compact(
                'profile',
                'system',
                'academic',
                'contributions'
            )
        );
    }
}
