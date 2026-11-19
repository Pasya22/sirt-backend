<?php

namespace App\Application\Keuangan\Listeners;

use App\Domain\Keuangan\Events\IuranDilunasi;
use App\Domain\Keuangan\Repositories\KasRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\TransaksiKas;

final readonly class CatatKasDariIuranListener
{
    public function __construct(private KasRepositoryInterface $kasRepo) {}

    public function handle(IuranDilunasi $event): void
    {
        $kas = $this->kasRepo->findByIuranId($event->iuranPembayaranId) ?? new TransaksiKas();
        $kas->tanggal = now()->toDateString();
        $kas->ket = "Iuran {$event->namaWarga} ({$event->bulan} {$event->tahun})";
        $kas->kategori = 'Iuran Warga';
        $kas->tipe = 'masuk';
        $kas->nominal = $event->totalNominal;
        $kas->iuran_pembayaran_id = $event->iuranPembayaranId;

        $this->kasRepo->save($kas);
    }
}