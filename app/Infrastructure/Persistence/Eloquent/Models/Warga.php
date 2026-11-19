<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warga extends Model
{
    protected $fillable = [
        'kartu_keluarga_id', 'nama', 'nik', 'no_kk',
        'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'blok', 'rt', 'rw', 'desa', 'kode_pos', 'kecamatan', 'kabupaten', 'provinsi',
        'status', 'is_kepala_keluarga', 'hubungan_keluarga',
    ];

    public function kartuKeluarga()
    {
        return $this->belongsTo(KartuKeluarga::class, 'kartu_keluarga_id');
    }

    protected function casts(): array
    {
        return ['is_kepala_keluarga' => 'boolean'];
    }

    public function scopeForWilayah(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query;
        }

        if (! empty($user->rw)) {
            $query->where('rw', $user->rw);
        }

        // Pengurus RT hanya melihat RT-nya; pengurus RW (rt null) melihat seluruh RT di RW-nya
        if (! empty($user->rt)) {
            $query->where('rt', $user->rt);
        }

        return $query;
    }

    public function iurans(): HasMany
    {
        return $this->hasMany(IuranPembayaran::class, 'warga_id');
    }
}
