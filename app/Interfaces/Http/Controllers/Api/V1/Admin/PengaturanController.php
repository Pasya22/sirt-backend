<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Domain\Pengaturan\Repositories\PengaturanRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class PengaturanController extends Controller
{
    public function __construct(private readonly PengaturanRepositoryInterface $repo) {}

    public function show(): JsonResponse
    {
        $p = $this->repo->get();
        return response()->json([
            'sapaan' => $p->sapaan,
            'headerImage' => $this->resolveImageUrl($p->header_image),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sapaan' => ['required', 'string', 'max:150'],
            'headerImage' => ['nullable', 'string', 'max:500'],
            'headerFile' => ['nullable', 'image', 'max:2048'],
        ]);

        $pengaturan = $this->repo->get();
        $pengaturan->sapaan = $validated['sapaan'];

        if ($request->hasFile('headerFile')) {
            if ($pengaturan->header_image && !str_starts_with($pengaturan->header_image, 'http')) {
                Storage::disk('public')->delete($pengaturan->header_image);
            }
            $pengaturan->header_image = $request->file('headerFile')->store('settings', 'public');
        } elseif (!empty($validated['headerImage'])) {
            $pengaturan->header_image = $validated['headerImage'];
        }

        $saved = $this->repo->save($pengaturan);

        return response()->json([
            'message' => 'Pengaturan tampilan berhasil disimpan!',
            'sapaan' => $saved->sapaan,
            'headerImage' => $this->resolveImageUrl($saved->header_image),
        ]);
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        return str_starts_with($path, 'http') ? $path : Storage::url($path);
    }
}