<?php

namespace App\Domain\IdentityAccess\Enums;

enum Aksi: string
{
    case LIHAT = 'lihat';
    case EDIT = 'edit';
    case HAPUS = 'hapus';
}