<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Kartu Keluarga (1 KK -> Banyak Anggota Keluarga)
        if (!Schema::hasTable('kartu_keluargas')) {
            Schema::create('kartu_keluargas', function (Blueprint $table) {
                $table->id();
                $table->string('no_kk', 16)->unique();
                $table->string('nama_kepala_keluarga', 120);
                $table->text('alamat')->nullable();
                $table->string('blok', 50)->nullable();
                $table->string('rt', 5)->nullable();
                $table->string('rw', 5)->nullable();
                $table->string('kode_pos', 10)->nullable();
                $table->string('desa', 100)->nullable();
                $table->string('kecamatan', 100)->nullable();
                $table->string('kabupaten', 100)->nullable();
                $table->string('provinsi', 100)->nullable();
                $table->timestamps();
            });
        }

        // 2. Perluas kolom wilayah pada tabel users (untuk Admin/Pengurus)
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'kode_pos')) {
                    $table->string('kode_pos', 10)->nullable()->after('desa');
                }
                if (!Schema::hasColumn('users', 'kecamatan')) {
                    $table->string('kecamatan', 100)->nullable()->after('kode_pos');
                }
                if (!Schema::hasColumn('users', 'kabupaten')) {
                    $table->string('kabupaten', 100)->nullable()->after('kecamatan');
                }
                if (!Schema::hasColumn('users', 'provinsi')) {
                    $table->string('provinsi', 100)->nullable()->after('kabupaten');
                }
            });
        }

        // 3. Perluas tabel warga agar terhubung ke Kartu Keluarga & memiliki data KTP + wilayah lengkap
        $wargaTable = Schema::hasTable('wargas') ? 'wargas' : 'warga';

        if (Schema::hasTable($wargaTable)) {
            Schema::table($wargaTable, function (Blueprint $table) use ($wargaTable) {
                if (!Schema::hasColumn($wargaTable, 'kartu_keluarga_id')) {
                    $table->foreignId('kartu_keluarga_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('kartu_keluargas')
                        ->nullOnDelete();
                }
                if (!Schema::hasColumn($wargaTable, 'no_kk')) {
                    $table->string('no_kk', 16)->nullable()->index()->after('nik');
                }
                if (!Schema::hasColumn($wargaTable, 'jenis_kelamin')) {
                    $table->string('jenis_kelamin', 15)->nullable()->after('nama'); // Laki-laki / Perempuan
                }
                if (!Schema::hasColumn($wargaTable, 'tempat_lahir')) {
                    $table->string('tempat_lahir', 80)->nullable()->after('jenis_kelamin');
                }
                if (!Schema::hasColumn($wargaTable, 'tanggal_lahir')) {
                    $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
                }
                if (!Schema::hasColumn($wargaTable, 'hubungan_keluarga')) {
                    $table->string('hubungan_keluarga', 50)->default('Kepala Keluarga')->after('is_kepala_keluarga');
                }
                if (!Schema::hasColumn($wargaTable, 'kode_pos')) {
                    $table->string('kode_pos', 10)->nullable()->after('desa');
                }
                if (!Schema::hasColumn($wargaTable, 'kecamatan')) {
                    $table->string('kecamatan', 100)->nullable()->after('kode_pos');
                }
                if (!Schema::hasColumn($wargaTable, 'kabupaten')) {
                    $table->string('kabupaten', 100)->nullable()->after('kecamatan');
                }
                if (!Schema::hasColumn($wargaTable, 'provinsi')) {
                    $table->string('provinsi', 100)->nullable()->after('kabupaten');
                }
            });
        }
    }

    public function down(): void
    {
        $wargaTable = Schema::hasTable('wargas') ? 'wargas' : 'warga';

        if (Schema::hasTable($wargaTable)) {
            Schema::table($wargaTable, function (Blueprint $table) use ($wargaTable) {
                if (Schema::hasColumn($wargaTable, 'kartu_keluarga_id')) {
                    $table->dropConstrainedForeignId('kartu_keluarga_id');
                }
                $cols = array_filter([
                    'no_kk', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
                    'hubungan_keluarga', 'kode_pos', 'kecamatan', 'kabupaten', 'provinsi',
                ], fn ($c) => Schema::hasColumn($wargaTable, $c));
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $cols = array_filter(['kode_pos', 'kecamatan', 'kabupaten', 'provinsi'], fn ($c) => Schema::hasColumn('users', $c));
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('kartu_keluargas');
    }
};
