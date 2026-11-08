<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'product_id',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
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

    public function masalah(): ?string
    {
        $produk = $this->product;

        if (! $produk || ! $produk->aktif || $produk->shop->status !== Shop::TERVERIFIKASI) {
            return 'Produk ini tidak lagi tersedia.';
        }

        if ($produk->shop->user_id === $this->user_id) {
            return 'Produk dari tokomu sendiri tidak bisa dibeli.';
        }

        if ($kendala = $produk->kendalaToko()) {
            return $kendala;
        }

        if ($produk->stok < 1) {
            return 'Stok habis.';
        }

        if ($this->jumlah > $produk->stok) {
            return 'Stok tersisa '.$produk->stok.'.';
        }

        return null;
    }

    protected function subtotal(): Attribute
    {
        return Attribute::get(fn () => ($this->product?->harga ?? 0) * $this->jumlah);
    }
}
