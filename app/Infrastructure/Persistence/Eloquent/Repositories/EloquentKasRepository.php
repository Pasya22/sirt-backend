<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Keuangan\Repositories\KasRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\TransaksiKas;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class EloquentKasRepository implements KasRepositoryInterface
{
    public function search(?string $q = null): Collection
    {
        return TransaksiKas::query()
            ->when($q, fn ($query) => $query->where('ket', 'like', "%{$q}%"))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
    }

    public function findById(int $id): ?TransaksiKas { return TransaksiKas::find($id); }
    public function findByIuranId(int $iuranId): ?TransaksiKas { return TransaksiKas::where('iuran_pembayaran_id', $iuranId)->first(); }

    public function getSummary(): array
    {
        $totalMasuk = (int) TransaksiKas::where('tipe', 'masuk')->sum('nominal');
        $totalKeluar = (int) TransaksiKas::where('tipe', 'keluar')->sum('nominal');

        return [
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'saldoAkhir' => $totalMasuk - $totalKeluar,
        ];
    }

    public function getBulanan(int $bulan, int $tahun): array
    {
        $awal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $akhir = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $masukSebelum = (int) TransaksiKas::where('tanggal', '<', $awal)->where('tipe', 'masuk')->sum('nominal');
        $keluarSebelum = (int) TransaksiKas::where('tanggal', '<', $awal)->where('tipe', 'keluar')->sum('nominal');
        $saldoAwal = $masukSebelum - $keluarSebelum;

        $transaksi = TransaksiKas::whereBetween('tanggal', [$awal, $akhir])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $totalMasuk = (int) $transaksi->where('tipe', 'masuk')->sum('nominal');
        $totalKeluar = (int) $transaksi->where('tipe', 'keluar')->sum('nominal');

        $rincianKategori = $transaksi->groupBy(['tipe', 'kategori'])->map(
            fn ($groups) => $groups->map(fn ($items) => (int) $items->sum('nominal'))
        );

        return [
            'periode' => Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y'),
            'saldoAwal' => $saldoAwal,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'saldoAkhir' => $saldoAwal + $totalMasuk - $totalKeluar,
            'rincianKategori' => $rincianKategori,
            'transaksi' => $transaksi,
        ];
    }

    public function getChart6Bulan(): array
    {
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $awal = $date->copy()->startOfMonth()->toDateString();
            $akhir = $date->copy()->endOfMonth()->toDateString();

            $result[] = [
                'bulan' => $date->translatedFormat('M'),
                'masuk' => (int) TransaksiKas::whereBetween('tanggal', [$awal, $akhir])->where('tipe', 'masuk')->sum('nominal'),
                'keluar' => (int) TransaksiKas::whereBetween('tanggal', [$awal, $akhir])->where('tipe', 'keluar')->sum('nominal'),
            ];
        }
        return $result;
    }

    public function latest(int $limit = 5): Collection
    {
        return TransaksiKas::orderByDesc('tanggal')->orderByDesc('id')->limit($limit)->get();
    }

    public function save(TransaksiKas $kas): TransaksiKas { $kas->save(); return $kas; }
    public function delete(TransaksiKas $kas): void { $kas->delete(); }
}