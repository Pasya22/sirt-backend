<?php

namespace App\Domain\Komunikasi\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Pengumuman;
use Illuminate\Support\Collection;

interface PengumumanRepositoryInterface
{
    public function all(bool $terkirimOnly = false): Collection;
    public function findById(int $id): ?Pengumuman;
    public function save(Pengumuman $pengumuman): Pengumuman;
    public function delete(Pengumuman $pengumuman): void;
}