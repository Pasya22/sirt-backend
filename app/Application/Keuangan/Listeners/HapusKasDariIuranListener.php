<?php

namespace App\Application\Keuangan\Listeners;

use App\Domain\Keuangan\Events\IuranDibatalkan;
use App\Domain\Keuangan\Repositories\KasRepositoryInterface;

final readonly class HapusKasDariIuranListener
{
    public function __construct(private KasRepositoryInterface $kasRepo) {}

    public function handle(IuranDibatalkan $event): void
    {
        $kas = $this->kasRepo->findByIuranId($event->iuranPembayaranId);
        if ($kas) {
            $this->kasRepo->delete($kas);
        }
    }
}