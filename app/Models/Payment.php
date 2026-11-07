<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const MENUNGGU = 'menunggu_verifikasi';
    public const DITERIMA = 'diterima';
    public const DITOLAK = 'ditolak';

    protected $fillable = [
        'order_id',
        'nama_pengirim',
        'jumlah',
        'bukti',
        'status',
        'alasan_penolakan',
        'diverifikasi_at',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'diverifikasi_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            self::MENUNGGU => 'Menunggu verifikasi',
            self::DITERIMA => 'Diterima',
            self::DITOLAK => 'Ditolak',
            default => $this->status,
        };
    }
}
