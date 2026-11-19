<?php

namespace App\Application\Komunikasi\UseCases;

use App\Domain\Komunikasi\Events\PengumumanDikirim;
use App\Domain\Komunikasi\Repositories\PengumumanRepositoryInterface;
use App\Domain\Shared\Contracts\AuditLoggerInterface;
use App\Domain\Shared\Exceptions\DomainException;

final readonly class KirimPengumumanUseCase
{
    public function __construct(
        private PengumumanRepositoryInterface $repo,
        private AuditLoggerInterface $audit,
    ) {}

    public function execute(int $id, ?int $actorId = null): void
    {
        $pengumuman = $this->repo->findById($id)
            ?? throw new DomainException('Pengumuman tidak ditemukan.');

        $pengumuman->status = 'Terkirim';
        $pengumuman->dikirim_at = now();
        $this->repo->save($pengumuman);

        event(new PengumumanDikirim($pengumuman->id, $pengumuman->judul));
        $this->audit->log("Mengirim notifikasi pengumuman: {$pengumuman->judul}", $actorId);
    }
}