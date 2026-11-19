<?php

namespace App\Domain\Keuangan\Events;

final readonly class IuranDibatalkan
{
    public function __construct(
        public int $iuranPembayaranId,
        public string $namaWarga,
        public string $bulan,
    ) {}
}