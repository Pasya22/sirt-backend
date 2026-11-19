<?php

namespace App\Domain\Keuangan\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\IuranPembayaran;
use Illuminate\Support\Collection;

interface IuranRepositoryInterface
{
    public function getMatrixByYear(int $tahun, ?string $search = null): Collection;
    public function findOrInit(int $wargaId, int $tahun, string $bulan): IuranPembayaran;
    public function save(IuranPembayaran $iuran): IuranPembayaran;
}