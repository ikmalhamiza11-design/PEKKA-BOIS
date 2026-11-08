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
        'stok_harian',
        'pre_order',
        'pre_order_hari',
        'aktif',
        'unggulan',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'stok' => 'integer',
            'stok_harian' => 'integer',
            'pre_order' => 'boolean',
            'pre_order_hari' => 'integer',
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

    /** Produk pre-order: hanya makanan, dan penjual sudah menentukan lama pembuatannya. */
    public function preOrder(): bool
    {
        return $this->pre_order && $this->pre_order_hari > 0;
    }

    public function labelPreOrder(): ?string
    {
        return $this->preOrder() ? 'Pre-order, siap sekitar '.$this->pre_order_hari.' hari setelah dipesan' : null;
    }

    /**
     * Kendala karena keadaan toko. Toko libur menutup semua pesanan.
     * Di luar jam buka, hanya produk pre-order yang masih bisa dipesan.
     */
    public function kendalaToko(): ?string
    {
        $toko = $this->shop;

        if (! $toko) {
            return null;
        }

        if ($toko->libur) {
            return 'Toko sedang libur.';
        }

        if (! $toko->sesuaiJadwal() && ! $this->preOrder()) {
            return $toko->alasanTutup();
        }

        return null;
    }

    protected function hargaRupiah(): Attribute
    {
        return Attribute::get(fn () => 'Rp '.number_format($this->harga, 0, ',', '.'));
    }
}
