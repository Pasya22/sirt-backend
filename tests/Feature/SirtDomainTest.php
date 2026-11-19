<?php

use App\Domain\IdentityAccess\Enums\Aksi;
use App\Domain\IdentityAccess\Enums\MenuKey;
use App\Domain\IdentityAccess\Enums\Role;
use App\Domain\IdentityAccess\ValueObjects\IzinMatrix;
use App\Infrastructure\Persistence\Eloquent\Models\TransaksiKas;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\Warga;
use Illuminate\Support\Facades\Hash;

test('matriks izin otomatis mengaktifkan lihat jika edit dipilih dan mengunci menu pengguna untuk Ketua RT', function () {
    $matrix = new IzinMatrix([
        'warga' => ['lihat' => false, 'edit' => true, 'hapus' => false],
        'pengguna' => ['lihat' => false, 'edit' => false, 'hapus' => false],
    ], Role::KETUA_RT);

    expect($matrix->can(MenuKey::WARGA, Aksi::LIHAT))->toBeTrue()
        ->and($matrix->can(MenuKey::PENGGUNA, Aksi::EDIT))->toBeTrue();
});

test('pelunasan iuran otomatis mencatat pemasukan kas dan pembatalan menghapusnya', function () {
    $bendahara = User::create([
        'nama' => 'Dewi Sartika',
        'email' => 'bendahara@rt01.test',
        'password' => Hash::make('password123'),
        'role' => Role::BENDAHARA->value,
        'status' => 'Aktif',
        'izin' => IzinMatrix::presetFor(Role::BENDAHARA)->toArray(),
    ]);

    $warga = Warga::create([
        'nama' => 'Ahmad Fauzi',
        'nik' => '3216011001900099',
        'blok' => 'A/01',
        'status' => 'Tetap',
    ]);

    $this->actingAs($bendahara)
        ->postJson('/api/v1/admin/iuran/status', [
            'wargaId' => $warga->id,
            'tahun' => 2026,
            'bulan' => 'Nov',
            'statusBaru' => 'Lunas',
            'detail' => ['keamanan' => 50000, 'kebersihan' => 30000, 'kasRt' => 20000],
        ])
        ->assertOk();

    expect(TransaksiKas::where('nominal', 100000)->where('tipe', 'masuk')->exists())->toBeTrue();

    // Batalkan lunas -> kas otomatis dihapus
    $this->actingAs($bendahara)
        ->postJson('/api/v1/admin/iuran/status', [
            'wargaId' => $warga->id,
            'tahun' => 2026,
            'bulan' => 'Nov',
            'statusBaru' => 'Belum',
        ])
        ->assertOk();

    expect(TransaksiKas::where('nominal', 100000)->exists())->toBeFalse();
});

test('bendahara ditolak saat mencoba mengakses endpoint manajemen pengguna', function () {
    $bendahara = User::create([
        'nama' => 'Dewi Sartika',
        'email' => 'bendahara@rt01.test',
        'password' => Hash::make('password123'),
        'role' => Role::BENDAHARA->value,
        'status' => 'Aktif',
        'izin' => IzinMatrix::presetFor(Role::BENDAHARA)->toArray(),
    ]);

    $this->actingAs($bendahara)
        ->getJson('/api/v1/admin/pengguna')
        ->assertForbidden();
});