<?php

namespace App\Services;

use App\Models\ComplianceScore;
use App\Models\MonitoringLog;
use App\Models\Website;

/**
 * Menghitung compliance score tiap website berdasarkan 3 kriteria berbobot:
 * uptime (50), SSL aktif (30), domain resmi .go.id (20) — total 100.
 *
 * Setiap perhitungan disimpan sebagai baris baru di compliance_scores
 * (riwayat, bukan menimpa data lama), sama seperti filosofi monitoring_logs.
 */
class ComplianceScoreService
{
    /** Bobot maksimal tiap komponen (total harus 100). */
    private const BOBOT_UPTIME = 50;
    private const BOBOT_SSL = 30;
    private const BOBOT_DOMAIN = 20;

    /** Jumlah hari data monitoring yang dipakai untuk menghitung persentase uptime. */
    private const PERIODE_UPTIME_HARI = 30;

    /**
     * Ambang batas "SSL akan kedaluwarsa" — disamakan dengan SslWarningController
     * supaya konsisten dengan fitur Peringatan SSL yang sudah ada.
     */
    private const SSL_WARNING_DAYS = 30;

    /** Batas bawah total skor untuk kategori "Baik". */
    private const AMBANG_BAIK = 85;

    /** Batas bawah total skor untuk kategori "Cukup" (di bawah ini = "Kurang"). */
    private const AMBANG_CUKUP = 60;

    /**
     * Hitung & simpan skor compliance untuk semua website yang punya data monitoring.
     * Return jumlah website yang berhasil dihitung.
     */
    public function calculateAll(): int
    {
        $count = 0;

        foreach (Website::all() as $website) {
            if ($this->calculateForWebsite($website)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Hitung & simpan skor compliance untuk satu website.
     * Return false kalau website belum punya data monitoring sama sekali dalam periode
     * (belum dihitung dulu, supaya tidak dianggap "buruk" padahal cuma belum sempat dicek).
     */
    public function calculateForWebsite(Website $website): bool
    {
        $periodeAwal = now()->subDays(self::PERIODE_UPTIME_HARI);

        $totalCek = $website->monitoringLogs()
            ->where('checked_at', '>=', $periodeAwal)
            ->count();

        if ($totalCek === 0) {
            return false;
        }

        $cekUp = $website->monitoringLogs()
            ->where('checked_at', '>=', $periodeAwal)
            ->where('is_up', true)
            ->count();

        $skorUptime = $this->hitungSkorUptime($cekUp, $totalCek);

        $logTerbaru = $website->monitoringLogs()->latest('checked_at')->first();
        $skorSsl = $this->hitungSkorSsl($logTerbaru);

        $skorDomain = $this->hitungSkorDomain($website->url);

        ComplianceScore::create([
            'website_id' => $website->id,
            'skor_domain_resmi' => $skorDomain,
            'skor_ssl_aktif' => $skorSsl,
            'skor_uptime' => $skorUptime,
            'total_skor' => $skorUptime + $skorSsl + $skorDomain,
            'calculated_at' => now(),
        ]);

        return true;
    }

    private function hitungSkorUptime(int $cekUp, int $totalCek): int
    {
        $persentase = ($cekUp / $totalCek) * 100;

        return (int) round(($persentase / 100) * self::BOBOT_UPTIME);
    }

    private function hitungSkorSsl(?MonitoringLog $log): int
    {
        if (!$log || !$log->ssl_valid) {
            return 0;
        }

        if (!$log->ssl_expires_at) {
            return self::BOBOT_SSL;
        }

        $sisaHari = now()->diffInDays($log->ssl_expires_at, false);

        // SSL valid tapi mendekati kedaluwarsa -> beri separuh poin sebagai sinyal peringatan,
        // bukan langsung nol (SSL masih aktif, cuma perlu segera diperpanjang).
        if ($sisaHari <= self::SSL_WARNING_DAYS) {
            return (int) round(self::BOBOT_SSL / 2);
        }

        return self::BOBOT_SSL;
    }

    private function hitungSkorDomain(string $url): int
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return str_ends_with($host, '.go.id') ? self::BOBOT_DOMAIN : 0;
    }

    /**
     * Label & kelas badge Bootstrap berdasarkan total skor.
     * Dipakai di view untuk menampilkan kategori Baik/Cukup/Kurang.
     *
     * @return array{label: string, badge: string}
     */
    public static function kategori(int $totalSkor): array
    {
        if ($totalSkor >= self::AMBANG_BAIK) {
            return ['label' => 'Baik', 'badge' => 'bg-success'];
        }

        if ($totalSkor >= self::AMBANG_CUKUP) {
            return ['label' => 'Cukup', 'badge' => 'bg-warning text-dark'];
        }

        return ['label' => 'Kurang', 'badge' => 'bg-danger'];
    }
}
