<?php

namespace App\Domain\Keuangan\ValueObjects;

use App\Domain\Shared\Exceptions\DomainException;

final readonly class DetailIuran
{
    public function __construct(
        public int $keamanan,
        public int $kebersihan,
        public int $kasRt,
    ) {
        if ($this->keamanan < 0 || $this->kebersihan < 0 || $this->kasRt < 0) {
            throw new DomainException('Nominal rincian iuran tidak boleh negatif.');
        }
    }

    public static function standar(): self
    {
        return new self(keamanan: 50000, kebersihan: 30000, kasRt: 20000);
    }

    public static function kosong(): self
    {
        return new self(keamanan: 0, kebersihan: 0, kasRt: 0);
    }

    public function total(): int
    {
        return $this->keamanan + $this->kebersihan + $this->kasRt;
    }
}