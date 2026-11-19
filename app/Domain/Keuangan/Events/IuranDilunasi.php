<?php

namespace App\Domain\Keuangan\Events;

final readonly class IuranDilunasi
{
    public function __construct(
        public int $iuranPembayaranId,
        public string $namaWarga,
        public string $bulan,
        public int $tahun,
        public int $totalNominal,
    ) {}
}