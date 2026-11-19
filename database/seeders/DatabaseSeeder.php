<?php

namespace Database\Seeders;

use App\Domain\IdentityAccess\Enums\Role;
use App\Domain\IdentityAccess\ValueObjects\IzinMatrix;
use App\Infrastructure\Persistence\Eloquent\Models\AktivitasLog;
use App\Infrastructure\Persistence\Eloquent\Models\IuranPembayaran;
use App\Infrastructure\Persistence\Eloquent\Models\Kegiatan;
use App\Infrastructure\Persistence\Eloquent\Models\Pengaturan;
use App\Infrastructure\Persistence\Eloquent\Models\Pengumuman;
use App\Infrastructure\Persistence\Eloquent\Models\StrukturNode;
use App\Infrastructure\Persistence\Eloquent\Models\TransaksiKas;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\Warga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        User::create([
            'nama' => 'Budi Santoso',
            'email' => 'ketua@rt01.test',
            'password' => $password,
            'role' => Role::KETUA_RT->value,
            'status' => 'Aktif',
            'izin' => IzinMatrix::presetFor(Role::KETUA_RT)->toArray(),
        ]);

        User::create([
            'nama' => 'Citra Lestari',
            'email' => 'sekretaris@rt01.test',
            'password' => $password,
            'role' => Role::SEKRETARIS->value,
            'status' => 'Aktif',
            'izin' => IzinMatrix::presetFor(Role::SEKRETARIS)->toArray(),
        ]);

        User::create([
            'nama' => 'Dewi Sartika',
            'email' => 'bendahara@rt01.test',
            'password' => $password,
            'role' => Role::BENDAHARA->value,
            'status' => 'Aktif',
            'izin' => IzinMatrix::presetFor(Role::BENDAHARA)->toArray(),
        ]);

        User::create([
            'nama' => 'Admin Sistem',
            'email' => 'admin@rt01.test',
            'password' => $password,
            'role' => Role::ADMIN->value,
            'status' => 'Aktif',
            'izin' => IzinMatrix::presetFor(Role::ADMIN)->toArray(),
        ]);

        $w1 = Warga::create(['nama' => 'Ahmad Fauzi', 'nik' => '3216011001900001', 'blok' => 'A/01', 'status' => 'Tetap', 'is_kepala_keluarga' => true]);
        $w2 = Warga::create(['nama' => 'Budi Santoso', 'nik' => '3216011001900002', 'blok' => 'A/02', 'status' => 'Tetap', 'is_kepala_keluarga' => true]);
        Warga::create(['nama' => 'Citra Lestari', 'nik' => '3216011001900003', 'blok' => 'B/04', 'status' => 'Kontrak', 'is_kepala_keluarga' => true]);
        Warga::create(['nama' => 'Dewi Sartika', 'nik' => '3216011001900004', 'blok' => 'B/07', 'status' => 'Tetap', 'is_kepala_keluarga' => true]);
        Warga::create(['nama' => 'Eko Prasetyo', 'nik' => '3216011001900005', 'blok' => 'C/02', 'status' => 'Kontrak', 'is_kepala_keluarga' => true]);

        // Struktur Organisasi (Inti + 4 Seksi)
        $ketua = StrukturNode::create(['kelompok' => 'inti', 'jabatan' => 'Ketua RT', 'nama' => 'Budi Santoso', 'inisial' => 'BS', 'color' => 'bg-primary', 'x' => 350, 'y' => 50]);
        StrukturNode::create(['kelompok' => 'inti', 'jabatan' => 'Wakil Ketua', 'nama' => 'Andi Wijaya', 'inisial' => 'AW', 'color' => 'bg-emerald-600', 'x' => 150, 'y' => 200, 'parent_id' => $ketua->id]);
        StrukturNode::create(['kelompok' => 'inti', 'jabatan' => 'Sekretaris', 'nama' => 'Citra Lestari', 'inisial' => 'CL', 'color' => 'bg-blue-600', 'x' => 350, 'y' => 200, 'parent_id' => $ketua->id]);
        StrukturNode::create(['kelompok' => 'inti', 'jabatan' => 'Bendahara', 'nama' => 'Dewi Sartika', 'inisial' => 'DS', 'color' => 'bg-orange-500', 'x' => 550, 'y' => 200, 'parent_id' => $ketua->id]);

        StrukturNode::create(['kelompok' => 'seksi', 'jabatan' => 'Seksi Keamanan', 'nama' => 'Eko Prasetyo', 'inisial' => 'EP', 'color' => 'bg-slate-700', 'x' => 100, 'y' => 350]);
        StrukturNode::create(['kelompok' => 'seksi', 'jabatan' => 'Seksi Kebersihan', 'nama' => 'Fajar Ramadhan', 'inisial' => 'FR', 'color' => 'bg-slate-700', 'x' => 300, 'y' => 350]);
        StrukturNode::create(['kelompok' => 'seksi', 'jabatan' => 'Seksi Humas', 'nama' => 'Gita Permata', 'inisial' => 'GP', 'color' => 'bg-slate-700', 'x' => 500, 'y' => 350]);
        StrukturNode::create(['kelompok' => 'seksi', 'jabatan' => 'Seksi Keagamaan', 'nama' => 'Hendra Gunawan', 'inisial' => 'HG', 'color' => 'bg-slate-700', 'x' => 700, 'y' => 350]);

        // Kegiatan dengan waktu, estimasi, biaya_aktual, & deskripsi
        Kegiatan::create([
            'judul' => 'Kerja Bakti Lingkungan',
            'kategori' => 'Kerja Bakti',
            'status' => 'Planning',
            'tanggal' => '2026-11-02',
            'waktu' => '07.00',
            'lokasi' => 'Lapangan RT',
            'estimasi' => 500000,
            'biaya_aktual' => 0,
            'tampil_warga' => true,
            'deskripsi' => 'Kegiatan kerja bakti rutin untuk membersihkan saluran air dan lingkungan sekitar RT 01.',
        ]);
        Kegiatan::create([
            'judul' => 'Rapat Bulanan Warga',
            'kategori' => 'Rapat',
            'status' => 'Berjalan',
            'tanggal' => '2026-11-08',
            'waktu' => '19.30',
            'lokasi' => 'Balai Warga',
            'estimasi' => 250000,
            'biaya_aktual' => 150000,
            'tampil_warga' => true,
            'deskripsi' => 'Pembahasan laporan kas bulanan dan agenda keamanan lingkungan.',
        ]);
        Kegiatan::create([
            'judul' => 'Pengajian Bersama',
            'kategori' => 'Keagamaan',
            'status' => 'Planning',
            'tanggal' => '2026-11-15',
            'waktu' => '20.00',
            'lokasi' => 'Musala RW 08',
            'estimasi' => 150000,
            'biaya_aktual' => 0,
            'tampil_warga' => true,
            'deskripsi' => 'Pengajian rutin bulanan warga RT 01 / RW 08.',
        ]);
        Kegiatan::create([
            'judul' => 'Peringatan HUT RI',
            'kategori' => 'Acara',
            'status' => 'Selesai',
            'tanggal' => '2026-08-17',
            'waktu' => '08.00',
            'lokasi' => 'Lapangan Utama',
            'estimasi' => 2500000,
            'biaya_aktual' => 2450000,
            'tampil_warga' => true,
            'deskripsi' => 'Lomba rakyat dan malam tirakatan warga RT 01.',
        ]);
        TransaksiKas::create(['tanggal' => '2026-10-28', 'ket' => 'Iuran Warga Oktober', 'kategori' => 'Iuran Warga', 'tipe' => 'masuk', 'nominal' => 2800000]);
        TransaksiKas::create(['tanggal' => '2026-10-25', 'ket' => 'Kebersihan Lingkungan', 'kategori' => 'Kebersihan', 'tipe' => 'keluar', 'nominal' => 350000]);
        TransaksiKas::create(['tanggal' => '2026-10-20', 'ket' => 'Donasi Warga', 'kategori' => 'Donasi', 'tipe' => 'masuk', 'nominal' => 400000]);

        foreach (['Agu', 'Sep', 'Okt'] as $bln) {
            IuranPembayaran::create([
                'warga_id' => $w1->id,
                'tahun' => 2026,
                'bulan' => $bln,
                'status' => 'Lunas',
                'keamanan' => 50000,
                'kebersihan' => 30000,
                'kas_rt' => 20000,
                'tanggal_bayar' => now(),
            ]);
        }
        foreach (['Agu', 'Sep'] as $bln) {
            IuranPembayaran::create([
                'warga_id' => $w2->id,
                'tahun' => 2026,
                'bulan' => $bln,
                'status' => 'Lunas',
                'keamanan' => 50000,
                'kebersihan' => 30000,
                'kas_rt' => 20000,
                'tanggal_bayar' => now(),
            ]);
        }

        Pengumuman::create(['judul' => 'Jadwal Kerja Bakti November', 'isi' => 'Diharapkan seluruh warga hadir pada hari Minggu pukul 07.00 WIB.', 'tanggal' => '2026-10-27', 'status' => 'Terkirim', 'dikirim_at' => now()]);
        Pengumuman::create(['judul' => 'Iuran Oktober Segera Jatuh Tempo', 'isi' => 'Mohon segera melunasi iuran bulanan kepada Bendahara RT.', 'tanggal' => '2026-10-24', 'status' => 'Draf']);

        Pengaturan::create([
            'sapaan' => 'Selamat siang, Warga',
            'header_image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&q=80&w=800',
        ]);

        AktivitasLog::create(['pesan' => 'Bendahara menambahkan transaksi kas']);
        AktivitasLog::create(['pesan' => 'Sekretaris mengubah data kegiatan']);
    }
}
