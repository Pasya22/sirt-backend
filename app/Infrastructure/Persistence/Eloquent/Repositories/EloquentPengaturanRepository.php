<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Pengaturan\Repositories\PengaturanRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Pengaturan;

final class EloquentPengaturanRepository implements PengaturanRepositoryInterface
{
    public function get(): Pengaturan
    {
        return Pengaturan::firstOrCreate([], [
            'sapaan' => 'Selamat siang, Warga',
            'header_image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&q=80&w=800',
        ]);
    }

    public function save(Pengaturan $pengaturan): Pengaturan { $pengaturan->save(); return $pengaturan; }
}