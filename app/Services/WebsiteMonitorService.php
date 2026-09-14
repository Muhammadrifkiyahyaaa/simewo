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

        $start = microtime(true);

        try {
            $response = Http::timeout(10)->withoutVerifying()->get($website->url);
            $statusCode = $response->status();
            $isUp = $response->successful();

            if (!$isUp) {
                $errorType = 'http_error';
                $errorMessage = 'Server merespons dengan kode ' . $statusCode . '.';
            }

            [$hasSuspiciousContent, $suspiciousKeywords] = $this->scanForSuspiciousContent($response->body());
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
            'checked_at' => now(),
            'batch_id' => $batchId,
        ]);
    }

    /**
     * Memindai HTML halaman untuk kata kunci judol yang aktif di database.
     *
     * @return array{0: bool, 1: ?string} [ditemukan?, daftar kata kunci yang cocok]
     */
    private function scanForSuspiciousContent(string $html): array
    {
        $normalized = strtolower(substr($html, 0, 500000));
        $matched = [];

        foreach ($this->getKeywords() as $keyword) {
            if (str_contains($normalized, strtolower($keyword))) {
                $matched[] = $keyword;
            }
        }

        if (empty($matched)) {
            return [false, null];
        }

        return [true, implode(', ', $matched)];
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
