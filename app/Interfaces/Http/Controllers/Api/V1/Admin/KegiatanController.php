<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Domain\Kegiatan\Repositories\KegiatanRepositoryInterface;
use App\Domain\Shared\Contracts\AuditLoggerInterface;
use App\Domain\Shared\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\Kegiatan;
use App\Interfaces\Http\Resources\KegiatanResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

final class KegiatanController extends Controller
{
    public function __construct(
        private readonly KegiatanRepositoryInterface $repo,
        private readonly AuditLoggerInterface $audit,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $this->repo->search($request->query('search'), $request->query('status'));
        return KegiatanResource::collection($items);
    }

    public function store(Request $request): KegiatanResource
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:50'],
            'status' => ['required', Rule::in(['Planning', 'Berjalan', 'Selesai'])],
            'tanggal' => ['required', 'date'],
            'lokasi' => ['required', 'string', 'max:150'],
            'estimasi' => ['nullable', 'integer', 'min:0'],
            'tampilWarga' => ['sometimes', 'boolean'],
            'deskripsi' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'max:2048'],
            'galeri.*' => ['nullable', 'image', 'max:2048'],
            'waktu' => ['nullable', 'string', 'max:10'],
            'biayaAktual' => ['nullable', 'integer', 'min:0'],
        ]);

        $coverPath = $request->hasFile('cover')
            ? $request->file('cover')->store('kegiatan/cover', 'public')
            : null;

        $galeriPaths = [];
        if ($request->hasFile('galeri')) {
            foreach ($request->file('galeri') as $file) {
                $galeriPaths[] = $file->store('kegiatan/galeri', 'public');
            }
        }

        $kegiatan = $this->repo->save(new Kegiatan([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'status' => $validated['status'],
            'tanggal' => $validated['tanggal'],
            'lokasi' => $validated['lokasi'],
            'estimasi' => $validated['estimasi'] ?? 0,
            'tampil_warga' => $request->boolean('tampilWarga', true),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'cover_path' => $coverPath,
            'galeri_paths' => $galeriPaths,
            'waktu' => $validated['waktu'] ?? '08.00',
            'biaya_aktual' => $validated['biayaAktual'] ?? 0,
        ]));

        $this->audit->log("{$request->user()->role} menambahkan kegiatan {$kegiatan->judul}", $request->user()->id);
        return new KegiatanResource($kegiatan);
    }

    public function update(Request $request, int $id): KegiatanResource
    {
        $kegiatan = $this->repo->findById($id) ?? throw new DomainException('Kegiatan tidak ditemukan.');

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:50'],
            'status' => ['required', Rule::in(['Planning', 'Berjalan', 'Selesai'])],
            'tanggal' => ['required', 'date'],
            'lokasi' => ['required', 'string', 'max:150'],
            'estimasi' => ['nullable', 'integer', 'min:0'],
            'tampilWarga' => ['sometimes', 'boolean'],
            'deskripsi' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'max:2048'],
            'waktu' => ['nullable', 'string', 'max:10'],
            'biayaAktual' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('cover')) {
            if ($kegiatan->cover_path) {
                Storage::disk('public')->delete($kegiatan->cover_path);
            }
            $kegiatan->cover_path = $request->file('cover')->store('kegiatan/cover', 'public');
        }

        $kegiatan->fill([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'status' => $validated['status'],
            'tanggal' => $validated['tanggal'],
            'lokasi' => $validated['lokasi'],
            'estimasi' => $validated['estimasi'] ?? $kegiatan->estimasi,
            'tampil_warga' => $request->boolean('tampilWarga', $kegiatan->tampil_warga),
            'deskripsi' => $validated['deskripsi'] ?? $kegiatan->deskripsi,
            'waktu' => $validated['waktu'] ?? $kegiatan->waktu,
            'biaya_aktual' => $validated['biayaAktual'] ?? $kegiatan->biaya_aktual,
        ]);

        $saved = $this->repo->save($kegiatan);
        $this->audit->log("{$request->user()->role} mengubah data kegiatan {$saved->judul}", $request->user()->id);
        return new KegiatanResource($saved);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $kegiatan = $this->repo->findById($id) ?? throw new DomainException('Kegiatan tidak ditemukan.');
        if ($kegiatan->cover_path) {
            Storage::disk('public')->delete($kegiatan->cover_path);
        }
        $this->repo->delete($kegiatan);
        $this->audit->log("{$request->user()->role} menghapus kegiatan {$kegiatan->judul}", $request->user()->id);
        return response()->json(['message' => 'Kegiatan dihapus.']);
    }
}
