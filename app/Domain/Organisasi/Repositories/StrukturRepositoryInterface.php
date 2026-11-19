<?php

namespace App\Domain\Organisasi\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\StrukturNode;
use Illuminate\Support\Collection;

interface StrukturRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?StrukturNode;

    public function create(array $data): StrukturNode;

    public function update(StrukturNode $node, array $data): StrukturNode;

    public function updateLayout(array $nodes): void;

    public function delete(StrukturNode $node): void;
}