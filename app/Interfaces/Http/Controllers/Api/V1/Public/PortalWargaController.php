<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Public;

use App\Domain\Kegiatan\Repositories\KegiatanRepositoryInterface;
use App\Domain\Keuangan\Repositories\KasRepositoryInterface;
use App\Domain\Organisasi\Repositories\StrukturRepositoryInterface;
use App\Domain\Pengaturan\Repositories\PengaturanRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Interfaces\Http\Resources\KegiatanResource;
use App\Interfaces\Http\Resources\StrukturNodeResource;
use App\Interfaces\Http\Resources\TransaksiKasResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

final class PortalWargaController extends Controller
{
    public function home(
        PengaturanRepositoryInterface $pengaturanRepo,
        KasRepositoryInterface $kasRepo,
        KegiatanRepositoryInterface $kegiatanRepo,
    ): JsonResponse {
        $p = $pengaturanRepo->get();
        $headerUrl = $p->header_image && !str_starts_with($p->header_image, 'http')
            ? Storage::url($p->header_image)
            : $p->header_image;

        $summary = $kasRepo->getSummary();
        $bulanIni = $kasRepo->getBulanan(now()->month, now()->year);

        return response()->json([
            'pengaturan' => [
                'sapaan' => $p->sapaan,
                'headerImage' => $headerUrl,
            ],
            'kas' => [
                'saldoAkhir' => $summary['saldoAkhir'],
                'masukBulanIni' => $bulanIni['totalMasuk'],
                'keluarBulanIni' => $bulanIni['totalKeluar'],
            ],
            'kegiatanTerdekat' => KegiatanResource::collection($kegiatanRepo->upcomingPublic(3)),
        ]);
    }

    public function kas(KasRepositoryInterface $kasRepo): JsonResponse
    {
        return response()->json([
            'summary' => $kasRepo->getSummary(),
            'chart6Bulan' => $kasRepo->getChart6Bulan(),
            'data' => TransaksiKasResource::collection($kasRepo->search()),
        ]);
    }

    public function kegiatan(KegiatanRepositoryInterface $kegiatanRepo): AnonymousResourceCollection
    {
        return KegiatanResource::collection($kegiatanRepo->search(null, null, true));
    }

    public function detailKegiatan(int $id, KegiatanRepositoryInterface $kegiatanRepo): KegiatanResource|JsonResponse
    {
        $item = $kegiatanRepo->findById($id);
        if (!$item || !$item->tampil_warga) {
            return response()->json(['message' => 'Kegiatan tidak ditemukan.'], 404);
        }
        return new KegiatanResource($item);
    }

    public function organisasi(StrukturRepositoryInterface $strukturRepo): JsonResponse
    {
        $nodes = $strukturRepo->all();

        return response()->json([
            'pengurusInti' => StrukturNodeResource::collection($nodes->where('kelompok', 'inti')->values()),
            'seksiSeksi' => StrukturNodeResource::collection($nodes->where('kelompok', 'seksi')->values()),
            'allNodes' => StrukturNodeResource::collection($nodes),
        ]);
    }
}
