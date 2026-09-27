<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /**
     * Halaman profil penelitian.
     */
    public function index()
    {
        $profile = [
            'name' => 'Dian Afriandi',
            'role' => 'Peneliti / Mahasiswa Informatika',
            'study_program' => 'Informatika',
            'institution' => 'Institut Teknologi dan Bisnis Riau Pesisir',
            'year' => '2026',
        ];

        $research = [
            'title' => 'Implementasi Squid Proxy untuk Filtering dan Monitoring Akses Internet pada Jaringan Multi-WAN di Institut Teknologi dan Bisnis Riau Pesisir',

            'description' => 'Penelitian berfokus pada penerapan Squid Proxy pada jaringan Multi-WAN untuk mendukung filtering akses internet, monitoring access log, analisis cache, dan mekanisme fail-open yang tetap mempertahankan konektivitas jaringan ketika layanan proxy tidak tersedia.',

            'focus' => [
                'Squid Proxy',
                'Filtering',
                'Monitoring',
                'Caching',
                'Multi-WAN',
                'MikroTik',
                'Laravel',
                'Fail-Open',
            ],

            'components' => [
                [
                    'name' => 'Squid Proxy',
                    'description' => 'Menangani filtering HTTP/HTTPS, pencatatan access log, dan cache.',
                ],
                [
                    'name' => 'MikroTik',
                    'description' => 'Mengelola Multi-WAN, routing, policy routing, fail-open, ARP, dan DHCP.',
                ],
                [
                    'name' => 'Laravel',
                    'description' => 'Dashboard monitoring, blacklist, statistik, perangkat, cache, dan status sistem.',
                ],
                [
                    'name' => 'MySQL',
                    'description' => 'Menyimpan access log, perangkat, blacklist, status sistem, dan data monitoring.',
                ],
            ],

            'features' => [
                'Monitoring access log',
                'Filtering domain',
                'Monitoring cache HIT/MISS',
                'Monitoring perangkat jaringan',
                'Status layanan Squid dan MikroTik',
                'Fail-open jaringan',
                'Statistik trafik dan klien',
            ],
        ];

        return view(
            'pages.admin.profile',
            compact(
                'profile',
                'research'
            )
        );
    }
}
