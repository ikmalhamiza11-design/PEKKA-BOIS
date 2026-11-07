<?php

namespace Tests\Feature\Penjual;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class RekeningTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    public function test_penjual_menyimpan_rekening_dan_spasi_dibersihkan(): void
    {
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)
            ->put(route('penjual.rekening.update'), [
                'nama_bank' => 'BRI',
                'nomor_rekening' => '1234 5678-90',
                'atas_nama' => 'Siti Rohmah',
            ])
            ->assertRedirect(route('penjual.rekening.edit'))
            ->assertSessionHas('pesan');

        $toko = $penjual->shop->fresh();

        $this->assertSame('BRI', $toko->nama_bank);
        $this->assertSame('1234567890', $toko->nomor_rekening);
        $this->assertTrue($toko->punyaRekening());
    }

    public function test_semua_kolom_wajib_diisi(): void
    {
        $this->actingAs($this->buatPenjual())
            ->put(route('penjual.rekening.update'), [])
            ->assertSessionHasErrors(['nama_bank', 'nomor_rekening', 'atas_nama']);
    }

    public function test_nomor_rekening_harus_berupa_angka(): void
    {
        $this->actingAs($this->buatPenjual())
            ->put(route('penjual.rekening.update'), [
                'nama_bank' => 'BRI',
                'nomor_rekening' => 'abc123',
                'atas_nama' => 'Siti',
            ])
            ->assertSessionHasErrors('nomor_rekening');
    }

    public function test_halaman_rekening_menampilkan_data_tersimpan(): void
    {
        $penjual = $this->buatPenjual();
        $penjual->shop->update(['nama_bank' => 'Mandiri', 'nomor_rekening' => '9876543210', 'atas_nama' => 'Ani']);

        $this->actingAs($penjual)
            ->get(route('penjual.rekening.edit'))
            ->assertOk()
            ->assertSee('9876543210');
    }
}
