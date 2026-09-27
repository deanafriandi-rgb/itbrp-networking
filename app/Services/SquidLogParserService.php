<?php

namespace App\Services;

use Carbon\Carbon;

class SquidLogParserService
{
    /**
     * Parse satu baris access.log Squid.
     */
    public function parse(string $line): ?array
    {
        $line = trim($line);

        if ($line === '') {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Format default access.log Squid
        |--------------------------------------------------------------------------
        |
        | timestamp
        | elapsed
        | client_ip
        | squid_code/http_code
        | bytes
        | method
        | url
        | rfc931
        | hierarchy
        | mime_type
        |
        */

        $parts = preg_split('/\s+/', $line, 10);

        if (!$parts || count($parts) < 9) {
            return null;
        }

        $timestamp    = $parts[0] ?? null;
        $elapsedMs   = $parts[1] ?? 0;
        $clientIp    = $parts[2] ?? null;
        $codeStatus  = $parts[3] ?? null;
        $bytes       = $parts[4] ?? 0;
        $method      = strtoupper($parts[5] ?? '');
        $url         = $parts[6] ?? null;
        $rfc931      = $parts[7] ?? null;
        $hierarchy   = $parts[8] ?? null;
        $mimeType    = $parts[9] ?? null;

        if (!$timestamp || !$clientIp || !$codeStatus) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Abaikan trafik internal seperti MikroTik Netwatch
        |--------------------------------------------------------------------------
        */

        if (in_array($clientIp, config('squid.ignored_ips', []), true)) {
            return null;
        }

        [$squidCode, $httpCode] = $this->parseCodeStatus($codeStatus);

        $target = $this->parseTarget(
            $method,
            $url
        );

        $destinationIp = $this->extractDestinationIp(
            $hierarchy,
            $target['host']
        );

        return [
            'logged_at' => $this->parseTimestamp($timestamp),

            'client_ip' => $clientIp,

            'domain' => $target['domain'],
            'url' => $url,
            'method' => $method ?: null,
            'protocol' => $target['protocol'],

            'squid_code' => $squidCode,
            'http_code' => $httpCode,

            'bytes' => is_numeric($bytes)
                ? (int) $bytes
                : 0,

            'elapsed_ms' => is_numeric($elapsedMs)
                ? (int) $elapsedMs
                : 0,

            'hierarchy' => $hierarchy !== '-'
                ? $hierarchy
                : null,

            'destination_ip' => $destinationIp,

            'destination_port' => $target['port'],

            'mime_type' => $mimeType && $mimeType !== '-'
                ? $mimeType
                : null,

            'category' => $this->classify(
                $squidCode
            ),
        ];
    }

    /**
     * Parse TCP_MISS/200 → TCP_MISS + 200.
     */
    private function parseCodeStatus(string $value): array
    {
        $parts = explode('/', $value, 2);

        $code = $parts[0] ?? null;
        $http = $parts[1] ?? null;

        return [
            $code,
            is_numeric($http)
                ? (int) $http
                : null,
        ];
    }

    /**
     * Mengambil host/domain/port/protocol.
     */
    private function parseTarget(
        string $method,
        ?string $url
    ): array {
        $result = [
            'domain' => null,
            'host' => null,
            'port' => null,
            'protocol' => null,
        ];

        if (!$url || $url === '-') {
            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | HTTPS CONNECT
        |--------------------------------------------------------------------------
        |
        | Contoh:
        | CONNECT www.zoom.com:443
        | CONNECT 170.114.78.80:443
        |
        */

        if ($method === 'CONNECT') {
            $result['protocol'] = 'HTTPS';

            [$host, $port] = $this->splitHostPort($url);

            $result['host'] = $host;
            $result['port'] = $port ?: 443;

            if ($host && !filter_var($host, FILTER_VALIDATE_IP)) {
                $result['domain'] = strtolower($host);
            }

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | HTTP / URL biasa
        |--------------------------------------------------------------------------
        */

        $parsed = @parse_url($url);

        if (!is_array($parsed)) {
            return $result;
        }

        $scheme = strtolower(
            $parsed['scheme'] ?? ''
        );

        $host = strtolower(
            $parsed['host'] ?? ''
        );

        $result['host'] = $host ?: null;

        if ($host && !filter_var($host, FILTER_VALIDATE_IP)) {
            $result['domain'] = $host;
        }

        if ($scheme === 'https') {
            $result['protocol'] = 'HTTPS';
        } elseif ($scheme === 'http') {
            $result['protocol'] = 'HTTP';
        } else {
            $result['protocol'] = $scheme
                ? strtoupper($scheme)
                : null;
        }

        if (isset($parsed['port'])) {
            $result['port'] = (int) $parsed['port'];
        } elseif ($scheme === 'https') {
            $result['port'] = 443;
        } elseif ($scheme === 'http') {
            $result['port'] = 80;
        }

        return $result;
    }

    /**
     * Pecah domain:port dengan aman.
     */
    private function splitHostPort(string $target): array
    {
        /*
        |--------------------------------------------------------------------------
        | IPv6 dalam format [xxxx]:443
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with($target, '[') &&
            preg_match('/^\[(.+)]:(\d+)$/', $target, $match)
        ) {
            return [
                $match[1],
                (int) $match[2],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | IPv4 / hostname biasa
        |--------------------------------------------------------------------------
        */

        $position = strrpos($target, ':');

        if ($position === false) {
            return [$target, null];
        }

        $host = substr($target, 0, $position);
        $port = substr($target, $position + 1);

        if (!ctype_digit($port)) {
            return [$target, null];
        }

        return [
            $host,
            (int) $port,
        ];
    }

    /**
     * ORIGINAL_DST/172.217.113.4
     * HIER_DIRECT/1.2.3.4
     */
    private function extractDestinationIp(
        ?string $hierarchy,
        ?string $targetHost
    ): ?string {
        if ($hierarchy && str_contains($hierarchy, '/')) {
            [, $host] = explode('/', $hierarchy, 2);

            if (filter_var($host, FILTER_VALIDATE_IP)) {
                return $host;
            }
        }

        if (
            $targetHost &&
            filter_var($targetHost, FILTER_VALIDATE_IP)
        ) {
            return $targetHost;
        }

        return null;
    }

    /**
     * Timestamp Squid:
     *
     * 1790439638.323
     */
    private function parseTimestamp(
        string $timestamp
    ): string {
        $seconds = (float) $timestamp;

        $wholeSeconds = (int) floor($seconds);

        $milliseconds = (int) round(
            ($seconds - $wholeSeconds) * 1000
        );

        if ($milliseconds >= 1000) {
            $wholeSeconds++;
            $milliseconds = 0;
        }

        return Carbon::createFromTimestamp(
            $wholeSeconds
        )
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s.')
            . str_pad(
                (string) $milliseconds,
                3,
                '0',
                STR_PAD_LEFT
            );
    }

    /**
     * Klasifikasi hasil Squid untuk dashboard.
     */
    private function classify(
        ?string $squidCode
    ): string {
        if (!$squidCode) {
            return 'OTHER';
        }

        $code = strtoupper($squidCode);

        /*
        |--------------------------------------------------------------------------
        | BLOCKED
        |--------------------------------------------------------------------------
        */

        if (str_contains($code, 'DENIED')) {
            return 'BLOCKED';
        }

        /*
        |--------------------------------------------------------------------------
        | CACHE HIT
        |--------------------------------------------------------------------------
        */

        $hitCodes = [
            'TCP_HIT',
            'TCP_MEM_HIT',
            'TCP_REFRESH_HIT',
            'TCP_IMS_HIT',
            'TCP_NEGATIVE_HIT',
            'TCP_OFFLINE_HIT',
        ];

        if (in_array($code, $hitCodes, true)) {
            return 'CACHE_HIT';
        }

        /*
        |--------------------------------------------------------------------------
        | CACHE MISS
        |--------------------------------------------------------------------------
        */

        $missCodes = [
            'TCP_MISS',
            'TCP_REFRESH_MISS',
            'TCP_CLIENT_REFRESH_MISS',
        ];

        if (in_array($code, $missCodes, true)) {
            return 'CACHE_MISS';
        }

        /*
        |--------------------------------------------------------------------------
        | HTTPS tunnel normal
        |--------------------------------------------------------------------------
        */

        if (
            $code === 'TCP_TUNNEL' ||
            str_starts_with($code, 'TCP_TUNNEL')
        ) {
            return 'ALLOWED';
        }

        /*
        |--------------------------------------------------------------------------
        | Connection gagal / tidak selesai
        |--------------------------------------------------------------------------
        */

        if (
            $code === 'NONE_NONE' ||
            str_contains($code, 'ABORTED') ||
            str_starts_with($code, 'ERR_')
        ) {
            return 'FAILED';
        }

        return 'OTHER';
    }
}
