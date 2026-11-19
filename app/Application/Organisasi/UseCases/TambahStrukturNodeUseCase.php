<?php

namespace App\Application\Organisasi\UseCases;

use App\Domain\Organisasi\Repositories\StrukturRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StrukturNode;

final class TambahStrukturNodeUseCase
{
    public function __construct(private readonly StrukturRepositoryInterface $struktur) {}

    public function execute(array $input): StrukturNode
    {
        $kelompok = $input['kelompok'] ?? 'seksi';

        return $this->struktur->create([
            'kelompok' => $kelompok,
            'jabatan' => $input['jabatan'],
            'nama' => $input['nama'],
            'inisial' => $this->buatInisial($input['nama']),
            'color' => $input['color'] ?? ($kelompok === 'inti' ? 'bg-primary' : 'bg-slate-700'),
            'parent_id' => $input['parentId'] ?? null,
            'x' => $input['x'] ?? 100,
            'y' => $input['y'] ?? ($kelompok === 'inti' ? 200 : 360),
        ]);
    }

    private function buatInisial(string $nama): string
    {
        $parts = preg_split('/\s+/', trim($nama)) ?: [];
        $inisial = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $inisial .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $inisial ?: 'RT';
    }
}