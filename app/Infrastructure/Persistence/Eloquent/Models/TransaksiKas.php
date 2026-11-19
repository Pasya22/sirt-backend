<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKas extends Model
{
    protected $table = 'transaksi_kas';
    protected $fillable = ['tanggal', 'ket', 'kategori', 'tipe', 'nominal', 'bukti_path', 'iuran_pembayaran_id'];

    protected function casts(): array
    {
        return ['nominal' => 'integer'];
    }
}