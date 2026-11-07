<?php

namespace App\Support;

class Rupiah
{
    public static function format(int|float|null $nilai): string
    {
        return 'Rp '.number_format((float) $nilai, 0, ',', '.');
    }
}
