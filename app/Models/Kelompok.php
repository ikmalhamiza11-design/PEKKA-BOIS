<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelompok extends Model
{
    protected $fillable = ['nama', 'desa', 'kecamatan'];

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function label(): string
    {
        $wilayah = collect([$this->desa, $this->kecamatan])->filter()->implode(', ');

        return $wilayah !== '' ? $this->nama.' ('.$wilayah.')' : $this->nama;
    }
}
