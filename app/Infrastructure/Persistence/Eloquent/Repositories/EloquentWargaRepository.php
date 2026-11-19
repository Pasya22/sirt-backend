<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Kependudukan\Repositories\WargaRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Warga;
use Illuminate\Support\Collection;

final class EloquentWargaRepository implements WargaRepositoryInterface
{

    public function search(?string $q, ?string $status, string $sort = 'asc'): Collection
    {
        return Warga::query()
            ->when($q, function ($query) use ($q) {
                $search = '%' . trim($q) . '%';

                $query->where(function ($sub) use ($search) {
                    $sub->where('nama', 'ILIKE', $search)
                        ->orWhere('nik', 'LIKE', $search);
                });
            })
            ->when(
                $status && $status !== 'Semua',
                fn($query) => $query->where('status', $status)
            )
            ->orderBy('nama', $sort === 'desc' ? 'desc' : 'asc')
            ->get();
    }


    public function findById(int $id): ?Warga
    {
        return Warga::find($id);
    }

    public function existsByNik(string $nik, ?int $ignoreId = null): bool
    {
        return Warga::where('nik', $nik)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }

    public function countAll(): int
    {
        return Warga::count();
    }
    public function countKepalaKeluarga(): int
    {
        return Warga::where('is_kepala_keluarga', true)->count();
    }
    public function save(Warga $warga): Warga
    {
        $warga->save();
        return $warga;
    }
    public function delete(Warga $warga): void
    {
        $warga->delete();
    }
}
