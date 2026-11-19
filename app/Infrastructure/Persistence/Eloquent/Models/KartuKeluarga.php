<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KartuKeluarga extends Model
{
    protected $fillable = [
        'no_kk', 'nama_kepala_keluarga', 'alamat', 'blok',
        'rt', 'rw', 'kode_pos', 'desa', 'kecamatan', 'kabupaten', 'provinsi',
    ];

    public function anggota(): HasMany
    {
        return $this->hasMany(Warga::class, 'kartu_keluarga_id');
    }
}
