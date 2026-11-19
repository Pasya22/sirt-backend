<?php

namespace App\Application\IdentityAccess\UseCases;

use App\Domain\IdentityAccess\Enums\Role;
use App\Domain\IdentityAccess\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Collection;

final class DaftarPenggunaUseCase
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function execute(?User $actor): Collection
    {
        $list = $this->users->all();

        // Sembunyikan akun diri sendiri dari daftar pengguna kecuali role Admin
        if ($actor && $actor->role !== Role::ADMIN->value) {
            $list = $list->reject(fn(User $u) => $u->id === $actor->id)->values();
        }

        return $list;
    }
}
