<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class IuranPembayaran extends Model
{
    protected $fillable = [
        'warga_id', 'tahun', 'bulan', 'status',
        'keamanan', 'kebersihan', 'kas_rt', 'tanggal_bayar',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'keamanan' => 'integer',
            'kebersihan' => 'integer',
            'kas_rt' => 'integer',
            'tanggal_bayar' => 'datetime',
        ];
    }
}