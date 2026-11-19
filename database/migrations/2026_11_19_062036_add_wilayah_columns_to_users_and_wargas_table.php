<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lepas check constraint enum lama pada users.role jika dibuat via $table->enum() di PostgreSQL
        if (DB::getDriverName() === 'pgsql' && Schema::hasTable('users')) {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->change();

            if (! Schema::hasColumn('users', 'rt')) {
                $table->string('rt', 5)->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'rw')) {
                $table->string('rw', 5)->nullable()->after('rt');
            }
            if (! Schema::hasColumn('users', 'desa')) {
                $table->string('desa', 100)->default('Desa Sukadami')->after('rw');
            }
        });

        // Isi default rt='01' untuk pengurus tingkat RT yang sudah ada
        DB::table('users')
            ->whereNull('rt')
            ->whereIn('role', ['Ketua RT', 'Sekretaris', 'Bendahara', 'Admin'])
            ->update(['rt' => '01', 'rw' => '08', 'desa' => 'Desa Sukadami']);

        $wargaTable = Schema::hasTable('wargas') ? 'wargas' : 'warga';

        Schema::table($wargaTable, function (Blueprint $table) use ($wargaTable) {
            if (! Schema::hasColumn($wargaTable, 'rt')) {
                $table->string('rt', 5)->default('01')->after('blok');
            }
            if (! Schema::hasColumn($wargaTable, 'rw')) {
                $table->string('rw', 5)->default('08')->after('rt');
            }
            if (! Schema::hasColumn($wargaTable, 'desa')) {
                $table->string('desa', 100)->default('Desa Sukadami')->after('rw');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(['rt', 'rw', 'desa'], fn($c) => Schema::hasColumn('users', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });

        $wargaTable = Schema::hasTable('wargas') ? 'wargas' : 'warga';

        Schema::table($wargaTable, function (Blueprint $table) use ($wargaTable) {
            $cols = array_filter(['rt', 'rw', 'desa'], fn($c) => Schema::hasColumn($wargaTable, $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
