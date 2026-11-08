<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function store(Request $request, int $id): RedirectResponse
    {
        $request->user()->orders()->findOrFail($id);

        $data = $request->validate([
            'nama_pengirim' => ['required', 'string', 'max:100'],
            'bukti' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('bukti')->store('bukti-transfer', 'local');

        $berhasil = DB::transaction(function () use ($request, $id, $data, $path) {
            $pesanan = Order::with('shop')
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->findOrFail($id);

            if (! $pesanan->bisaDibayar()) {
                return false;
            }

            Payment::create([
                'order_id' => $pesanan->id,
                'nama_pengirim' => $data['nama_pengirim'],
                'jumlah' => $pesanan->total,
                'bukti' => $path,
                'status' => Payment::MENUNGGU,
            ]);

            $pesanan->update(['status' => Order::MENUNGGU_VERIFIKASI]);

            return true;
        });

        if (! $berhasil) {
            Storage::disk('local')->delete($path);

            return redirect()->route('pesanan.show', $id)->with('galat', 'Bukti transfer tidak bisa dikirim untuk pesanan ini.');
        }

        return redirect()->route('pesanan.show', $id)->with('pesan', 'Bukti transfer terkirim. Sekarang hubungi penjual lewat tombol WhatsApp di bawah untuk membicarakan pengambilan atau pengiriman.');
    }

    public function bukti(Request $request, int $id)
    {
        $pembayaran = Payment::with('order.shop')->findOrFail($id);
        $idPengguna = $request->user()->id;

        abort_unless(
            $pembayaran->order->user_id === $idPengguna || $pembayaran->order->shop->user_id === $idPengguna,
            404
        );

        abort_unless(Storage::disk('local')->exists($pembayaran->bukti), 404);

        return Storage::disk('local')->response($pembayaran->bukti);
    }
}
