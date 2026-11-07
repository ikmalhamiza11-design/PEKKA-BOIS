<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    public const MENUNGGU = 'menunggu';
    public const TERVERIFIKASI = 'terverifikasi';
    public const DITOLAK = 'ditolak';

    protected $fillable = [
        'nama',
        'kelompok_id',
        'status',
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(Kelompok::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function punyaRekening(): bool
    {
        return filled($this->nama_bank) && filled($this->nomor_rekening) && filled($this->atas_nama);
    }
}
