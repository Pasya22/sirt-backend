<?php

namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class KegiatanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'kategori' => $this->kategori,
            'status' => $this->status,
            'tanggal' => $this->tanggal,
            'waktu' => $this->waktu,
            'lokasi' => $this->lokasi,
            'estimasi' => $this->estimasi,
            'aktual' => $this->biaya_aktual,
            'tampilWarga' => $this->tampil_warga,
            'deskripsi' => $this->deskripsi,
            'coverUrl' => $this->cover_path ? Storage::url($this->cover_path) : null,
            'galeri' => collect($this->galeri_paths ?? [])->map(fn ($p) => Storage::url($p))->values(),
        ];
    }
}