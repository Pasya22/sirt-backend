<?php

namespace App\Domain\Kegiatan\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Kegiatan;
use Illuminate\Support\Collection;

interface KegiatanRepositoryInterface
{
    public function search(?string $q, ?string $status, bool $publicOnly = false): Collection;
    public function findById(int $id): ?Kegiatan;
    public function countAktif(): int;
    public function save(Kegiatan $kegiatan): Kegiatan;
    public function delete(Kegiatan $kegiatan): void;
    public function upcomingPublic(int $limit = 3): Collection;
}