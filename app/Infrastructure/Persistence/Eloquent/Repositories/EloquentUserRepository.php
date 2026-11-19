<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\IdentityAccess\Enums\Role;
use App\Domain\IdentityAccess\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Collection;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function all(): Collection { return User::orderBy('id')->get(); }
    public function findById(int $id): ?User { return User::find($id); }
    public function findByEmail(string $email): ?User { return User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first(); }
    public function countActiveKetuaRt(): int { return User::where('role', Role::KETUA_RT->value)->where('status', 'Aktif')->count(); }
    public function save(User $user): User { $user->save(); return $user; }
    public function delete(User $user): void { $user->delete(); }
}