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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->whereNull('disembunyikan_at');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function setujui(?User $admin = null): void
    {
        $this->forceFill([
            'status' => self::TERVERIFIKASI,
            'alasan_penolakan' => null,
            'diverifikasi_oleh' => $admin?->id,
            'diverifikasi_at' => now(),
        ])->save();
    }

    public function tolak(User $admin, string $alasan): void
    {
        $this->forceFill([
            'status' => self::DITOLAK,
            'alasan_penolakan' => $alasan,
            'diverifikasi_oleh' => $admin->id,
            'diverifikasi_at' => now(),
        ])->save();
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            self::TERVERIFIKASI => 'Terverifikasi',
            self::DITOLAK => 'Ditolak',
            default => 'Menunggu',
        };
    }

    public function punyaRekening(): bool
    {
        return filled($this->nama_bank) && filled($this->nomor_rekening) && filled($this->atas_nama);
    }
}
