<?php

namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StrukturNodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'kelompok' => $this->kelompok,
            'jabatan' => $this->jabatan,
            'nama' => $this->nama,
            'inisial' => $this->inisial,
            'color' => $this->color,
            'x' => $this->x,
            'y' => $this->y,
            'parentId' => $this->parent_id ? (string) $this->parent_id : null,
        ];
    }
}