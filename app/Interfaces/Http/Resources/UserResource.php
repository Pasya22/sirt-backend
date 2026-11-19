<?php
namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'nama'      => $this->nama,
            'email'     => $this->email,
            'role'      => $this->role,
            'rt'        => $this->rt,
            'rw'        => $this->rw,
            'desa'      => $this->desa,
            'kode_pos'  => $this->kode_pos,
            'kecamatan' => $this->kecamatan,
            'kabupaten' => $this->kabupaten,
            'provinsi'  => $this->provinsi,
            'status'    => $this->status,
            'izin'      => $this->getIzinMatrix()->toArray(),
        ];
    }
}
