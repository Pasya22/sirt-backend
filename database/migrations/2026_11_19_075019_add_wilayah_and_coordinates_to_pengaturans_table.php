<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Schema::hasTable('pengaturans') ? 'pengaturans' : 'pengaturan';

        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'rt')) {
                    $table->string('rt', 5)->nullable()->default('01');
                }
                if (!Schema::hasColumn($tableName, 'rw')) {
                    $table->string('rw', 5)->nullable()->default('08');
                }
                if (!Schema::hasColumn($tableName, 'desa')) {
                    $table->string('desa', 100)->nullable()->default('Desa Sukadami');
                }
                if (!Schema::hasColumn($tableName, 'kecamatan')) {
                    $table->string('kecamatan', 100)->nullable()->default('Cikarang Selatan');
                }
                if (!Schema::hasColumn($tableName, 'kabupaten')) {
                    $table->string('kabupaten', 100)->nullable()->default('Kabupaten Bekasi');
                }
                if (!Schema::hasColumn($tableName, 'provinsi')) {
                    $table->string('provinsi', 100)->nullable()->default('Jawa Barat');
                }
                if (!Schema::hasColumn($tableName, 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable();
                }
                if (!Schema::hasColumn($tableName, 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable();
                }
                if (!Schema::hasColumn($tableName, 'radius_meter')) {
                    $table->integer('radius_meter')->default(1000); // default radius 1 km
                }
            });
        }
    }

    public function down(): void
    {
        $tableName = Schema::hasTable('pengaturans') ? 'pengaturans' : 'pengaturan';

        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $cols = array_filter([
                    'rt', 'rw', 'desa', 'kecamatan', 'kabupaten', 'provinsi',
                    'latitude', 'longitude', 'radius_meter',
                ], fn ($c) => Schema::hasColumn($tableName, $c));
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
