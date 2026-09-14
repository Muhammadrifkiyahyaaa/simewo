<?php

namespace App\Console\Commands;

use App\Services\WebsiteMonitorService;
use Illuminate\Console\Command;
use App\Services\ComplianceScoreService;

class RunMonitoringCheck extends Command
{
    protected $signature = 'monitoring:run';

    protected $description = 'Menjalankan pengecekan uptime & SSL untuk semua website terdaftar (dipakai untuk penjadwalan otomatis)';

    public function handle(WebsiteMonitorService $monitor, ComplianceScoreService $complianceScoreService): int
    {
        $count = $monitor->checkAllWebsites();
        $complianceScoreService->calculateAll();

        $this->info("Pengecekan selesai untuk {$count} website. (" . now()->format('d-m-Y H:i:s') . ")");

        return self::SUCCESS;
    }
}
