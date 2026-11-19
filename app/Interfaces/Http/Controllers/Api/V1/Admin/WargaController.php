<?php
namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Domain\Kependudukan\Repositories\WargaRepositoryInterface;
use App\Domain\Kependudukan\ValueObjects\Nik;
use App\Domain\Shared\Contracts\AuditLoggerInterface;
use App\Domain\Shared\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\KartuKeluarga;
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
        $data->load('kartuKeluarga.anggota');
        return WargaResource::collection($data);
    }

    /** Endpoint lookup cepat saat pengurus mengetik 16 digit No. KK */
    public function cekKartuKeluarga(string $noKk): JsonResponse
    {
        $kk = KartuKeluarga::with('anggota')->where('no_kk', $noKk)->first();
        if (! $kk) {
            return response()->json(['found' => false, 'data' => null]);
        }

        return response()->json([
            'found' => true,
            'data'  => [
                'id'                   => $kk->id,
                'no_kk'                => $kk->no_kk,
                'nama_kepala_keluarga' => $kk->nama_kepala_keluarga,
                'alamat'               => $kk->alamat,
                'blok'                 => $kk->blok,
                'rt'                   => $kk->rt,
                'rw'                   => $kk->rw,
                'kode_pos'             => $kk->kode_pos,
                'desa'                 => $kk->desa,
                'kecamatan'            => $kk->kecamatan,
                'kabupaten'            => $kk->kabupaten,
                'provinsi'             => $kk->provinsi,
                'anggota'              => $kk->anggota->map(fn(Warga $w) => [
                    'id'                => $w->id,
                    'nama'              => $w->nama,
                    'nik'               => $w->nik,
                    'hubungan_keluarga' => $w->hubungan_keluarga,
                    'jenis_kelamin'     => $w->jenis_kelamin,
                    'tanggal_lahir'     => $w->tanggal_lahir?->format('Y-m-d'),
                ]),
            ],
        ]);
    }

    public function store(Request $request): WargaResource
    {
        $validated = $this->validateWarga($request);

        $nik = new Nik($validated['nik']);
        if ($this->repo->existsByNik($nik->value)) {
            throw new DomainException('NIK sudah terdaftar.');
        }

        $kk = $this->syncKartuKeluarga($validated, $request->user());

        $warga = $this->repo->save(new Warga([
            'kartu_keluarga_id'  => $kk?->id,
            'nama'               => trim($validated['nama']),
            'nik'                => $nik->value,
            'no_kk'              => $kk?->no_kk ?? ($validated['no_kk'] ?? null),
            'jenis_kelamin'      => $validated['jenis_kelamin'] ?? null,
            'tempat_lahir'       => trim((string) ($validated['tempat_lahir'] ?? '')) ?: null,
            'tanggal_lahir'      => $validated['tanggal_lahir'] ?? null,
            'blok'               => trim($validated['blok']),
            'rt'                 => $this->formatKode($validated['rt'] ?? $request->user()?->rt),
            'rw'                 => $this->formatKode($validated['rw'] ?? $request->user()?->rw),
            'desa'               => trim((string) ($validated['desa'] ?? $request->user()?->desa ?? '')) ?: null,
            'kode_pos'           => trim((string) ($validated['kode_pos'] ?? $request->user()?->kode_pos ?? '')) ?: null,
            'kecamatan'          => trim((string) ($validated['kecamatan'] ?? $request->user()?->kecamatan ?? '')) ?: null,
            'kabupaten'          => trim((string) ($validated['kabupaten'] ?? $request->user()?->kabupaten ?? '')) ?: null,
            'provinsi'           => trim((string) ($validated['provinsi'] ?? $request->user()?->provinsi ?? '')) ?: null,
            'status'             => $validated['status'],
            'is_kepala_keluarga' => ($validated['hubungan_keluarga'] ?? 'Kepala Keluarga') === 'Kepala Keluarga',
            'hubungan_keluarga'  => $validated['hubungan_keluarga'] ?? 'Kepala Keluarga',
        ]));

        $warga->load('kartuKeluarga.anggota');
        $this->audit->log("{$request->user()->role} menambah warga baru ({$warga->nama})", $request->user()->id);
        return new WargaResource($warga);
    }

    public function update(Request $request, int $id): WargaResource
    {
        $warga     = $this->repo->findById($id) ?? throw new DomainException('Data warga tidak ditemukan.');
        $validated = $this->validateWarga($request);

        $nik = new Nik($validated['nik']);
        if ($this->repo->existsByNik($nik->value, $warga->id)) {
            throw new DomainException('NIK sudah terdaftar.');
        }

        $kk = $this->syncKartuKeluarga($validated, $request->user());

        $warga->fill([
            'kartu_keluarga_id'  => $kk?->id,
            'nama'               => trim($validated['nama']),
            'nik'                => $nik->value,
            'no_kk'              => $kk?->no_kk ?? ($validated['no_kk'] ?? null),
            'jenis_kelamin'      => $validated['jenis_kelamin'] ?? null,
            'tempat_lahir'       => trim((string) ($validated['tempat_lahir'] ?? '')) ?: null,
            'tanggal_lahir'      => $validated['tanggal_lahir'] ?? null,
            'blok'               => trim($validated['blok']),
            'rt'                 => $this->formatKode($validated['rt'] ?? null),
            'rw'                 => $this->formatKode($validated['rw'] ?? null),
            'desa'               => trim((string) ($validated['desa'] ?? '')) ?: null,
            'kode_pos'           => trim((string) ($validated['kode_pos'] ?? '')) ?: null,
            'kecamatan'          => trim((string) ($validated['kecamatan'] ?? '')) ?: null,
            'kabupaten'          => trim((string) ($validated['kabupaten'] ?? '')) ?: null,
            'provinsi'           => trim((string) ($validated['provinsi'] ?? '')) ?: null,
            'status'             => $validated['status'],
            'is_kepala_keluarga' => ($validated['hubungan_keluarga'] ?? 'Kepala Keluarga') === 'Kepala Keluarga',
            'hubungan_keluarga'  => $validated['hubungan_keluarga'] ?? 'Kepala Keluarga',
        ]);

        $saved = $this->repo->save($warga);
        $saved->load('kartuKeluarga.anggota');
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

    private function validateWarga(Request $request): array
    {
        return $request->validate([
            'nama'              => ['required', 'string', 'max:100'],
            'nik'               => ['required', 'string', 'size:16'],
            'no_kk'             => ['nullable', 'string', 'size:16'],
            'jenis_kelamin'     => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'tempat_lahir'      => ['nullable', 'string', 'max:80'],
            'tanggal_lahir'     => ['nullable', 'date'],
            'hubungan_keluarga' => ['nullable', 'string', 'max:50'],
            'blok'              => ['required', 'string', 'max:20'],
            'rt'                => ['nullable', 'string', 'max:5'],
            'rw'                => ['nullable', 'string', 'max:5'],
            'desa'              => ['nullable', 'string', 'max:100'],
            'kode_pos'          => ['nullable', 'string', 'max:10'],
            'kecamatan'         => ['nullable', 'string', 'max:100'],
            'kabupaten'         => ['nullable', 'string', 'max:100'],
            'provinsi'          => ['nullable', 'string', 'max:100'],
            'status'            => ['required', Rule::in(['Tetap', 'Kontrak'])],
        ]);
    }

    private function syncKartuKeluarga(array $validated, $actor): ?KartuKeluarga
    {
        $noKk = trim((string) ($validated['no_kk'] ?? ''));
        if (strlen($noKk) !== 16) {
            return null;
        }

        $isKepala = ($validated['hubungan_keluarga'] ?? 'Kepala Keluarga') === 'Kepala Keluarga';
        $kk       = KartuKeluarga::firstOrNew(['no_kk' => $noKk]);

        if (! $kk->exists || $isKepala) {
            $kk->nama_kepala_keluarga = trim($validated['nama']);
        }

        $kk->blok      = trim($validated['blok']);
        $kk->rt        = $this->formatKode($validated['rt'] ?? $actor?->rt);
        $kk->rw        = $this->formatKode($validated['rw'] ?? $actor?->rw);
        $kk->desa      = trim((string) ($validated['desa'] ?? $actor?->desa ?? '')) ?: null;
        $kk->kode_pos  = trim((string) ($validated['kode_pos'] ?? $actor?->kode_pos ?? '')) ?: null;
        $kk->kecamatan = trim((string) ($validated['kecamatan'] ?? $actor?->kecamatan ?? '')) ?: null;
        $kk->kabupaten = trim((string) ($validated['kabupaten'] ?? $actor?->kabupaten ?? '')) ?: null;
        $kk->provinsi  = trim((string) ($validated['provinsi'] ?? $actor?->provinsi ?? '')) ?: null;
        $kk->save();

        return $kk;
    }

    private function formatKode(?string $val): ?string
    {
        $v = trim((string) $val);
        if ($v === '') {
            return null;
        }

        return ctype_digit($v) ? str_pad($v, 2, '0', STR_PAD_LEFT) : $v;
    }
}
