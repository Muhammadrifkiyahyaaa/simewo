<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'skor_domain_resmi',
        'skor_ssl_aktif',
        'skor_uptime',
        'total_skor',
        'calculated_at',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
