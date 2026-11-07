<?php

namespace Tests\Feature\Penjual;

use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class SetujuiTokoTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    public function test_perintah_mengubah_status_toko_menjadi_terverifikasi(): void
    {
        $penjual = $this->buatPenjual(Shop::MENUNGGU);

        $this->artisan('toko:setujui', ['email' => $penjual->email])->assertSuccessful();

        $this->assertSame(Shop::TERVERIFIKASI, $penjual->shop->fresh()->status);
    }

    public function test_perintah_gagal_untuk_email_tanpa_toko(): void
    {
        $pembeli = $this->buatPembeli();

        $this->artisan('toko:setujui', ['email' => $pembeli->email])->assertFailed();
        $this->artisan('toko:setujui', ['email' => 'tidakada@contoh.test'])->assertFailed();
    }
}
