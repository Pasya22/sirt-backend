<?php

namespace App\Domain\Pengaturan\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Pengaturan;

interface PengaturanRepositoryInterface
{
    public function get(): Pengaturan;
    public function save(Pengaturan $pengaturan): Pengaturan;
}