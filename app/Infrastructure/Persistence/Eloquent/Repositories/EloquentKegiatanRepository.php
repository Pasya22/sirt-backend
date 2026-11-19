<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Kegiatan\Repositories\KegiatanRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Kegiatan;
use Illuminate\Support\Collection;

final class EloquentKegiatanRepository implements KegiatanRepositoryInterface
{
    public function search(?string $q, ?string $status, bool $publicOnly = false): Collection
    {
        return Kegiatan::query()
            ->when($publicOnly, fn($query) => $query->where('tampil_warga', true))
            ->when($q, fn($query) => $query->where(fn($sub) => $sub->where('judul', 'like', "%{$q}%")->orWhere('lokasi', 'like', "%{$q}%")))
            ->when($status && $status !== 'Semua', fn($query) => $query->where('status', $status))
            ->orderByDesc('tanggal')
            ->get();
    }

    public function findById(int $id): ?Kegiatan
    {
        return Kegiatan::find($id);
    }
    public function countAktif(): int
    {
        return Kegiatan::whereIn('status', ['Planning', 'Berjalan'])->count();
    }
    public function save(Kegiatan $kegiatan): Kegiatan
    {
        $kegiatan->save();
        return $kegiatan;
    }
    public function delete(Kegiatan $kegiatan): void
    {
        $kegiatan->delete();
    }
    public function upcomingPublic(int $limit = 3): Collection
    {
        return Kegiatan::where('tampil_warga', true)
            ->whereIn('status', ['Planning', 'Berjalan'])
            ->orderBy('tanggal')
            ->limit($limit)
            ->get();
    }
}
