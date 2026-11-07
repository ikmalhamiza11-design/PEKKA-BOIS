<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PesananController extends Controller
{
    public const FILTER = [
        'perlu_verifikasi' => [Order::MENUNGGU_VERIFIKASI],
        'perlu_diproses' => [Order::DIBAYAR, Order::DIPROSES],
        'dikirim' => [Order::DIKIRIM],
    ];

    public function index(Request $request): View
    {
        $status = array_key_exists((string) $request->query('status'), self::FILTER) ? $request->query('status') : null;

        $pesanan = $request->user()->shop->orders()
            ->with('items')
            ->when($status, fn ($query) => $query->whereIn('status', self::FILTER[$status]))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('penjual.pesanan.index', ['pesanan' => $pesanan, 'status' => $status]);
    }

    public function show(Request $request, int $id): View
    {
        $pesanan = $request->user()->shop->orders()
            ->with(['items', 'payments', 'pembayaranTerakhir'])
            ->findOrFail($id);

        return view('penjual.pesanan.show', ['pesanan' => $pesanan]);
    }

    public function proses(Request $request, int $id): RedirectResponse
    {
        $request->user()->shop->orders()->findOrFail($id);

        $berhasil = DB::transaction(function () use ($request, $id) {
            $pesanan = $request->user()->shop->orders()->lockForUpdate()->findOrFail($id);

            if ($pesanan->status !== Order::DIBAYAR) {
                return false;
            }

            $pesanan->update(['status' => Order::DIPROSES, 'diproses_at' => now()]);

            return true;
        });

        return $this->hasil($id, $berhasil, 'Pesanan ditandai sedang diproses.');
    }

    public function kirim(Request $request, int $id): RedirectResponse
    {
        $request->user()->shop->orders()->findOrFail($id);

        $data = $request->validate([
            'kurir' => ['required', 'string', 'max:50'],
            'nomor_resi' => ['nullable', 'string', 'max:60'],
        ], [
            'kurir.required' => 'Tulis nama kurir atau cara pengiriman.',
        ]);

        $berhasil = DB::transaction(function () use ($request, $id, $data) {
            $pesanan = $request->user()->shop->orders()->lockForUpdate()->findOrFail($id);

            if ($pesanan->status !== Order::DIPROSES) {
                return false;
            }

            $pesanan->update([
                'status' => Order::DIKIRIM,
                'kurir' => $data['kurir'],
                'nomor_resi' => $data['nomor_resi'] ?? null,
                'dikirim_at' => now(),
            ]);

            return true;
        });

        return $this->hasil($id, $berhasil, 'Pesanan ditandai sudah dikirim.');
    }

    protected function hasil(int $id, bool $berhasil, string $pesan): RedirectResponse
    {
        $tujuan = redirect()->route('penjual.pesanan.show', $id);

        return $berhasil
            ? $tujuan->with('pesan', $pesan)
            : $tujuan->with('galat', 'Status pesanan ini sudah berubah, muat ulang halaman.');
    }
}
