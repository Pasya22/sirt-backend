<?php

namespace App\Infrastructure\Logging;

use App\Domain\Shared\Contracts\AuditLoggerInterface;
use App\Infrastructure\Persistence\Eloquent\Models\AktivitasLog;

final class EloquentAuditLogger implements AuditLoggerInterface
{
    public function log(string $pesan, ?int $userId = null): void
    {
        AktivitasLog::create([
            'user_id' => $userId,
            'pesan' => $pesan,
        ]);
    }
}