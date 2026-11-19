<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Domain\Keuangan\Repositories\KasRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Infrastructure\Exports\LaporanKasExcelExport;
use App\Interfaces\Http\Resources\TransaksiKasResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

final class LaporanController extends Controller
{
    public function __construct(private readonly KasRepositoryInterface $kasRepo) {}

    public function index(Request $request): JsonResponse
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);
        $laporan = $this->kasRepo->getBulanan($bulan, $tahun);

        return response()->json([
            ...$laporan,
            'transaksi' => TransaksiKasResource::collection($laporan['transaksi']),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);
        $laporan = $this->kasRepo->getBulanan($bulan, $tahun);

        $pdf = Pdf::loadView('pdf.laporan-kas', ['laporan' => $laporan]);
        return $pdf->download("laporan-kas-{$tahun}-{$bulan}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);
        $laporan = $this->kasRepo->getBulanan($bulan, $tahun);

        return Excel::download(
            new LaporanKasExcelExport($laporan['transaksi'], $laporan['periode']),
            "laporan-kas-{$tahun}-{$bulan}.xlsx"
        );
    }
}