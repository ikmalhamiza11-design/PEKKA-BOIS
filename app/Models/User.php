<?php

namespace App\Models;

use App\Notifications\ResetKataSandi;
use App\Notifications\VerifyAkunPeka;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const PEMBELI = 'pembeli';
    public const PENJUAL = 'penjual';
    public const ADMIN_KELOMPOK = 'admin_kelompok';
    public const ADMIN_DINAS = 'admin_dinas';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function labelPeran(): string
    {
        return match ($this->role) {
            self::PENJUAL => 'Penjual',
            self::ADMIN_KELOMPOK => 'Admin Kelompok',
            self::ADMIN_DINAS => 'Admin Dinas',
            default => 'Pembeli',
        };
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyAkunPeka());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetKataSandi($token));
    }
}
