<?php

namespace App\Services;

use App\Models\MonitoringLog;
use App\Models\SuspiciousKeyword;
use App\Models\Website;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Berisi logika inti pengecekan uptime, SSL, dan konten mencurigakan
 * (indikasi judi online) sebuah website. Dipakai bersama oleh
 * MonitoringController (tombol "Cek Sekarang") dan RunMonitoringCheck
 * (penjadwalan otomatis malam hari).
 *
 * CATATAN: daftar kata kunci judol sekarang diambil dari tabel
 * suspicious_keywords (bisa diatur admin lewat menu "Kata Diblokir"),
 * bukan hardcode di kelas ini lagi.
 */
class WebsiteMonitorService
{
    /**
     * Cache kata kunci selama satu proses checkAllWebsites(),
     * supaya tidak query database berulang-ulang tiap website.
     */
    private ?array $keywords = null;
    private ?string $lastBatchId = null;

    /**
     * Cek semua website terdaftar, satu per satu.
     * Mengembalikan jumlah website yang berhasil dicek.
     */
    public function checkAllWebsites(): int
    {
        $batchId = (string) \Illuminate\Support\Str::uuid();
        $this->lastBatchId = $batchId;

        $websites = Website::all();

        foreach ($websites as $website) {
            $this->checkWebsite($website, $batchId);
        }

        return $websites->count();
    }

    public function getLastBatchId(): ?string
    {
        return $this->lastBatchId;
    }

    public function checkWebsite(Website $website, ?string $batchId = null): void
    {
        $statusCode = null;
        $isUp = false;
        $errorType = null;
        $errorMessage = null;
        $hasSuspiciousContent = false;
        $suspiciousKeywords = null;
        $suspiciousUrls = null;

        $start = microtime(true);

        try {
            $response = Http::timeout(10)->withoutVerifying()->get($website->url);
            $statusCode = $response->status();
            $isUp = $response->successful();

            if (!$isUp) {
                $errorType = 'http_error';
                $errorMessage = 'Server merespons dengan kode ' . $statusCode . '.';
            }

            [$hasSuspiciousContent, $suspiciousKeywords, $suspiciousUrls] = $this->scanForSuspiciousContent(
                $response->body(),
                $website->url
            );
        } catch (ConnectionException $e) {
            [$errorType, $errorMessage] = $this->classifyConnectionError($e->getMessage());
        } catch (\Throwable $e) {
            $errorType = 'unknown_error';
            $errorMessage = 'Terjadi kesalahan tak terduga: ' . $e->getMessage();
            \Log::error('Monitoring check failed for ' . $website->url . ': ' . $e->getMessage());
        }

        $responseTimeMs = (int) round((microtime(true) - $start) * 1000);

        [$sslValid, $sslExpiresAt] = $this->checkSsl($website->url);

        MonitoringLog::create([
            'website_id' => $website->id,
            'status_code' => $statusCode,
            'is_up' => $isUp,
            'response_time_ms' => $responseTimeMs,
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'ssl_valid' => $sslValid,
            'ssl_expires_at' => $sslExpiresAt,
            'has_suspicious_content' => $hasSuspiciousContent,
            'suspicious_keywords' => $suspiciousKeywords,
            'suspicious_urls' => $suspiciousUrls,
            'checked_at' => now(),
            'batch_id' => $batchId,
        ]);
    }

    /**
     * Memindai HTML halaman untuk kata kunci judol yang aktif di database.
     *
     * @return array{0: bool, 1: ?string, 2: ?string} [ditemukan?, kata kunci yang cocok, URL situs judi]
     */
    private function scanForSuspiciousContent(string $html, string $siteUrl): array
    {
        $html = substr($html, 0, 500000);
        $normalized = strtolower($html);
        $matched = [];

        foreach ($this->getKeywords() as $keyword) {
            if (str_contains($normalized, strtolower($keyword))) {
                $matched[] = $keyword;
            }
        }

        if (empty($matched)) {
            return [false, null, null];
        }

        return [true, implode(', ', $matched), $this->extractSuspiciousUrls($html, $siteUrl)];
    }

    /**
     * Mengambil URL situs judi dari HTML: link/URL ke domain LAIN (bukan domain
     * website itu sendiri) yang URL-nya atau teks link-nya mengandung kata kunci.
     * Maksimal 5 URL, dipisah baris baru. Null jika tidak ditemukan.
     */
    private function extractSuspiciousUrls(string $html, string $siteUrl): ?string
    {
        $siteHost = $this->baseHost($siteUrl);

        $keywords = [];
        foreach ($this->getKeywords() as $keyword) {
            $normalizedKeyword = $this->normalizeForMatch($keyword);
            if (strlen($normalizedKeyword) >= 4) {
                $keywords[] = $normalizedKeyword;
            }
        }

        if (empty($keywords)) {
            return null;
        }

        // [url => teks link yang sudah dinormalisasi]
        $candidates = [];

        if (preg_match_all('~<a\s[^>]*?href\s*=\s*["\']([^"\']+)["\'][^>]*>(.*?)</a>~is', $html, $links, PREG_SET_ORDER)) {
            foreach ($links as $link) {
                $url = $this->cleanUrl($link[1]);
                if ($url !== null) {
                    $candidates[$url] = ($candidates[$url] ?? '') . $this->normalizeForMatch(strip_tags($link[2]));
                }
            }
        }

        if (preg_match_all('~https?://[^\s"\'<>()\[\]\\\\]+~i', $html, $rawUrls)) {
            foreach ($rawUrls[0] as $raw) {
                $url = $this->cleanUrl($raw);
                if ($url !== null && !isset($candidates[$url])) {
                    $candidates[$url] = '';
                }
            }
        }

        $found = [];

        foreach ($candidates as $url => $anchorText) {
            $host = $this->baseHost($url);

            if ($host === '') {
                continue;
            }

            // Lewati link ke website itu sendiri (termasuk subdomainnya)
            if ($siteHost !== '' && ($host === $siteHost || str_ends_with($host, '.' . $siteHost))) {
                continue;
            }

            $normalizedUrl = $this->normalizeForMatch($url);

            foreach ($keywords as $keyword) {
                if (str_contains($normalizedUrl, $keyword) || str_contains($anchorText, $keyword)) {
                    $found[$url] = true;
                    break;
                }
            }

            if (count($found) >= 5) {
                break;
            }
        }

        return empty($found) ? null : implode("\n", array_keys($found));
    }

    private function cleanUrl(string $raw): ?string
    {
        $url = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5));

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }

        $url = rtrim($url, '.,;');

        if (!preg_match('~^https?://[^/\s]+~i', $url)) {
            return null;
        }

        return substr($url, 0, 300);
    }

    private function baseHost(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return (string) preg_replace('/^www\./', '', $host);
    }

    private function normalizeForMatch(string $text): string
    {
        return (string) preg_replace('/[^a-z0-9]/', '', strtolower($text));
    }

    private function getKeywords(): array
    {
        if ($this->keywords === null) {
            $this->keywords = SuspiciousKeyword::pluck('keyword')->all();
        }

        return $this->keywords;
    }

    /**
     * @return array{0: string, 1: string} [error_type, error_message]
     */
    private function classifyConnectionError(string $message): array
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'could not resolve host')) {
            return [
                'dns_error',
                'Domain tidak ditemukan. Kemungkinan URL salah ketik, domain belum diarahkan, atau domain sudah kedaluwarsa.',
            ];
        }

        if (str_contains($lower, 'connection refused')) {
            return [
                'connection_refused',
                'Server menolak koneksi. Kemungkinan layanan web di server sedang mati atau port tertutup.',
            ];
        }

        if (str_contains($lower, 'timed out') || str_contains($lower, 'timeout')) {
            return [
                'timeout',
                'Server tidak merespons dalam 10 detik. Kemungkinan server kelebihan beban atau jaringan lambat.',
            ];
        }

        if (str_contains($lower, 'ssl') || str_contains($lower, 'certificate')) {
            return [
                'ssl_error',
                'Gagal membuat koneksi aman ke server (masalah SSL saat menghubungi website).',
            ];
        }

        return [
            'connection_error',
            'Website tidak bisa dihubungi sama sekali. Detail: ' . $message,
        ];
    }

    private function checkSsl(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (!$host || !str_starts_with($url, 'https://')) {
            return [null, null];
        }

        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $client = @stream_socket_client(
                "ssl://{$host}:443",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$client) {
                return [false, null];
            }

            $params = stream_context_get_params($client);
            $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);

            fclose($client);

            if (!$cert || !isset($cert['validTo_time_t'])) {
                return [false, null];
            }

            $expiresAt = \Carbon\Carbon::createFromTimestamp($cert['validTo_time_t']);
            $isValid = $expiresAt->isFuture();

            return [$isValid, $expiresAt];
        } catch (\Throwable $e) {
            return [false, null];
        }
    }
}
