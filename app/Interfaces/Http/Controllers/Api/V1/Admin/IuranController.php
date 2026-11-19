<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Application\Keuangan\UseCases\BayarIuranMassalUseCase;
use App\Application\Keuangan\UseCases\UbahStatusIuranUseCase;
use App\Domain\IdentityAccess\Enums\Aksi;
use App\Domain\IdentityAccess\Enums\MenuKey;
use App\Domain\Keuangan\Repositories\IuranRepositoryInterface;
use App\Domain\Keuangan\ValueObjects\DetailIuran;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class IuranController extends Controller
{
    public function __construct(
        private readonly IuranRepositoryInterface $repo,
        private readonly UbahStatusIuranUseCase $ubahStatus,
        private readonly BayarIuranMassalUseCase $bayarMassal,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tahun = (int) $request->query('tahun', now()->year);
        return response()->json([
            'tahun' => $tahun,
            'data' => $this->repo->getMatrixByYear($tahun, $request->query('search')),
        ]);
    }

    public function ubahStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wargaId' => ['required', 'integer'],
            'tahun' => ['sometimes', 'integer', 'min:2020'],
            'bulan' => ['required', Rule::in(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'])],
            'statusBaru' => ['required', Rule::in(['Lunas', 'Belum'])],
            'detail' => ['nullable', 'array'],
            'detail.keamanan' => ['nullable', 'integer', 'min:0'],
            'detail.kebersihan' => ['nullable', 'integer', 'min:0'],
            'detail.kasRt' => ['nullable', 'integer', 'min:0'],
        ]);

        // Cek granular permission: Lunas butuh 'edit', membatalkan Lunas butuh 'hapus'
        $aksiDibutuhkan = $validated['statusBaru'] === 'Lunas' ? Aksi::EDIT : Aksi::HAPUS;
        if (!$request->user()->canAccessMenu(MenuKey::IURAN, $aksiDibutuhkan)) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengubah status ini.'], 403);
        }

        $detail = isset($validated['detail'])
            ? new DetailIuran(
                (int) ($validated['detail']['keamanan'] ?? 0),
                (int) ($validated['detail']['kebersihan'] ?? 0),
                (int) ($validated['detail']['kasRt'] ?? 0),
            )
            : DetailIuran::standar();

        $this->ubahStatus->execute(
            wargaId: (int) $validated['wargaId'],
            tahun: (int) ($validated['tahun'] ?? now()->year),
            bulan: $validated['bulan'],
            statusBaru: $validated['statusBaru'],
            detail: $detail,
            actorId: $request->user()->id
        );

        return response()->json([
            'message' => $validated['statusBaru'] === 'Lunas'
                ? 'Iuran berhasil dilunasi dan dicatat ke kas.'
                : 'Status lunas dibatalkan.',
        ]);
    }

    public function bayarMassal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wargaId' => ['required', 'integer'],
            'tahun' => ['sometimes', 'integer', 'min:2020'],
            'bulan' => ['required', 'array', 'min:1'],
            'bulan.*' => ['required', Rule::in(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'])],
        ]);

        $this->bayarMassal->execute(
            wargaId: (int) $validated['wargaId'],
            tahun: (int) ($validated['tahun'] ?? now()->year),
            daftarBulan: $validated['bulan'],
            actorId: $request->user()->id
        );

        $jumlah = count(array_unique($validated['bulan']));
        return response()->json([
            'message' => "Pembayaran iuran berhasil dicatat ({$jumlah} bulan).",
        ]);
    }
}