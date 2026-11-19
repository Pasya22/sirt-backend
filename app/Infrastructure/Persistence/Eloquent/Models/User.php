<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\IdentityAccess\Enums\Aksi;
use App\Domain\IdentityAccess\Enums\MenuKey;
use App\Domain\IdentityAccess\Enums\Role;
use App\Domain\IdentityAccess\ValueObjects\IzinMatrix;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'nama', 'email', 'password', 'role',
        'rt', 'rw', 'desa', 'kode_pos', 'kecamatan', 'kabupaten', 'provinsi',
        'status', 'izin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'izin'     => 'array',
        ];
    }

    public function isTingkatRw(): bool
    {
        return $this->role === Role::KETUA_RW->value || (empty($this->rt) && ! empty($this->rw));
    }

    public function getIzinMatrix(): IzinMatrix
    {
        $role = Role::tryFrom($this->role);
        return new IzinMatrix($this->izin ?? [], $role);
    }

    public function canAccessMenu(MenuKey $menu, Aksi $aksi): bool
    {
        if ($this->status !== 'Aktif') {
            return false;
        }
        return $this->getIzinMatrix()->can($menu, $aksi);
    }
}
