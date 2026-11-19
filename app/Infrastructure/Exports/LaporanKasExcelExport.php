<?php

namespace App\Infrastructure\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

final class LaporanKasExcelExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(
        private readonly Collection $transaksi,
        private readonly string $periode,
    ) {}

    public function collection(): Collection
    {
        return $this->transaksi;
    }

    public function title(): string
    {
        return "Kas {$this->periode}";
    }

    public function headings(): array
    {
        return ['Tanggal', 'Keterangan', 'Kategori', 'Tipe', 'Nominal (Rp)'];
    }

    public function map($trx): array
    {
        return [
            $trx->tanggal,
            $trx->ket,
            $trx->kategori,
            $trx->tipe === 'masuk' ? 'Uang Masuk' : 'Uang Keluar',
            $trx->nominal,
        ];
    }
}