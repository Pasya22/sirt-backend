<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Application\IdentityAccess\UseCases\HapusPenggunaUseCase;
use App\Application\IdentityAccess\UseCases\SimpanPenggunaUseCase;
use App\Domain\IdentityAccess\Enums\Role;
use App\Domain\IdentityAccess\Repositories\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Interfaces\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

final class PenggunaController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly SimpanPenggunaUseCase $simpanPengguna,
        private readonly HapusPenggunaUseCase $hapusPengguna,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $actor = $request->user();
        $list = $this->users->all();

        if ($actor && $actor->role !== Role::ADMIN->value) {
            $list = $list->reject(fn($u) => $u->id === $actor->id)->values();
        }

        return UserResource::collection($list);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'role' => ['required', Rule::in(array_column(Role::cases(), 'value'))],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'desa' => ['nullable', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
            'password' => ['required', 'string', 'min:8'],
            'izin' => ['nullable', 'array'],
        ]);

        $user = $this->simpanPengguna->execute($validated, $request->user());

        return response()->json([
            'message' => 'Pengguna berhasil ditambahkan.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'role' => ['required', Rule::in(array_column(Role::cases(), 'value'))],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'desa' => ['nullable', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
            'password' => ['nullable', 'string', 'min:8'],
            'izin' => ['nullable', 'array'],
        ]);

        $user = $this->simpanPengguna->execute($validated, $request->user(), $id);

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui.',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->hapusPengguna->execute($id, $request->user());

        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
