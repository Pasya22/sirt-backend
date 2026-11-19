<?php
namespace App\Application\IdentityAccess\UseCases;

use App\Domain\IdentityAccess\Enums\Role;
use App\Domain\IdentityAccess\Repositories\UserRepositoryInterface;
use App\Domain\Shared\Contracts\AuditLoggerInterface;
use App\Domain\Shared\Exceptions\DomainException;
use App\Infrastructure\Persistence\Eloquent\Models\User;

final readonly class HapusPenggunaUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AuditLoggerInterface $audit,
    ) {}

    public function execute(int $targetId, User $actor): void
    {
        if ($targetId === $actor->id) {
            throw new DomainException('Anda tidak bisa menghapus akun sendiri.');
        }

        $target = $this->users->findById($targetId);
        if (! $target) {
            throw new DomainException('Pengguna tidak ditemukan.');
        }

        // Cek jumlah Ketua RT aktif di wilayah yang sama (desa + rw + rt)
        if (
            $target->role === Role::KETUA_RT->value &&
            $target->status === 'Aktif'
        ) {
            $jumlahKetuaRtWilayah = User::where('role', Role::KETUA_RT->value)
                ->where('status', 'Aktif')
                ->where('desa', $target->desa)
                ->where('rw', $target->rw)
                ->where('rt', $target->rt)
                ->count();

            if ($jumlahKetuaRtWilayah <= 1) {
                throw new DomainException('Harus ada minimal satu Ketua RT aktif di wilayah ini.');
            }
        }

// Cek jumlah Ketua RW aktif di wilayah yang sama (desa + rw)
        if (
            $target->role === Role::KETUA_RW->value &&
            $target->status === 'Aktif' &&
            ! empty($target->rw)
        ) {
            $jumlahKetuaRwWilayah = User::where('role', Role::KETUA_RW->value)
                ->where('status', 'Aktif')
                ->where('desa', $target->desa)
                ->where('rw', $target->rw)
                ->count();

            if ($jumlahKetuaRwWilayah <= 1) {
                throw new DomainException('Harus ada minimal satu Ketua RW aktif di wilayah ini.');
            }
        }

        $nama = $target->nama;
        $this->users->delete($target);
        $this->audit->log("{$actor->role} menghapus pengguna {$nama}", $actor->id);
    }
}
