<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Keuangan\Repositories\IuranRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\IuranPembayaran;
use App\Infrastructure\Persistence\Eloquent\Models\Warga;
use Illuminate\Support\Collection;

final class EloquentIuranRepository implements IuranRepositoryInterface
{
    private const BULAN_LIST = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function getMatrixByYear(int $tahun, ?string $search = null): Collection
    {
        $wargas = Warga::with(['iurans' => fn ($q) => $q->where('tahun', $tahun)])
            ->where('is_kepala_keluarga', true)
            ->when($search, fn ($q) => $q->where('nama', 'like', "%{$search}%"))
            ->orderBy('blok')
            ->get();

        return $wargas->map(function (Warga $warga) {
            $byBulan = $warga->iurans->keyBy('bulan');
            $status = [];
            $detail = [];

            foreach (self::BULAN_LIST as $b) {
                $row = $byBulan->get($b);
                $status[$b] = $row?->status ?? 'Belum';
                $detail[$b] = [
                    'keamanan' => $row?->keamanan ?? 0,
                    'kebersihan' => $row?->kebersihan ?? 0,
                    'kasRt' => $row?->kas_rt ?? 0,
                ];
            }

            return [
                'id' => $warga->id,
                'nama' => $warga->nama,
                'blok' => $warga->blok,
                'status' => $status,
                'detail' => $detail,
            ];
        });
    }

    public function findOrInit(int $wargaId, int $tahun, string $bulan): IuranPembayaran
    {
        return IuranPembayaran::firstOrNew([
            'warga_id' => $wargaId,
            'tahun' => $tahun,
            'bulan' => $bulan,
        ]);
    }

    public function save(IuranPembayaran $iuran): IuranPembayaran { $iuran->save(); return $iuran; }
}