<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'status_code',
        'is_up',
        'response_time_ms',
        'error_type',
        'error_message',
        'ssl_valid',
        'ssl_expires_at',
        'has_suspicious_content',
        'suspicious_keywords',
        'suspicious_urls',
        'checked_at',
        'batch_id',
    ];

    protected $casts = [
        'is_up' => 'boolean',
        'ssl_valid' => 'boolean',
        'has_suspicious_content' => 'boolean',
        'ssl_expires_at' => 'datetime',
        'checked_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Terjemahan singkat untuk error_type, dipakai di tampilan.
     */
    public function errorTypeLabel(): ?string
    {
        return match ($this->error_type) {
            'dns_error' => 'Domain tidak ditemukan',
            'connection_refused' => 'Koneksi ditolak server',
            'timeout' => 'Time out (server terlalu lama merespons)',
            'ssl_error' => 'Gagal koneksi aman (SSL)',
            'http_error' => 'Server merespons dengan error',
            'connection_error' => 'Gagal terhubung ke server',
            'unknown_error' => 'Kesalahan tak terduga',
            default => null,
        };
    }
}
