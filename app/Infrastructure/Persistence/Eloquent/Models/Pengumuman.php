<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumumans';
    protected $fillable = ['judul', 'isi', 'tanggal', 'status', 'dikirim_at'];

    protected function casts(): array
    {
        return ['dikirim_at' => 'datetime'];
    }
}