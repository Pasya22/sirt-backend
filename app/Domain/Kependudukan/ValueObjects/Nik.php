<?php

namespace App\Domain\Kependudukan\ValueObjects;

use App\Domain\Shared\Exceptions\DomainException;

final readonly class Nik
{
    public string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if (!preg_match('/^\d{16}$/', $trimmed)) {
            throw new DomainException('NIK harus 16 digit angka.');
        }
        $this->value = $trimmed;
    }
}