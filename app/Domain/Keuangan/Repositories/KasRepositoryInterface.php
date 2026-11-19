<?php

namespace App\Domain\Keuangan\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\TransaksiKas;
use Illuminate\Support\Collection;

interface KasRepositoryInterface
{
    public function search(?string $q = null): Collection;
    public function findById(int $id): ?TransaksiKas;
    public function findByIuranId(int $iuranId): ?TransaksiKas;
    public function getSummary(): array;
    public function getBulanan(int $bulan, int $tahun): array;
    public function getChart6Bulan(): array;
    public function latest(int $limit = 5): Collection;
    public function save(TransaksiKas $kas): TransaksiKas;
    public function delete(TransaksiKas $kas): void;
}