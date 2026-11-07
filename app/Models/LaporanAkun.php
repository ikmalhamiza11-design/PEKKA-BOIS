<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanAkun extends Model
{
    public const BARU = 'baru';
    public const SELESAI = 'selesai';

    protected $fillable = [
        'nama',
        'phone',
        'email',
        'keterangan',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'ditangani_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penangan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function scopeBaru(Builder $query): void
    {
        $query->where('status', self::BARU);
    }

    public function tandaiSelesai(User $operator): void
    {
        $this->forceFill([
            'status' => self::SELESAI,
            'ditangani_oleh' => $operator->id,
            'ditangani_at' => now(),
        ])->save();
    }
}
