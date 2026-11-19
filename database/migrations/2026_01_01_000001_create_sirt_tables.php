<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ubah/sesuaikan tabel users bawaan Laravel
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'name')) {
                $table->renameColumn('name', 'nama');
            }
            $table->string('role')->default('Sekretaris')->after('email');
            $table->string('status')->default('Aktif')->after('role');
            $table->json('izin')->nullable()->after('status');
        });

        Schema::create('wargas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nik', 16)->unique();
            $table->string('blok', 20);
            $table->enum('status', ['Tetap', 'Kontrak'])->default('Tetap');
            $table->boolean('is_kepala_keluarga')->default(true);
            $table->timestamps();
        });

        // Pada tabel struktur_nodes:
        Schema::create('struktur_nodes', function (Blueprint $table) {
            $table->id();
            $table->enum('kelompok', ['inti', 'seksi'])->default('seksi');
            $table->string('jabatan');
            $table->string('nama');
            $table->string('inisial', 5);
            $table->string('color', 50)->default('bg-slate-700');
            $table->integer('x')->default(100);
            $table->integer('y')->default(100);
            $table->foreignId('parent_id')->nullable()->constrained('struktur_nodes')->nullOnDelete();
            $table->timestamps();
        });

        // Pada tabel kegiatans:
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori', 50);
            $table->enum('status', ['Planning', 'Berjalan', 'Selesai'])->default('Planning');
            $table->date('tanggal');
            $table->string('waktu', 10)->default('08.00');
            $table->string('lokasi');
            $table->unsignedBigInteger('estimasi')->default(0);
            $table->unsignedBigInteger('biaya_aktual')->default(0);
            $table->boolean('tampil_warga')->default(true);
            $table->text('deskripsi')->nullable();
            $table->string('cover_path')->nullable();
            $table->json('galeri_paths')->nullable();
            $table->timestamps();
        });

        Schema::create('iuran_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('wargas')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->string('bulan', 10); // Jan, Feb, ..., Agu, Sep, Okt, Nov, Des
            $table->enum('status', ['Lunas', 'Belum'])->default('Belum');
            $table->unsignedBigInteger('keamanan')->default(0);
            $table->unsignedBigInteger('kebersihan')->default(0);
            $table->unsignedBigInteger('kas_rt')->default(0);
            $table->timestamp('tanggal_bayar')->nullable();
            $table->timestamps();

            $table->unique(['warga_id', 'tahun', 'bulan']);
        });

        Schema::create('transaksi_kas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('ket');
            $table->string('kategori', 50);
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->unsignedBigInteger('nominal');
            $table->string('bukti_path')->nullable();
            $table->foreignId('iuran_pembayaran_id')->nullable()->constrained('iuran_pembayarans')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pengumumans', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('isi');
            $table->date('tanggal');
            $table->enum('status', ['Terkirim', 'Draf'])->default('Draf');
            $table->timestamp('dikirim_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pengaturans', function (Blueprint $table) {
            $table->id();
            $table->string('sapaan')->default('Selamat siang, Warga');
            $table->string('header_image')->nullable();
            $table->timestamps();
        });

        Schema::create('aktivitas_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pesan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_logs');
        Schema::dropIfExists('pengaturans');
        Schema::dropIfExists('pengumumans');
        Schema::dropIfExists('transaksi_kas');
        Schema::dropIfExists('iuran_pembayarans');
        Schema::dropIfExists('kegiatans');
        Schema::dropIfExists('struktur_nodes');
        Schema::dropIfExists('wargas');
    }
};
