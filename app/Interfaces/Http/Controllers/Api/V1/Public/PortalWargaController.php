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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

final class PortalWargaController extends Controller
{
    public function home(
        Request $request,
        PengaturanRepositoryInterface $pengaturanRepo,
        KasRepositoryInterface $kasRepo,
        KegiatanRepositoryInterface $kegiatanRepo,
    ): JsonResponse {
        $p         = $pengaturanRepo->get();
        $headerUrl = $p->header_image && ! str_starts_with($p->header_image, 'http')
            ? Storage::url($p->header_image)
            : $p->header_image;

        $summary  = $kasRepo->getSummary();
        $bulanIni = $kasRepo->getBulanan(now()->month, now()->year);

        // Wilayah default dari pengaturan sistem
        $wilayahAktif = [
            'rt'        => $p->rt ?? '01',
            'rw'        => $p->rw ?? '08',
            'desa'      => $p->desa ?? 'Desa Sukadami',
            'kecamatan' => $p->kecamatan ?? 'Cikarang Selatan',
            'kabupaten' => $p->kabupaten ?? 'Kabupaten Bekasi',
            'provinsi'  => $p->provinsi ?? 'Jawa Barat',
        ];

        $deteksiLokasi = null;
        $lat           = $request->query('lat');
        $lng           = $request->query('lng');

        if (is_numeric($lat) && is_numeric($lng)) {
            $deteksiLokasi = $this->deteksiWilayahDariKoordinat((float) $lat, (float) $lng, $p, $wilayahAktif);
        }

        return response()->json([
            'pengaturan'       => [
                'sapaan'      => $p->sapaan,
                'headerImage' => $headerUrl,
                'wilayah'     => $wilayahAktif,
            ],
            'lokasiTerdeteksi' => $deteksiLokasi,
            'kas'              => [
                'saldoAkhir'     => $summary['saldoAkhir'],
                'masukBulanIni'  => $bulanIni['totalMasuk'],
                'keluarBulanIni' => $bulanIni['totalKeluar'],
            ],
            'kegiatanTerdekat' => KegiatanResource::collection($kegiatanRepo->upcomingPublic(3)),
        ]);
    }

    private function deteksiWilayahDariKoordinat(float $lat, float $lng, $pengaturan, array $wilayahDefault): array
    {
        // Cache reverse geocode per ~100 meter (3 angka di belakang koma) selama 1 jam agar cepat & hemat request
        $cacheKey = 'geo_' . round($lat, 3) . '_' . round($lng, 3);

        $geo = Cache::remember($cacheKey, 3600, function () use ($lat, $lng) {
            try {
                $response = Http::withHeaders([
                    'User-Agent'      => 'SIRUTAWA-App/1.0 (contact@sirutawa.id)',
                    'Accept-Language' => 'id',
                ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                    'format'         => 'jsonv2',
                    'lat'            => $lat,
                    'lon'            => $lng,
                    'zoom'           => 16,
                    'addressdetails' => 1,
                ]);

                if ($response->successful()) {
                    return $response->json('address') ?? [];
                }
            } catch (\Throwable) {
                // Fallback jika API peta sedang lambat
            }
            return [];
        });

        $desaGps      = $geo['village'] ?? $geo['suburb'] ?? $geo['hamlet'] ?? null;
        $kecamatanGps = $geo['city_district'] ?? $geo['district'] ?? $geo['subdistrict'] ?? null;
        $kabupatenGps = $geo['city'] ?? $geo['regency'] ?? $geo['county'] ?? null;
        $provinsiGps  = $geo['state'] ?? null;

        // Cek apakah koordinat warga berada dalam radius wilayah RT/RW yang terdaftar di sistem
        $dalamRadius = false;
        $jarakMeter  = null;

        if (! empty($pengaturan->latitude) && ! empty($pengaturan->longitude)) {
            $jarakMeter = (int) round($this->hitungJarakMeter(
                $lat,
                $lng,
                (float) $pengaturan->latitude,
                (float) $pengaturan->longitude
            ));
            $dalamRadius = $jarakMeter <= ($pengaturan->radius_meter ?? 1000);
        } elseif ($desaGps && stripos($wilayahDefault['desa'], $desaGps) !== false) {
            // Jika admin belum set titik lat/lng, cocokkan berdasarkan nama Desa dari GPS
            $dalamRadius = true;
        }

        $rt        = $dalamRadius ? $wilayahDefault['rt'] : null;
        $rw        = $dalamRadius ? $wilayahDefault['rw'] : null;
        $desa      = $dalamRadius ? $wilayahDefault['desa'] : ($desaGps ?: $wilayahDefault['desa']);
        $kecamatan = $kecamatanGps ?: $wilayahDefault['kecamatan'];
        $kabupaten = $kabupatenGps ?: $wilayahDefault['kabupaten'];
        $provinsi  = $provinsiGps ?: $wilayahDefault['provinsi'];

        // Susun teks pesan lokasi lengkap
        $bagian = array_filter([
            $rt ? "RT {$rt}" : null,
            $rw ? "RW {$rw}" : null,
            $desa,
            $kecamatan ? "Kec. {$kecamatan}" : null,
            $kabupaten,
            $provinsi,
        ]);

        return [
            'dalamWilayahTerdaftar' => $dalamRadius,
            'jarakMeter'            => $jarakMeter,
            'rt'                    => $rt,
            'rw'                    => $rw,
            'desa'                  => $desa,
            'kecamatan'             => $kecamatan,
            'kabupaten'             => $kabupaten,
            'provinsi'              => $provinsi,
            'pesan'                 => 'Anda berada di wilayah ' . implode(', ', $bagian),
        ];
    }

    /** Rumus Haversine menghitung jarak 2 koordinat GPS dalam meter */
    private function hitungJarakMeter(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat        = deg2rad($lat2 - $lat1);
        $dLon        = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
         + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
         * sin($dLon / 2) * sin($dLon / 2);

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    public function kas(KasRepositoryInterface $kasRepo): JsonResponse
    {
        return response()->json([
            'summary'     => $kasRepo->getSummary(),
            'chart6Bulan' => $kasRepo->getChart6Bulan(),
            'data'        => TransaksiKasResource::collection($kasRepo->search()),
        ]);
    }

    public function kegiatan(KegiatanRepositoryInterface $kegiatanRepo): AnonymousResourceCollection
    {
        return KegiatanResource::collection($kegiatanRepo->search(null, null, true));
    }

    public function detailKegiatan(int $id, KegiatanRepositoryInterface $kegiatanRepo): KegiatanResource | JsonResponse
    {
        $item = $kegiatanRepo->findById($id);
        if (! $item || ! $item->tampil_warga) {
            return response()->json(['message' => 'Kegiatan tidak ditemukan.'], 404);
        }
        return new KegiatanResource($item);
    }

    public function organisasi(StrukturRepositoryInterface $strukturRepo): JsonResponse
    {
        $nodes = $strukturRepo->all();

        return response()->json([
            'pengurusInti' => StrukturNodeResource::collection($nodes->where('kelompok', 'inti')->values()),
            'seksiSeksi'   => StrukturNodeResource::collection($nodes->where('kelompok', 'seksi')->values()),
            'allNodes'     => StrukturNodeResource::collection($nodes),
        ]);
    }
}
