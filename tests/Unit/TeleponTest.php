<?php

namespace Tests\Unit;

use App\Support\Telepon;
use PHPUnit\Framework\TestCase;

class TeleponTest extends TestCase
{
    public function test_nomor_berawalan_nol_diubah_ke_62(): void
    {
        $this->assertSame('6281234567890', Telepon::normalisasi('0812-3456-7890'));
    }

    public function test_nomor_berawalan_62_tetap(): void
    {
        $this->assertSame('6281234567890', Telepon::normalisasi('+62 812 3456 7890'));
    }

    public function test_nomor_tanpa_awalan_diberi_62(): void
    {
        $this->assertSame('6281234567890', Telepon::normalisasi('81234567890'));
    }

    public function test_lokal_mengubah_62_menjadi_0(): void
    {
        $this->assertSame('081234567890', Telepon::lokal('6281234567890'));
    }

    public function test_lokal_untuk_nilai_kosong(): void
    {
        $this->assertSame('', Telepon::lokal(null));
        $this->assertSame('', Telepon::lokal(''));
    }
}
