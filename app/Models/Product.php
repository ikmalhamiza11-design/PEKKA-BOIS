<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'shop_id',
        'category_id',
        'nama',
        'deskripsi',
        'harga',
        'stok',
        'aktif',
        'unggulan',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'stok' => 'integer',
            'aktif' => 'boolean',
            'unggulan' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->whereNull('disembunyikan_at');
    }

    public function scopeTampil(Builder $query): void
    {
        $query->where('aktif', true)
            ->whereHas('shop', fn (Builder $toko) => $toko->where('status', Shop::TERVERIFIKASI));
    }

    protected function hargaRupiah(): Attribute
    {
        return Attribute::get(fn () => 'Rp '.number_format($this->harga, 0, ',', '.'));
    }
}
