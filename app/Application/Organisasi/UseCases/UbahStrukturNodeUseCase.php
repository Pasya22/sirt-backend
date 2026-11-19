<?php

namespace App\Application\Organisasi\UseCases;

use App\Domain\Organisasi\Repositories\StrukturRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StrukturNode;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UbahStrukturNodeUseCase
{
    public function __construct(private readonly StrukturRepositoryInterface $struktur) {}

    public function execute(int $id, array $input): StrukturNode
    {
        $node = $this->struktur->findById($id);
        if (!$node) {
            throw (new ModelNotFoundException())->setModel(StrukturNode::class, [$id]);
        }

        $parentId = ($input['parentId'] ?? null) === $node->id ? null : ($input['parentId'] ?? null);

        return $this->struktur->update($node, [
            'kelompok' => $input['kelompok'],
            'jabatan' => $input['jabatan'],
            'nama' => $input['nama'],
            'inisial' => $this->buatInisial($input['nama']),
            'color' => $input['color'] ?? $node->color,
            'parent_id' => $parentId,
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