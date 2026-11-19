<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Auth;

use App\Domain\IdentityAccess\Repositories\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Interfaces\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class AuthController extends Controller
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->users->findByEmail($credentials['email']);

        // Pesan disamakan agar tidak membocorkan email terdaftar
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Email atau password salah!'], 401);
        }

        if ($user->status !== 'Aktif') {
            return response()->json(['message' => 'Akun Anda nonaktif. Hubungi Ketua RT.'], 403);
        }

        $token = $user->createToken('sirt-panel')->plainTextToken;

        return response()->json([
            'message' => "Selamat datang, {$user->nama}!",
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Berhasil keluar.']);
    }
}