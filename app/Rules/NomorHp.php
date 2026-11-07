<?php

namespace App\Rules;

use App\Support\Telepon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NomorHp implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^628\d{8,12}$/', Telepon::normalisasi((string) $value))) {
            $fail('Nomor HP/WhatsApp belum benar. Contoh: 0812-3456-7890.');
        }
    }
}
