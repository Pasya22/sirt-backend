<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $fillable = [
        'judul', 'kategori', 'status', 'tanggal', 'waktu', 'lokasi',
        'estimasi', 'biaya_aktual', 'tampil_warga', 'deskripsi', 'cover_path', 'galeri_paths',
    ];

    protected function casts(): array
    {
        return [
            'estimasi' => 'integer',
            'biaya_aktual' => 'integer',
            'tampil_warga' => 'boolean',
            'galeri_paths' => 'array',
        ];
    }
}