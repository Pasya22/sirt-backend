<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Application\Organisasi\UseCases\HapusStrukturNodeUseCase;
use App\Application\Organisasi\UseCases\SimpanLayoutStrukturUseCase;
use App\Application\Organisasi\UseCases\TambahStrukturNodeUseCase;
use App\Application\Organisasi\UseCases\UbahStrukturNodeUseCase;
use App\Domain\Organisasi\Repositories\StrukturRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Interfaces\Http\Resources\StrukturNodeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class StrukturController extends Controller
{
    public function __construct(
        private readonly StrukturRepositoryInterface $struktur,
        private readonly TambahStrukturNodeUseCase $tambahNode,
        private readonly UbahStrukturNodeUseCase $ubahNode,
        private readonly SimpanLayoutStrukturUseCase $simpanLayout,
        private readonly HapusStrukturNodeUseCase $hapusNode,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return StrukturNodeResource::collection($this->struktur->all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kelompok' => ['nullable', 'in:inti,seksi'],
            'jabatan' => ['required', 'string', 'max:100'],
            'nama' => ['required', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:50'],
            'parentId' => ['nullable', 'integer', 'exists:struktur_nodes,id'],
            'x' => ['nullable', 'integer', 'min:0'],
            'y' => ['nullable', 'integer', 'min:0'],
        ]);

        $node = $this->tambahNode->execute($validated);

        return response()->json([
            'message' => 'Jabatan berhasil ditambahkan.',
            'data' => new StrukturNodeResource($node),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'kelompok' => ['required', 'in:inti,seksi'],
            'jabatan' => ['required', 'string', 'max:100'],
            'nama' => ['required', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:50'],
            'parentId' => ['nullable', 'integer', 'exists:struktur_nodes,id'],
        ]);

        $node = $this->ubahNode->execute($id, $validated);

        return response()->json([
            'message' => 'Data pengurus berhasil diperbarui.',
            'data' => new StrukturNodeResource($node),
        ]);
    }

    public function updateLayout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nodes' => ['required', 'array'],
            'nodes.*.id' => ['required', 'integer', 'exists:struktur_nodes,id'],
            'nodes.*.x' => ['required', 'integer', 'min:0'],
            'nodes.*.y' => ['required', 'integer', 'min:0'],
        ]);

        $this->simpanLayout->execute($validated['nodes']);

        return response()->json(['message' => 'Layout struktur berhasil disimpan.']);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->hapusNode->execute($id);
        return response()->json(['message' => 'Jabatan berhasil dihapus.']);
    }
}
