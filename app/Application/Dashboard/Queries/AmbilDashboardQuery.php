<?php

namespace App\Application\Dashboard\Queries;

use App\Domain\Kegiatan\Repositories\KegiatanRepositoryInterface;
use App\Domain\Kependudukan\Repositories\WargaRepositoryInterface;
use App\Domain\Keuangan\Repositories\KasRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\AktivitasLog;
use App\Interfaces\Http\Resources\TransaksiKasResource;

final readonly class AmbilDashboardQuery
{
    public function __construct(
        private WargaRepositoryInterface $wargaRepo,
        private KasRepositoryInterface $kasRepo,
        private KegiatanRepositoryInterface $kegiatanRepo,
    ) {}

    public function execute(): array
    {
        $summaryKas = $this->kasRepo->getSummary();

        return [
            'stats' => [
                'totalWarga' => $this->wargaRepo->countAll(),
                'kepalaKeluarga' => $this->wargaRepo->countKepalaKeluarga(),
                'saldoKas' => $summaryKas['saldoAkhir'],
                'kegiatanAktif' => $this->kegiatanRepo->countAktif(),
            ],
            'kas6Bulan' => $this->kasRepo->getChart6Bulan(),
            'aktivitasTerbaru' => AktivitasLog::latest()->limit(5)->get()->map(fn ($a) => [
                'id' => $a->id,
                'pesan' => $a->pesan,
                'waktu' => $a->created_at?->diffForHumans(),
            ]),
            'transaksiTerbaru' => TransaksiKasResource::collection($this->kasRepo->latest(5)),
        ];
    }
}