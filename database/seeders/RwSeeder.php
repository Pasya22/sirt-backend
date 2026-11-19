<?php

namespace Database\Seeders;

use App\Domain\IdentityAccess\Enums\Role;
use App\Domain\IdentityAccess\ValueObjects\IzinMatrix;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RwSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'ketuarw08@sirutawa.id'],
            [
                'nama' => 'H. Ahmad Sudrajat',
                'password' => Hash::make('password123'),
                'role' => Role::KETUA_RW->value,
                'rt' => null,
                'rw' => '08',
                'desa' => 'Desa Sukadami',
                'status' => 'Aktif',
                'izin' => IzinMatrix::presetFor(Role::KETUA_RW)->toArray(),
            ]
        );
    }
}
