<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function terima(Request $request, int $id): RedirectResponse
    {
        $pesananId = $this->milik($request, $id);

        $berhasil = DB::transaction(function () use ($request, $id) {
            [$pembayaran, $pesanan] = $this->kunci($request, $id);

            if (! $this->bisaDiproses($pembayaran, $pesanan)) {
                return false;
            }

            $pembayaran->update(['status' => Payment::DITERIMA, 'diverifikasi_at' => now(), 'alasan_penolakan' => null]);
            $pesanan->update(['status' => Order::DIBAYAR]);

            return true;
        });

        return $this->hasil($pesananId, $berhasil, 'Pembayaran diterima. Pesanan ditandai sudah dibayar.');
    }

    public function tolak(Request $request, int $id): RedirectResponse
    {
        $pesananId = $this->milik($request, $id);

        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:300'],
        ], [
            'alasan.required' => 'Tulis alasan penolakan agar pembeli tahu apa yang perlu diperbaiki.',
        ]);

        $berhasil = DB::transaction(function () use ($request, $id, $data) {
            [$pembayaran, $pesanan] = $this->kunci($request, $id);

            if (! $this->bisaDiproses($pembayaran, $pesanan)) {
                return false;
            }

            $pembayaran->update(['status' => Payment::DITOLAK, 'diverifikasi_at' => now(), 'alasan_penolakan' => $data['alasan']]);
            $pesanan->update(['status' => Order::MENUNGGU_PEMBAYARAN]);

            return true;
        });

        return $this->hasil($pesananId, $berhasil, 'Pembayaran ditolak. Pembeli bisa mengunggah bukti yang baru.');
    }

    protected function milik(Request $request, int $id): int
    {
        return Payment::whereHas('order', fn ($query) => $query->where('shop_id', $request->user()->shop->id))
            ->findOrFail($id)
            ->order_id;
    }

    protected function kunci(Request $request, int $id): array
    {
        $pembayaran = Payment::whereHas('order', fn ($query) => $query->where('shop_id', $request->user()->shop->id))
            ->lockForUpdate()
            ->findOrFail($id);

        $pesanan = Order::lockForUpdate()->findOrFail($pembayaran->order_id);

        return [$pembayaran, $pesanan];
    }

    protected function bisaDiproses(Payment $pembayaran, Order $pesanan): bool
    {
        return $pembayaran->status === Payment::MENUNGGU && $pesanan->status === Order::MENUNGGU_VERIFIKASI;
    }

    protected function hasil(int $pesananId, bool $berhasil, string $pesan): RedirectResponse
    {
        $tujuan = redirect()->route('penjual.pesanan.show', $pesananId);

        return $berhasil
            ? $tujuan->with('pesan', $pesan)
            : $tujuan->with('galat', 'Pembayaran ini sudah diproses sebelumnya.');
    }
}
