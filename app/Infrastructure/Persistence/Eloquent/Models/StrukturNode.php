<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class StrukturNode extends Model
{
    protected $fillable = ['kelompok', 'jabatan', 'nama', 'inisial', 'color', 'x', 'y', 'parent_id'];

    protected function casts(): array
    {
        return ['x' => 'integer', 'y' => 'integer'];
    }
}