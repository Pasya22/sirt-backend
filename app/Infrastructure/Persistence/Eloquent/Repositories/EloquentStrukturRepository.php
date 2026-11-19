<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Organisasi\Repositories\StrukturRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StrukturNode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class EloquentStrukturRepository implements StrukturRepositoryInterface
{
    public function all(): Collection
    {
        return StrukturNode::orderBy('y')->orderBy('x')->orderBy('id')->get();
    }

    public function findById(int $id): ?StrukturNode
    {
        return StrukturNode::find($id);
    }

    public function create(array $data): StrukturNode
    {
        return StrukturNode::create($data);
    }

    public function update(StrukturNode $node, array $data): StrukturNode
    {
        $node->update($data);
        return $node->fresh();
    }

    public function updateLayout(array $nodes): void
    {
        DB::transaction(function () use ($nodes) {
            foreach ($nodes as $item) {
                StrukturNode::where('id', $item['id'])->update([
                    'x' => $item['x'],
                    'y' => $item['y'],
                ]);
            }
        });
    }

    public function delete(StrukturNode $node): void
    {
        $node->delete();
    }
}