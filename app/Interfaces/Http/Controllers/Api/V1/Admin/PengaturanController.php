<?php
namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Domain\Pengaturan\Repositories\PengaturanRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class PengaturanController extends Controller
{
    public function __construct(private readonly PengaturanRepositoryInterface $repo)
    {}

    public function show(): JsonResponse
    {
        $p = $this->repo->get();
        return response()->json([
            'sapaan'      => $p->sapaan,
            'headerImage' => $this->resolveImageUrl($p->header_image),
            'wilayah'     => [
                'rt'        => $p->rt ?? '',
                'rw'        => $p->rw ?? '',
                'desa'      => $p->desa ?? '',
                'kode_pos'  => $p->kode_pos ?? '',
                'kecamatan' => $p->kecamatan ?? '',
                'kabupaten' => $p->kabupaten ?? '',
                'provinsi'  => $p->provinsi ?? '',
            ],
            'koordinat'   => [
                'latitude'     => $p->latitude,
                'longitude'    => $p->longitude,
                'radius_meter' => $p->radius_meter ?? 1000,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sapaan'       => ['required', 'string', 'max:150'],
            'headerImage'  => ['nullable', 'string', 'max:500'],
            'headerFile'   => ['nullable', 'image', 'max:2048'],
            'rt'           => ['nullable', 'string', 'max:5'],
            'rw'           => ['nullable', 'string', 'max:5'],
            'desa'         => ['nullable', 'string', 'max:100'],
            'kode_pos'     => ['nullable', 'string', 'max:10'],
            'kecamatan'    => ['nullable', 'string', 'max:100'],
            'kabupaten'    => ['nullable', 'string', 'max:100'],
            'provinsi'     => ['nullable', 'string', 'max:100'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meter' => ['nullable', 'integer', 'min:50', 'max:50000'],
        ]);

        $formatKode = fn(?string $val): ?string => ($v = trim((string) $val)) !== ''
            ? (ctype_digit($v) ? str_pad($v, 2, '0', STR_PAD_LEFT) : $v)
            : null;

        $pengaturan               = $this->repo->get();
        $pengaturan->sapaan       = $validated['sapaan'];
        $pengaturan->rt           = $formatKode($validated['rt'] ?? null);
        $pengaturan->rw           = $formatKode($validated['rw'] ?? null);
        $pengaturan->desa         = trim((string) ($validated['desa'] ?? '')) ?: null;
        $pengaturan->kode_pos     = trim((string) ($validated['kode_pos'] ?? '')) ?: null;
        $pengaturan->kecamatan    = trim((string) ($validated['kecamatan'] ?? '')) ?: null;
        $pengaturan->kabupaten    = trim((string) ($validated['kabupaten'] ?? '')) ?: null;
        $pengaturan->provinsi     = trim((string) ($validated['provinsi'] ?? '')) ?: null;
        $pengaturan->latitude     = $validated['latitude'] ?? null;
        $pengaturan->longitude    = $validated['longitude'] ?? null;
        $pengaturan->radius_meter = $validated['radius_meter'] ?? 1000;

        if ($request->hasFile('headerFile')) {
            if ($pengaturan->header_image && ! str_starts_with($pengaturan->header_image, 'http')) {
                Storage::disk('public')->delete($pengaturan->header_image);
            }
            $pengaturan->header_image = $request->file('headerFile')->store('settings', 'public');
        } elseif (! empty($validated['headerImage'])) {
            $pengaturan->header_image = $validated['headerImage'];
        }

        $saved = $this->repo->save($pengaturan);

        return response()->json([
            'message'     => 'Pengaturan tampilan & wilayah berhasil disimpan!',
            'sapaan'      => $saved->sapaan,
            'headerImage' => $this->resolveImageUrl($saved->header_image),
        ]);
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        return str_starts_with($path, 'http') ? $path : Storage::url($path);
    }
}
