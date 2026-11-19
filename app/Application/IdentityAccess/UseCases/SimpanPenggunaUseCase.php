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
        $email    = strtolower(trim($data['email']));
        $existing = $this->users->findByEmail($email);

        if ($existing && $existing->id !== $targetId) {
            throw new DomainException('Email sudah dipakai pengguna lain.');
        }

        $target = $targetId ? $this->users->findById($targetId) : new User();
        if ($targetId && ! $target) {
            throw new DomainException('Pengguna tidak ditemukan.');
        }

        $role   = Role::from($data['role']);
        $status = $data['status'];

        if ($target->exists && $target->id === $actor->id && $status === 'Nonaktif') {
            throw new DomainException('Anda tidak bisa menonaktifkan akun sendiri.');
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

        $izinMatrix = isset($data['izin'])
            ? new IzinMatrix($data['izin'], $role)
            : IzinMatrix::presetFor($role);

        $formatKode = fn(?string $val): ?string => ($v = trim((string) $val)) !== ''
            ? (ctype_digit($v) ? str_pad($v, 2, '0', STR_PAD_LEFT) : $v)
            : null;

        $target->nama      = trim($data['nama']);
        $target->email     = $email;
        $target->role      = $role->value;
        $target->rt        = $formatKode($data['rt'] ?? null);
        $target->rw        = $formatKode($data['rw'] ?? null);
        $target->desa      = trim((string) ($data['desa'] ?? '')) ?: null;
        $target->kode_pos  = trim((string) ($data['kode_pos'] ?? '')) ?: null;
        $target->kecamatan = trim((string) ($data['kecamatan'] ?? '')) ?: null;
        $target->kabupaten = trim((string) ($data['kabupaten'] ?? '')) ?: null;
        $target->provinsi  = trim((string) ($data['provinsi'] ?? '')) ?: null;
        $target->status    = $status;
        $target->izin      = $izinMatrix->toArray();

        if (! empty($data['password'])) {
            $target->password = Hash::make($data['password']);
        }

        $saved = $this->users->save($target);
        $this->audit->log("{$actor->role} menyimpan data pengguna {$saved->nama}", $actor->id);

        return $saved;
    }
}
