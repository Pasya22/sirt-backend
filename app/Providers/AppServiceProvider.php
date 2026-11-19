<?php

namespace App\Providers;

use App\Application\Keuangan\Listeners\CatatKasDariIuranListener;
use App\Application\Keuangan\Listeners\HapusKasDariIuranListener;
use App\Domain\IdentityAccess\Repositories\UserRepositoryInterface;
use App\Domain\Kegiatan\Repositories\KegiatanRepositoryInterface;
use App\Domain\Kependudukan\Repositories\WargaRepositoryInterface;
use App\Domain\Keuangan\Events\IuranDibatalkan;
use App\Domain\Keuangan\Events\IuranDilunasi;
use App\Domain\Keuangan\Repositories\IuranRepositoryInterface;
use App\Domain\Keuangan\Repositories\KasRepositoryInterface;
use App\Domain\Komunikasi\Repositories\PengumumanRepositoryInterface;
use App\Domain\Organisasi\Repositories\StrukturRepositoryInterface;
use App\Domain\Pengaturan\Repositories\PengaturanRepositoryInterface;
use App\Domain\Shared\Contracts\AuditLoggerInterface;
use App\Infrastructure\Logging\EloquentAuditLogger;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentIuranRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentKasRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentKegiatanRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPengaturanRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPengumumanRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentStrukturRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentWargaRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditLoggerInterface::class, EloquentAuditLogger::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(WargaRepositoryInterface::class, EloquentWargaRepository::class);
        $this->app->bind(StrukturRepositoryInterface::class, EloquentStrukturRepository::class);
        $this->app->bind(KegiatanRepositoryInterface::class, EloquentKegiatanRepository::class);
        $this->app->bind(KasRepositoryInterface::class, EloquentKasRepository::class);
        $this->app->bind(IuranRepositoryInterface::class, EloquentIuranRepository::class);
        $this->app->bind(PengumumanRepositoryInterface::class, EloquentPengumumanRepository::class);
        $this->app->bind(PengaturanRepositoryInterface::class, EloquentPengaturanRepository::class);
    }

    public function boot(): void
    {
        Event::listen(IuranDilunasi::class, CatatKasDariIuranListener::class);
        Event::listen(IuranDibatalkan::class, HapusKasDariIuranListener::class);
    }
}