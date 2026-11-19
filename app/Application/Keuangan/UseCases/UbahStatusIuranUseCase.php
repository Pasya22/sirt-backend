<?php

namespace App\Application\Keuangan\UseCases;

use App\Domain\Kependudukan\Repositories\WargaRepositoryInterface;
use App\Domain\Keuangan\Events\IuranDibatalkan;
use App\Domain\Keuangan\Events\IuranDilunasi;
use App\Domain\Keuangan\Repositories\IuranRepositoryInterface;
use App\Domain\Keuangan\ValueObjects\DetailIuran;
use App\Domain\Shared\Contracts\AuditLoggerInterface;
use App\Domain\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;

final readonly class UbahStatusIuranUseCase
{
    public function __construct(
        private IuranRepositoryInterface $iuranRepo,
        private WargaRepositoryInterface $wargaRepo,
        private AuditLoggerInterface $audit,
    ) {}

    public function execute(
        int $wargaId,
        int $tahun,
        string $bulan,
        string $statusBaru,
        ?DetailIuran $detail = null,
        ?int $actorId = null
    ): void {
        $warga = $this->wargaRepo->findById($wargaId)
            ?? throw new DomainException('Data warga tidak ditemukan.');

        if ($statusBaru === 'Lunas') {
            $rincian = $detail ?? DetailIuran::standar();
            if ($rincian->total() <= 0) {
                throw new DomainException('Total iuran tidak boleh Rp 0. Silakan isi nominal terlebih dahulu.');
            }
        } else {
            $rincian = DetailIuran::kosong();
        }

        DB::transaction(function () use ($warga, $tahun, $bulan, $statusBaru, $rincian, $actorId) {
            $record = $this->iuranRepo->findOrInit($warga->id, $tahun, $bulan);
            $record->status = $statusBaru;
            $record->keamanan = $rincian->keamanan;
            $record->kebersihan = $rincian->kebersihan;
            $record->kas_rt = $rincian->kasRt;
            $record->tanggal_bayar = $statusBaru === 'Lunas' ? now() : null;

            $saved = $this->iuranRepo->save($record);

            if ($statusBaru === 'Lunas') {
                event(new IuranDilunasi($saved->id, $warga->nama, $bulan, $tahun, $rincian->total()));
                $this->audit->log("Mencatat pelunasan iuran {$warga->nama} ({$bulan} {$tahun})", $actorId);
            } else {
                event(new IuranDibatalkan($saved->id, $warga->nama, $bulan));
                $this->audit->log("Membatalkan pelunasan iuran {$warga->nama} ({$bulan} {$tahun})", $actorId);
            }
        });
    }
}