<?php

namespace App\Interfaces\Http\Middleware;

use App\Domain\IdentityAccess\Enums\Aksi;
use App\Domain\IdentityAccess\Enums\MenuKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckMenuPermission
{
    public function handle(Request $request, Closure $next, string $menu, string $aksi): Response
    {
        $user = $request->user();

        if (!$user || $user->status !== 'Aktif') {
            return response()->json(['message' => 'Akun tidak aktif atau belum terautentikasi.'], 401);
        }

        if (!$user->canAccessMenu(MenuKey::from($menu), Aksi::from($aksi))) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk melakukan aksi ini.'], 403);
        }

        return $next($request);
    }
}