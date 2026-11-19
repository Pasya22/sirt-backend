<?php

namespace App\Domain\IdentityAccess\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function countActiveKetuaRt(): int;
    public function save(User $user): User;
    public function delete(User $user): void;
}