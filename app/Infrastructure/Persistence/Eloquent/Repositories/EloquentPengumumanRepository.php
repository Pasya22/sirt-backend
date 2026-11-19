<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Komunikasi\Repositories\PengumumanRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Pengumuman;
use Illuminate\Support\Collection;

final class EloquentPengumumanRepository implements PengumumanRepositoryInterface
{
    public function all(bool $terkirimOnly = false): Collection
    {
        return Pengumuman::query()
            ->when($terkirimOnly, fn ($q) => $q->where('status', 'Terkirim'))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
    }

    public function findById(int $id): ?Pengumuman { return Pengumuman::find($id); }
    public function save(Pengumuman $pengumuman): Pengumuman { $pengumuman->save(); return $pengumuman; }
    public function delete(Pengumuman $pengumuman): void { $pengumuman->delete(); }
}