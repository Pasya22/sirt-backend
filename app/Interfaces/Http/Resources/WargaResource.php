<?php

namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WargaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'nik' => $this->nik,
            'blok' => $this->blok,
            'status' => $this->status,
            'isKepalaKeluarga' => $this->is_kepala_keluarga,
        ];
    }
}
