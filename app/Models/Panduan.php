<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Panduan extends Model
{
    public const KATEGORI = [
        'pembeli' => 'Untuk Pembeli',
        'penjual' => 'Untuk Penjual',
        'umum' => 'Umum',
    ];

    protected $table = 'panduans';

    protected $fillable = ['judul', 'kategori', 'isi', 'urutan', 'dipublikasikan'];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'dipublikasikan' => 'boolean',
        ];
    }

    public function scopeTayang(Builder $query): void
    {
        $query->where('dipublikasikan', true);
    }

    public function labelKategori(): string
    {
        return self::KATEGORI[$this->kategori] ?? $this->kategori;
    }
}
