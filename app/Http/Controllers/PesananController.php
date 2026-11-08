<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PesananService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        return view('pesanan.index', [
            'pesanan' => $request->user()->orders()->with(['shop', 'items'])->latest('id')->paginate(10),
        ]);
    }

    public function show(Request $request, int $id): View
    {
        return view('pesanan.show', [
            'pesanan' => $request->user()->orders()->with(['shop.kelompok', 'shop.user', 'items.review', 'payments', 'pembayaranTerakhir'])->findOrFail($id),
        ]);
    }

    public function batal(Request $request, int $id, PesananService $layanan): RedirectResponse
    {
        $request->user()->orders()->findOrFail($id);

        if (! $layanan->batalkan($id, $request->user()->id)) {
            return redirect()->route('pesanan.show', $id)->with('galat', 'Pesanan ini sudah tidak bisa dibatalkan.');
        }

        return redirect()->route('pesanan.show', $id)->with('pesan', 'Pesanan dibatalkan dan stok dikembalikan.');
    }

    public function diterima(Request $request, int $id): RedirectResponse
    {
        $request->user()->orders()->findOrFail($id);

        $berhasil = DB::transaction(function () use ($request, $id) {
            $pesanan = Order::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($id);

            if ($pesanan->status !== Order::DIKIRIM) {
                return false;
            }

            $pesanan->update(['status' => Order::SELESAI, 'selesai_at' => now()]);

            return true;
        });

        if (! $berhasil) {
            return redirect()->route('pesanan.show', $id)->with('galat', 'Pesanan ini belum bisa dikonfirmasi diterima.');
        }

        return redirect()->route('pesanan.show', $id)->with('pesan', 'Terima kasih! Pesananmu sudah ditandai selesai.');
    }
}
