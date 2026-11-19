<?php

namespace App\Application\Organisasi\UseCases;

use App\Domain\Organisasi\Repositories\StrukturRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StrukturNode;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class HapusStrukturNodeUseCase
{
    public function __construct(private readonly StrukturRepositoryInterface $struktur) {}

    public function execute(int $id): void
    {
        $node = $this->struktur->findById($id);
        if (!$node) {
            throw (new ModelNotFoundException())->setModel(StrukturNode::class, [$id]);
        }

        $this->struktur->delete($node);
    }
}