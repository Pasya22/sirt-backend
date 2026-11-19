<?php

namespace App\Domain\IdentityAccess\Enums;

enum MenuKey: string
{
    case DASHBOARD = 'dashboard';
    case WARGA = 'warga';
    case ORGANISASI = 'organisasi';
    case KEGIATAN = 'kegiatan';
    case KAS = 'kas';
    case IURAN = 'iuran';
    case PENGUMUMAN = 'pengumuman';
    case LAPORAN = 'laporan';
    case PENGATURAN = 'pengaturan';
    case PENGGUNA = 'pengguna';
}