<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'order_item_id',
        'user_id',
        'product_id',
        'shop_id',
        'rating',
        'komentar',
        'balasan',
        'dibalas_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'dibalas_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public static function samarkan(?string $nama): string
    {
        $kata = preg_split('/\s+/u', trim((string) $nama), -1, PREG_SPLIT_NO_EMPTY);

        if ($kata === false || $kata === []) {
            return 'Pembeli';
        }

        if (count($kata) === 1) {
            return $kata[0];
        }

        return $kata[0].' '.mb_strtoupper(mb_substr($kata[1], 0, 1)).'.';
    }

    public function namaSamaran(): string
    {
        return self::samarkan($this->user?->name);
    }
}
