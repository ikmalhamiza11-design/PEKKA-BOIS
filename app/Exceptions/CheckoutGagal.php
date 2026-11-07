<?php

namespace App\Exceptions;

use RuntimeException;

class CheckoutGagal extends RuntimeException
{
    public function __construct(public array $masalah)
    {
        parent::__construct(implode(' ', $masalah));
    }
}
