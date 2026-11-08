<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** Kategori yang boleh memakai pre-order (khusus makanan). */
    public const BOLEH_PRE_ORDER = ['kuliner'];

    protected $fillable = ['nama', 'slug', 'urutan'];

    public function bolehPreOrder(): bool
    {
        return in_array($this->slug, self::BOLEH_PRE_ORDER, true);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
