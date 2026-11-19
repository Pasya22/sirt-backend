<?php

namespace App\Application\Organisasi\UseCases;

use App\Domain\Organisasi\Repositories\StrukturRepositoryInterface;

final class SimpanLayoutStrukturUseCase
{
    public function __construct(private readonly StrukturRepositoryInterface $struktur) {}

    public function execute(array $nodes): void
    {
        $this->struktur->updateLayout($nodes);
    }
}