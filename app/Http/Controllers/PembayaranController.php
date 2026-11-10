<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Support\PesanWa;
use App\Support\Rupiah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function store(Request $request, int $id): RedirectResponse
    {
        $pesananAwal = $request->user()->orders()->findOrFail($id);

        $request->merge([
            'jumlah_transfer' => preg_replace('/\D+/', '', (string) $request->input('jumlah_transfer')),
        ]);

        $data = $request->validate([
            'nama_pengirim' => ['required', 'string', 'max:100'],
            'jumlah_transfer' => ['required', 'integer', 'min:1'],
            'bukti' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'jumlah_transfer.required' => 'Isi nominal yang kamu transfer.',
            'jumlah_transfer.integer' => 'Nominal transfer harus berupa angka.',
        ]);

        if ((int) $data['jumlah_transfer'] !== (int) $pesananAwal->total) {
            return back()
                ->withInput($request->except('bukti'))
                ->withErrors([
                    'jumlah_transfer' => 'Nominal '.Rupiah::format((int) $data['jumlah_transfer']).' tidak sesuai dengan total pesanan '.Rupiah::format($pesananAwal->total).'. Periksa kembali nominal transfermu.',
                ]);
        }

        $path = $request->file('bukti')->store('bukti-transfer', 'local');

        $berhasil = DB::transaction(function () use ($request, $id, $data, $path) {
            $pesanan = Order::with('shop')
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->findOrFail($id);

            if (! $pesanan->bisaDibayar() || (int) $data['jumlah_transfer'] !== (int) $pesanan->total) {
                return false;
            }

            Payment::create([
                'order_id' => $pesanan->id,
                'nama_pengirim' => $data['nama_pengirim'],
                'jumlah' => (int) $data['jumlah_transfer'],
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

        $pesanan = Order::with(['items', 'shop.user', 'pembayaranTerakhir'])->findOrFail($id);
        $urlWa = PesanWa::untukPenjual($pesanan);

        if ($urlWa) {
            return redirect()->away($urlWa)->with('pesan', 'Bukti transfer terkirim dan nominalnya sesuai. Jika WhatsApp belum terbuka, tekan tombol Chat Penjual di bawah.');
        }

        return redirect()->route('pesanan.show', $id)->with('pesan', 'Bukti transfer terkirim dan nominalnya sesuai. Nomor WhatsApp penjual belum tersedia, silakan hubungi operator PEKKA.');
    }

    public function buktiTautan(int $id)
    {
        $pembayaran = Payment::findOrFail($id);

        abort_unless(Storage::disk('local')->exists($pembayaran->bukti), 404);

        return Storage::disk('local')->response($pembayaran->bukti);
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
