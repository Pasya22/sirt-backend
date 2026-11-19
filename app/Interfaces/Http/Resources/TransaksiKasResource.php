<?php

namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TransaksiKasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal,
            'ket' => $this->ket,
            'kategori' => $this->kategori,
            'tipe' => $this->tipe,
            'nominal' => $this->nominal,
            'bukti' => $this->bukti_path ? Storage::url($this->bukti_path) : null,
        ];
    }
}