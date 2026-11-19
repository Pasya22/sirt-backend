<?php

namespace App\Domain\Komunikasi\Events;

final readonly class PengumumanDikirim
{
    public function __construct(
        public int $pengumumanId,
        public string $judul,
    ) {}
}