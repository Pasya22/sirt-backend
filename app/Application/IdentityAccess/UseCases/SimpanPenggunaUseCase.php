<?php

namespace App\Application\IdentityAccess\UseCases;

use App\Domain\IdentityAccess\Enums\Role;
use App\Domain\IdentityAccess\Repositories\UserRepositoryInterface;
use App\Domain\IdentityAccess\ValueObjects\IzinMatrix;
use App\Domain\Shared\Contracts\AuditLoggerInterface;
use App\Domain\Shared\Exceptions\DomainException;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\Hash;

final readonly class SimpanPenggunaUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AuditLoggerInterface $audit,
    ) {}

    public function execute(array $data, User $actor, ?int $targetId = null): User
    {
        $email = strtolower(trim($data['email']));
        $existing = $this->users->findByEmail($email);

        if ($existing && $existing->id !== $targetId) {
            throw new DomainException('Email sudah dipakai pengguna lain.');
        }

        $target = $targetId ? $this->users->findById($targetId) : new User();
        if ($targetId && !$target) {
            throw new DomainException('Pengguna tidak ditemukan.');
        }

        $role = Role::from($data['role']);
        $status = $data['status'];

        if ($target->exists && $target->id === $actor->id && $status === 'Nonaktif') {
            throw new DomainException('Anda tidak bisa menonaktifkan akun sendiri.');
        }

        if (
            $target->exists &&
            $target->role === Role::KETUA_RT->value &&
            ($role !== Role::KETUA_RT || $status === 'Nonaktif') &&
            $this->users->countActiveKetuaRt() <= 1
        ) {
            throw new DomainException('Harus ada minimal satu Ketua RT aktif.');
        }

        $izinMatrix = isset($data['izin'])
            ? new IzinMatrix($data['izin'], $role)
            : IzinMatrix::presetFor($role);

        $target->nama = trim($data['nama']);
        $target->email = $email;
        $target->role = $role->value;
        $target->status = $status;
        $target->izin = $izinMatrix->toArray();

        if (!empty($data['password'])) {
            $target->password = Hash::make($data['password']);
        }

        $saved = $this->users->save($target);
        $this->audit->log("{$actor->role} menyimpan data pengguna {$saved->nama}", $actor->id);

        return $saved;
    }
}