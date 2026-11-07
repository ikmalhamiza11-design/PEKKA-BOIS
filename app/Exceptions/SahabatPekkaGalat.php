<?php

namespace App\Exceptions;

use RuntimeException;

class SahabatPekkaGalat extends RuntimeException
{
    public function __construct(string $pesan, public readonly int $status = 503)
    {
        parent::__construct($pesan);
    }
}
