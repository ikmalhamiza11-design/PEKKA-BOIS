<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    public const MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const DIBAYAR = 'dibayar';
    public const DIPROSES = 'diproses';
    public const DIKIRIM = 'dikirim';
    public const SELESAI = 'selesai';
    public const DIBATALKAN = 'dibatalkan';

    protected $fillable = [
        'kode',
        'user_id',
        'shop_id',
        'status',
        'nama_penerima',
        'telepon',
        'alamat',
        'catatan',
        'subtotal',
        'total',
        'kurir',
        'nomor_resi',
        'diproses_at',
        'dikirim_at',
        'selesai_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'total' => 'integer',
            'diproses_at' => 'datetime',
            'dikirim_at' => 'datetime',
            'selesai_at' => 'datetime',
        ];
    }

    public static function buatKode(): string
    {
        do {
            $kode = 'PKB-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (static::where('kode', $kode)->exists());

        return $kode;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function pembayaranTerakhir(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            self::MENUNGGU_PEMBAYARAN => 'Menunggu pembayaran',
            self::MENUNGGU_VERIFIKASI => 'Menunggu verifikasi pembayaran',
            self::DIBAYAR => 'Sudah dibayar',
            self::DIPROSES => 'Sedang diproses',
            self::DIKIRIM => 'Dikirim',
            self::SELESAI => 'Selesai',
            self::DIBATALKAN => 'Dibatalkan',
            default => Str::headline($this->status),
        };
    }

    public function bisaDibatalkan(): bool
    {
        return $this->status === self::MENUNGGU_PEMBAYARAN;
    }

    public function bisaDibayar(): bool
    {
        return $this->status === self::MENUNGGU_PEMBAYARAN && $this->shop->punyaRekening();
    }
}
