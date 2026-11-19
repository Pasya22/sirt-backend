<?php
namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WargaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'nama'              => $this->nama,
            'nik'               => $this->nik,
            'no_kk'             => $this->no_kk,
            'jenis_kelamin'     => $this->jenis_kelamin,
            'tempat_lahir'      => $this->tempat_lahir,
            'tanggal_lahir'     => $this->tanggal_lahir?->format('Y-m-d'),
            'hubungan_keluarga' => $this->hubungan_keluarga ?? 'Kepala Keluarga',
            'blok'              => $this->blok,
            'rt'                => $this->rt,
            'rw'                => $this->rw,
            'desa'              => $this->desa,
            'kode_pos'          => $this->kode_pos,
            'kecamatan'         => $this->kecamatan,
            'kabupaten'         => $this->kabupaten,
            'provinsi'          => $this->provinsi,
            'status'            => $this->status,
            'isKepalaKeluarga'  => $this->is_kepala_keluarga,
            'anggotaKeluarga'   => $this->whenLoaded('kartuKeluarga', function () {
                return $this->kartuKeluarga?->anggota
                    ->where('id', '!=', $this->id)
                    ->values()
                    ->map(fn($a) => [
                        'id'                => $a->id,
                        'nama'              => $a->nama,
                        'nik'               => $a->nik,
                        'hubungan_keluarga' => $a->hubungan_keluarga,
                        'jenis_kelamin'     => $a->jenis_kelamin,
                    ]) ?? [];
            }, []),
        ];
    }
}
