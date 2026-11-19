<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class AktivitasLog extends Model
{
    protected $fillable = ['user_id', 'pesan'];
}