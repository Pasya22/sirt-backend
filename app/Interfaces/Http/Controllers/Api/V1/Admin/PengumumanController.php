<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Application\Komunikasi\UseCases\KirimPengumumanUseCase;
use App\Domain\Komunikasi\Repositories\PengumumanRepositoryInterface;
use App\Domain\Shared\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\Pengumuman;
use App\Interfaces\Http\Resources\PengumumanResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

final class PengumumanController extends Controller
{
    public function __construct(
        private readonly PengumumanRepositoryInterface $repo,
        private readonly KirimPengumumanUseCase $kirimUseCase,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return PengumumanResource::collection($this->repo->all());
    }

    public function store(Request $request): PengumumanResource
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string'],
            'tanggal' => ['required', 'date'],
            'status' => ['required', Rule::in(['Draf', 'Terkirim'])],
        ]);

        $item = $this->repo->save(new Pengumuman([
            ...$validated,
            'dikirim_at' => $validated['status'] === 'Terkirim' ? now() : null,
        ]));

        return new PengumumanResource($item);
    }

    public function update(Request $request, int $id): PengumumanResource
    {
        $item = $this->repo->findById($id) ?? throw new DomainException('Pengumuman tidak ditemukan.');

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string'],
            'tanggal' => ['required', 'date'],
            'status' => ['required', Rule::in(['Draf', 'Terkirim'])],
        ]);

        $item->fill($validated);
        if ($validated['status'] === 'Terkirim' && !$item->dikirim_at) {
            $item->dikirim_at = now();
        }

        return new PengumumanResource($this->repo->save($item));
    }

    public function kirim(Request $request, int $id): JsonResponse
    {
        $this->kirimUseCase->execute($id, $request->user()->id);
        return response()->json(['message' => 'Notifikasi berhasil dikirim!']);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = $this->repo->findById($id) ?? throw new DomainException('Pengumuman tidak ditemukan.');
        $this->repo->delete($item);
        return response()->json(['message' => 'Pengumuman dihapus.']);
    }
}