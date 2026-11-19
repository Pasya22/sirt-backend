<?php

namespace App\Domain\Shared\Contracts;

interface AuditLoggerInterface
{
    public function log(string $pesan, ?int $userId = null): void;
}