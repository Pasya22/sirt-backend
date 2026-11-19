<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warga extends Model
{
    protected $fillable = ['nama', 'nik', 'blok', 'status', 'is_kepala_keluarga'];

    protected function casts(): array
    {
        return ['is_kepala_keluarga' => 'boolean'];
    }

    public function iurans(): HasMany
    {
        return $this->hasMany(IuranPembayaran::class, 'warga_id');
    }
}