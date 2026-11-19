<?php

namespace App\Domain\IdentityAccess\Enums;

enum Role: string
{
    case KETUA_RT = 'Ketua RT';
    case SEKRETARIS = 'Sekretaris';
    case BENDAHARA = 'Bendahara';
    case ADMIN = 'Admin';

    public function prefix(): string
    {
        return match ($this) {
            self::KETUA_RT => '/ketua',
            self::SEKRETARIS => '/sekretaris',
            self::BENDAHARA => '/bendahara',
            self::ADMIN => '/admin',
        };
    }
}