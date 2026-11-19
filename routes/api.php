<?php

use App\Interfaces\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Interfaces\Http\Controllers\Api\V1\Admin\IuranController;
use App\Interfaces\Http\Controllers\Api\V1\Admin\KasController;
use App\Interfaces\Http\Controllers\Api\V1\Admin\KegiatanController;
use App\Interfaces\Http\Controllers\Api\V1\Admin\LaporanController;
use App\Interfaces\Http\Controllers\Api\V1\Admin\PengaturanController;
use App\Interfaces\Http\Controllers\Api\V1\Admin\PenggunaController;
use App\Interfaces\Http\Controllers\Api\V1\Admin\PengumumanController;
use App\Interfaces\Http\Controllers\Api\V1\Admin\StrukturController;
use App\Interfaces\Http\Controllers\Api\V1\Admin\WargaController;
use App\Interfaces\Http\Controllers\Api\V1\Auth\AuthController;
use App\Interfaces\Http\Controllers\Api\V1\Public\PortalWargaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // --- Portal Warga (Publik, Tanpa Login) ---
    Route::prefix('public')->group(function () {
        Route::get('/home', [PortalWargaController::class, 'home']);
        Route::get('/kas', [PortalWargaController::class, 'kas']);
        Route::get('/kegiatan', [PortalWargaController::class, 'kegiatan']);
        Route::get('/kegiatan/{id}', [PortalWargaController::class, 'detailKegiatan']);
        Route::get('/organisasi', [PortalWargaController::class, 'organisasi']);
    });

    // --- Autentikasi ---
    Route::post('/auth/login', [AuthController::class, 'login']);

    // --- Panel Pengurus (Wajib Login + Matriks Izin Per Menu) ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::prefix('admin')->group(function () {
            Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard,lihat');

            // Data Warga
            Route::get('/warga', [WargaController::class, 'index'])->middleware('permission:warga,lihat');
            Route::post('/warga', [WargaController::class, 'store'])->middleware('permission:warga,edit');
            Route::put('/warga/{id}', [WargaController::class, 'update'])->middleware('permission:warga,edit');
            Route::delete('/warga/{id}', [WargaController::class, 'destroy'])->middleware('permission:warga,hapus');

            // Struktur Organisasi
            Route::get('/struktur', [StrukturController::class, 'index'])->middleware('permission:organisasi,lihat');
            Route::post('/struktur', [StrukturController::class, 'store'])->middleware('permission:organisasi,edit');
            Route::put('/struktur/layout', [StrukturController::class, 'updateLayout'])->middleware('permission:organisasi,edit');
            Route::put('/struktur/{id}', [StrukturController::class, 'update'])->middleware('permission:organisasi,edit');
            Route::delete('/struktur/{id}', [StrukturController::class, 'destroy'])->middleware('permission:organisasi,hapus');
            // Kegiatan (POST _method=PUT dipakai React jika ada unggah gambar)
            Route::get('/kegiatan', [KegiatanController::class, 'index'])->middleware('permission:kegiatan,lihat');
            Route::post('/kegiatan', [KegiatanController::class, 'store'])->middleware('permission:kegiatan,edit');
            Route::match(['put', 'post'], '/kegiatan/{id}', [KegiatanController::class, 'update'])->middleware('permission:kegiatan,edit');
            Route::delete('/kegiatan/{id}', [KegiatanController::class, 'destroy'])->middleware('permission:kegiatan,hapus');

            // Kas RT
            Route::get('/kas', [KasController::class, 'index'])->middleware('permission:kas,lihat');
            Route::post('/kas', [KasController::class, 'store'])->middleware('permission:kas,edit');
            Route::match(['put', 'post'], '/kas/{id}', [KasController::class, 'update'])->middleware('permission:kas,edit');
            Route::delete('/kas/{id}', [KasController::class, 'destroy'])->middleware('permission:kas,hapus');

            // Iuran Warga
            Route::get('/iuran', [IuranController::class, 'index'])->middleware('permission:iuran,lihat');
            Route::post('/iuran/status', [IuranController::class, 'ubahStatus'])->middleware('permission:iuran,lihat');
            Route::post('/iuran/massal', [IuranController::class, 'bayarMassal'])->middleware('permission:iuran,edit');

            // Pengumuman
            Route::get('/pengumuman', [PengumumanController::class, 'index'])->middleware('permission:pengumuman,lihat');
            Route::post('/pengumuman', [PengumumanController::class, 'store'])->middleware('permission:pengumuman,edit');
            Route::put('/pengumuman/{id}', [PengumumanController::class, 'update'])->middleware('permission:pengumuman,edit');
            Route::post('/pengumuman/{id}/kirim', [PengumumanController::class, 'kirim'])->middleware('permission:pengumuman,edit');
            Route::delete('/pengumuman/{id}', [PengumumanController::class, 'destroy'])->middleware('permission:pengumuman,hapus');

            // Laporan & Export PDF/Excel
            Route::get('/laporan', [LaporanController::class, 'index'])->middleware('permission:laporan,lihat');
            Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->middleware('permission:laporan,lihat');
            Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->middleware('permission:laporan,lihat');

            // Pengaturan Tampilan
            Route::get('/pengaturan', [PengaturanController::class, 'show'])->middleware('permission:pengaturan,lihat');
            Route::post('/pengaturan', [PengaturanController::class, 'update'])->middleware('permission:pengaturan,edit');

            // Manajemen Pengguna & Matriks Izin
            Route::get('/pengguna', [PenggunaController::class, 'index'])->middleware('permission:pengguna,lihat');
            Route::post('/pengguna', [PenggunaController::class, 'store'])->middleware('permission:pengguna,edit');
            Route::put('/pengguna/{id}', [PenggunaController::class, 'update'])->middleware('permission:pengguna,edit');
            Route::delete('/pengguna/{id}', [PenggunaController::class, 'destroy'])->middleware('permission:pengguna,hapus');
        });
    });
});
