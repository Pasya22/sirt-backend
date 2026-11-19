<?php

namespace App\Application\Keuangan\UseCases;

use App\Domain\Keuangan\ValueObjects\DetailIuran;
use App\Domain\Shared\Exceptions\DomainException;

final readonly class BayarIuranMassalUseCase
{
    public function __construct(
        private UbahStatusIuranUseCase $ubahStatus,
    ) {}

    /** @param string[] $daftarBulan */
    public function execute(int $wargaId, int $tahun, array $daftarBulan, ?int $actorId = null): void
    {
        if (empty($daftarBulan)) {
            throw new DomainException('Pilih warga dan minimal satu bulan iuran.');
        }

        $standar = DetailIuran::standar();
        foreach (array_unique($daftarBulan) as $bulan) {
            $this->ubahStatus->execute($wargaId, $tahun, $bulan, 'Lunas', $standar, $actorId);
        }
    }
}