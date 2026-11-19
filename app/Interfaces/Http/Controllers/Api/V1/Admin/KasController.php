<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Domain\Keuangan\Repositories\KasRepositoryInterface;
use App\Domain\Shared\Contracts\AuditLoggerInterface;
use App\Domain\Shared\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\TransaksiKas;
use App\Interfaces\Http\Resources\TransaksiKasResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

final class KasController extends Controller
{
    public function __construct(
        private readonly KasRepositoryInterface $repo,
        private readonly AuditLoggerInterface $audit,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->repo->search($request->query('search'));
        return response()->json([
            'summary' => $this->repo->getSummary(),
            'data' => TransaksiKasResource::collection($items),
        ]);
    }

    public function store(Request $request): TransaksiKasResource
    {
        $validated = $request->validate([
            'tipe' => ['required', Rule::in(['masuk', 'keluar'])],
            'nominal' => ['required', 'integer', 'min:1'],
            'kategori' => ['required', 'string', 'max:50'],
            'tanggal' => ['required', 'date'],
            'ket' => ['required', 'string', 'max:255'],
            'bukti' => ['nullable', 'image', 'max:2048'],
        ]);

        $buktiPath = $request->hasFile('bukti')
            ? $request->file('bukti')->store('kas/bukti', 'public')
            : null;

        $trx = $this->repo->save(new TransaksiKas([
            'tipe' => $validated['tipe'],
            'nominal' => $validated['nominal'],
            'kategori' => $validated['kategori'],
            'tanggal' => $validated['tanggal'],
            'ket' => $validated['ket'],
            'bukti_path' => $buktiPath,
        ]));

        $this->audit->log("{$request->user()->role} menambahkan transaksi kas ({$trx->ket})", $request->user()->id);
        return new TransaksiKasResource($trx);
    }

    public function update(Request $request, int $id): TransaksiKasResource
    {
        $trx = $this->repo->findById($id) ?? throw new DomainException('Transaksi tidak ditemukan.');

        $validated = $request->validate([
            'tipe' => ['required', Rule::in(['masuk', 'keluar'])],
            'nominal' => ['required', 'integer', 'min:1'],
            'kategori' => ['required', 'string', 'max:50'],
            'tanggal' => ['required', 'date'],
            'ket' => ['required', 'string', 'max:255'],
            'bukti' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('bukti')) {
            if ($trx->bukti_path) {
                Storage::disk('public')->delete($trx->bukti_path);
            }
            $trx->bukti_path = $request->file('bukti')->store('kas/bukti', 'public');
        }

        $trx->fill([
            'tipe' => $validated['tipe'],
            'nominal' => $validated['nominal'],
            'kategori' => $validated['kategori'],
            'tanggal' => $validated['tanggal'],
            'ket' => $validated['ket'],
        ]);

        return new TransaksiKasResource($this->repo->save($trx));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $trx = $this->repo->findById($id) ?? throw new DomainException('Transaksi tidak ditemukan.');
        if ($trx->bukti_path) {
            Storage::disk('public')->delete($trx->bukti_path);
        }
        $this->repo->delete($trx);
        $this->audit->log("{$request->user()->role} menghapus transaksi kas ({$trx->ket})", $request->user()->id);
        return response()->json(['message' => 'Transaksi berhasil dihapus.']);
    }
}