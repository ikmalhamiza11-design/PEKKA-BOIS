<?php

namespace Tests\Unit;

use App\Models\Review;
use PHPUnit\Framework\TestCase;

class ReviewTest extends TestCase
{
    public function test_nama_dua_kata_disamarkan(): void
    {
        $this->assertSame('Siti R.', Review::samarkan('Siti Rohmah'));
    }

    public function test_nama_lebih_dari_dua_kata_hanya_memakai_dua_kata_pertama(): void
    {
        $this->assertSame('Siti R.', Review::samarkan('Siti Rohmah Lestari'));
    }

    public function test_nama_satu_kata_tetap(): void
    {
        $this->assertSame('Budi', Review::samarkan('Budi'));
    }

    public function test_spasi_berlebih_dirapikan_dan_huruf_kecil_dikapitalkan(): void
    {
        $this->assertSame('Budi S.', Review::samarkan('  Budi    santoso '));
    }

    public function test_nama_kosong_menjadi_pembeli(): void
    {
        $this->assertSame('Pembeli', Review::samarkan(''));
        $this->assertSame('Pembeli', Review::samarkan(null));
    }

    public function test_huruf_non_latin_tidak_rusak(): void
    {
        $this->assertSame('Éva Ö.', Review::samarkan('Éva özkan'));
    }
}
