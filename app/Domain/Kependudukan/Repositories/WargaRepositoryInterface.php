<?php

namespace App\Domain\Kependudukan\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Warga;
use Illuminate\Support\Collection;

interface WargaRepositoryInterface
{
    public function search(?string $q, ?string $status, string $sort = 'asc'): Collection;
    public function findById(int $id): ?Warga;
    public function existsByNik(string $nik, ?int $ignoreId = null): bool;
    public function countAll(): int;
    public function countKepalaKeluarga(): int;
    public function save(Warga $warga): Warga;
    public function delete(Warga $warga): void;
}