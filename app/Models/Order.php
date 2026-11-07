<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    public const MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
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
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'total' => 'integer',
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

    public function labelStatus(): string
    {
        return match ($this->status) {
            self::MENUNGGU_PEMBAYARAN => 'Menunggu pembayaran',
            self::DIBATALKAN => 'Dibatalkan',
            default => Str::headline($this->status),
        };
    }

    public function bisaDibatalkan(): bool
    {
        return $this->status === self::MENUNGGU_PEMBAYARAN;
    }
}
