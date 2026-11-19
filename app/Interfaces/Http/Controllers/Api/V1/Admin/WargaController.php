<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Domain\Kependudukan\Repositories\WargaRepositoryInterface;
use App\Domain\Kependudukan\ValueObjects\Nik;
use App\Domain\Shared\Contracts\AuditLoggerInterface;
use App\Domain\Shared\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\Warga;
use App\Interfaces\Http\Resources\WargaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

final class WargaController extends Controller
{
    public function __construct(
        private readonly WargaRepositoryInterface $repo,
        private readonly AuditLoggerInterface $audit,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $this->repo->search(
            $request->query('search'),
            $request->query('status'),
            $request->query('sort', 'asc')
        );
        return WargaResource::collection($data);
    }

    public function store(Request $request): WargaResource
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nik' => ['required', 'string'],
            'blok' => ['required', 'string', 'max:20'],
            'status' => ['required', Rule::in(['Tetap', 'Kontrak'])],
            'is_kepala_keluarga' => ['sometimes', 'boolean'],
        ]);

        $nik = new Nik($validated['nik']);
        if ($this->repo->existsByNik($nik->value)) {
            throw new DomainException('NIK sudah terdaftar.');
        }

        $warga = $this->repo->save(new Warga([
            'nama' => trim($validated['nama']),
            'nik' => $nik->value,
            'blok' => trim($validated['blok']),
            'status' => $validated['status'],
            'is_kepala_keluarga' => $validated['is_kepala_keluarga'] ?? true,
        ]));

        $this->audit->log("{$request->user()->role} menambah warga baru ({$warga->nama})", $request->user()->id);
        return new WargaResource($warga);
    }

    public function update(Request $request, int $id): WargaResource
    {
        $warga = $this->repo->findById($id) ?? throw new DomainException('Data warga tidak ditemukan.');

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nik' => ['required', 'string'],
            'blok' => ['required', 'string', 'max:20'],
            'status' => ['required', Rule::in(['Tetap', 'Kontrak'])],
            'is_kepala_keluarga' => ['sometimes', 'boolean'],
        ]);

        $nik = new Nik($validated['nik']);
        if ($this->repo->existsByNik($nik->value, $warga->id)) {
            throw new DomainException('NIK sudah terdaftar.');
        }

        $warga->fill([
            'nama' => trim($validated['nama']),
            'nik' => $nik->value,
            'blok' => trim($validated['blok']),
            'status' => $validated['status'],
            'is_kepala_keluarga' => $validated['is_kepala_keluarga'] ?? $warga->is_kepala_keluarga,
        ]);

        $saved = $this->repo->save($warga);
        $this->audit->log("{$request->user()->role} mengubah data warga ({$saved->nama})", $request->user()->id);
        return new WargaResource($saved);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $warga = $this->repo->findById($id) ?? throw new DomainException('Data warga tidak ditemukan.');
        $this->repo->delete($warga);
        $this->audit->log("{$request->user()->role} menghapus data warga ({$warga->nama})", $request->user()->id);
        return response()->json(['message' => 'Data warga dihapus.']);
    }
}