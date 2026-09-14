<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_opd',
        'kategori',
        'pic_name',
        'pic_kontak',
    ];

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }
}
