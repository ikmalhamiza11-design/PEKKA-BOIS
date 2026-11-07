<?php

namespace Tests\Concerns;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;

trait MembuatPesananBayar
{
    use MembuatDataContoh;

    protected function isiRekening(User $penjual): void
    {
        $penjual->shop->update([
            'nama_bank' => 'BRI',
            'nomor_rekening' => '1234567890',
            'atas_nama' => 'Siti Rohmah',
        ]);
    }

    protected function buatPesanan(User $pembeli, bool $denganRekening = true): Order
    {
        $penjual = $this->buatPenjual();

        if ($denganRekening) {
            $this->isiRekening($penjual);
        }

        $produk = $this->buatProduk($penjual->shop, null, ['stok' => 10, 'harga' => 10000]);
        $this->isiKeranjang($pembeli, $produk, 2);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout());

        return Order::latest('id')->first();
    }

    protected function kirimBukti(User $pembeli, Order $pesanan): void
    {
        $this->actingAs($pembeli)->post(route('pembayaran.store', $pesanan->id), [
            'nama_pengirim' => 'Budi Santoso',
            'bukti' => UploadedFile::fake()->image('bukti.jpg', 600, 800),
        ]);
    }

    protected function buatPesananDenganBukti(): array
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);
        $this->kirimBukti($pembeli, $pesanan);

        $pesanan = $pesanan->fresh();
        $pembayaran = Payment::where('order_id', $pesanan->id)->first();

        return [$pembeli, $pesanan->shop->user, $pesanan, $pembayaran];
    }

    protected function buatPesananDibayar(): array
    {
        [$pembeli, $penjual, $pesanan, $pembayaran] = $this->buatPesananDenganBukti();

        $this->actingAs($penjual)->patch(route('penjual.pembayaran.terima', $pembayaran->id));

        return [$pembeli, $penjual, $pesanan->fresh()];
    }
}
