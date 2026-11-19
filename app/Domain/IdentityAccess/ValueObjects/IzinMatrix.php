<?php
namespace App\Domain\IdentityAccess\ValueObjects;

use App\Domain\IdentityAccess\Enums\Aksi;
use App\Domain\IdentityAccess\Enums\MenuKey;
use App\Domain\IdentityAccess\Enums\Role;

final readonly class IzinMatrix
{
    /** @var array<string, array{lihat: bool, edit: bool, hapus: bool}> */
    private array $matrix;

    public function __construct(array $rawMatrix, ?Role $role = null)
    {
        $normalized = [];
        foreach (MenuKey::cases() as $menu) {
            $item  = $rawMatrix[$menu->value] ?? [];
            $edit  = (bool) ($item['edit'] ?? false);
            $hapus = (bool) ($item['hapus'] ?? false);
            $lihat = (bool) ($item['lihat'] ?? false);

            // Invariant: Edit dan Hapus butuh Lihat
            if ($edit || $hapus) {
                $lihat = true;
            }
            if (! $lihat) {
                $edit  = false;
                $hapus = false;
            }

            $normalized[$menu->value] = [
                'lihat' => $lihat,
                'edit'  => $edit,
                'hapus' => $hapus,
            ];
        }

        // Invariant: Ketua RT & Ketua RW tidak boleh terkunci dari menu Pengguna
        if (in_array($role, [Role::KETUA_RT, Role::KETUA_RW], true)) {
            $normalized[MenuKey::PENGGUNA->value] = ['lihat' => true, 'edit' => true, 'hapus' => true];
        }

        $this->matrix = $normalized;
    }

    public static function presetFor(Role $role): self
    {
        $baca      = ['lihat' => true, 'edit' => false, 'hapus' => false];
        $lihatEdit = ['lihat' => true, 'edit' => true, 'hapus' => false];
        $penuh     = ['lihat' => true, 'edit' => true, 'hapus' => true];

        $preset = match ($role) {
            Role::KETUA_RT, Role::KETUA_RW => [
                'dashboard'  => $baca, 'warga'    => $lihatEdit, 'organisasi' => $penuh,
                'kegiatan'   => $penuh, 'kas'     => $baca, 'iuran'           => $baca,
                'pengumuman' => $penuh, 'laporan' => $baca, 'pengaturan'      => $penuh,
                'pengguna'   => $penuh,
            ],
            Role::SEKRETARIS => [
                'dashboard' => $baca, 'warga'       => $penuh, 'organisasi' => $penuh,
                'kegiatan'  => $penuh, 'pengumuman' => $penuh, 'kas'        => $baca,
                'laporan'   => $baca,
            ],
            Role::BENDAHARA  => [
                'dashboard' => $baca, 'kegiatan' => $baca, 'kas' => $penuh,
                'iuran'     => $penuh, 'laporan' => $baca,
            ],
            Role::ADMIN      => array_fill_keys(
                array_map(fn(MenuKey $m) => $m->value, MenuKey::cases()),
                $penuh
            ),
        };

        return new self($preset, $role);
    }

    public function can(MenuKey $menu, Aksi $aksi): bool
    {
        return $this->matrix[$menu->value][$aksi->value] ?? false;
    }

    public function toArray(): array
    {
        return $this->matrix;
    }
}
