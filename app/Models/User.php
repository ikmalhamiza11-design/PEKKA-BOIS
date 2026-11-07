<?php

namespace App\Models;

use App\Notifications\ResetKataSandi;
use App\Notifications\VerifyAkunPeka;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

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
            'wajib_ganti_sandi' => 'boolean',
        ];
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(Kelompok::class);
    }

    public function keranjang(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function inisial(): string
    {
        return Str::of($this->name)->explode(' ')->filter()->take(2)->map(fn ($kata) => Str::upper(Str::substr($kata, 0, 1)))->implode('');
    }

    public function fotoUrl(): ?string
    {
        return $this->foto ? asset('storage/'.$this->foto) : null;
    }

    public function halamanAwal(): string
    {
        return $this->role === self::PENJUAL || $this->adalahAdmin()
            ? route('dashboard')
            : route('beranda');
    }

    public function adalahAdminDinas(): bool
    {
        return $this->role === self::ADMIN_DINAS;
    }

    public function adalahAdminKelompok(): bool
    {
        return $this->role === self::ADMIN_KELOMPOK;
    }

    public function adalahAdmin(): bool
    {
        return $this->adalahAdminDinas() || $this->adalahAdminKelompok();
    }

    public function cakupanToko(): Builder
    {
        $toko = Shop::query();

        if ($this->adalahAdminDinas()) {
            return $toko;
        }

        return $toko->where('kelompok_id', $this->kelompok_id ?? 0);
    }

    public function cakupanUlasan(): Builder
    {
        return Review::query()->whereIn('shop_id', $this->cakupanToko()->select('shops.id'));
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
