<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    public const MENUNGGU = 'menunggu';
    public const TERVERIFIKASI = 'terverifikasi';
    public const DITOLAK = 'ditolak';

    protected $fillable = [
        'nama',
        'kelompok_id',
        'status',
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
        'libur',
        'jadwal_otomatis',
        'jam_buka',
        'jam_tutup',
        'hari_buka',
    ];

    public const NAMA_HARI = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    protected function casts(): array
    {
        return [
            'libur' => 'boolean',
            'jadwal_otomatis' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(Kelompok::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->whereNull('disembunyikan_at');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function setujui(?User $admin = null): void
    {
        $this->forceFill([
            'status' => self::TERVERIFIKASI,
            'alasan_penolakan' => null,
            'diverifikasi_oleh' => $admin?->id,
            'diverifikasi_at' => now(),
        ])->save();
    }

    public function tolak(User $admin, string $alasan): void
    {
        $this->forceFill([
            'status' => self::DITOLAK,
            'alasan_penolakan' => $alasan,
            'diverifikasi_oleh' => $admin->id,
            'diverifikasi_at' => now(),
        ])->save();
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            self::TERVERIFIKASI => 'Terverifikasi',
            self::DITOLAK => 'Ditolak',
            default => 'Menunggu',
        };
    }

    public function punyaRekening(): bool
    {
        return filled($this->nama_bank) && filled($this->nomor_rekening) && filled($this->atas_nama);
    }

    /**
     * Hari buka sebagai daftar angka 1 (Senin) sampai 7 (Minggu).
     * Kosong berarti buka setiap hari.
     */
    public function hariBukaDaftar(): array
    {
        $hari = array_filter(array_map('intval', explode(',', (string) $this->hari_buka)), fn ($h) => $h >= 1 && $h <= 7);

        return $hari === [] ? array_keys(self::NAMA_HARI) : array_values(array_unique($hari));
    }

    public static function zonaWaktu(): string
    {
        return config('app.zona_toko', 'Asia/Jakarta');
    }

    /** Apakah jadwal otomatis mengizinkan toko buka pada saat ini. */
    public function sesuaiJadwal(?\Carbon\CarbonInterface $waktu = null): bool
    {
        if (! $this->jadwal_otomatis || ! filled($this->jam_buka) || ! filled($this->jam_tutup)) {
            return true;
        }

        $waktu = ($waktu ?? now())->copy()->setTimezone(self::zonaWaktu());

        if (! in_array($waktu->dayOfWeekIso, $this->hariBukaDaftar(), true)) {
            return false;
        }

        $sekarang = $waktu->format('H:i');

        return $sekarang >= $this->jam_buka && $sekarang < $this->jam_tutup;
    }

    /** Toko menerima pesanan biasa (non pre-order) saat ini. */
    public function bukaSekarang(?\Carbon\CarbonInterface $waktu = null): bool
    {
        return ! $this->libur && $this->sesuaiJadwal($waktu);
    }

    /** Teks singkat untuk pembeli kalau toko tidak sedang buka. Null kalau buka. */
    public function alasanTutup(?\Carbon\CarbonInterface $waktu = null): ?string
    {
        if ($this->libur) {
            return 'Toko sedang libur.';
        }

        if (! $this->sesuaiJadwal($waktu)) {
            return 'Toko sedang tutup. '.$this->jadwalTeks();
        }

        return null;
    }

    public function jadwalTeks(): string
    {
        if (! $this->jadwal_otomatis || ! filled($this->jam_buka) || ! filled($this->jam_tutup)) {
            return '';
        }

        $hari = $this->hariBukaDaftar();

        $teksHari = count($hari) === 7
            ? 'Setiap hari'
            : implode(', ', array_map(fn ($h) => self::NAMA_HARI[$h], $hari));

        return 'Buka '.$teksHari.', pukul '.str_replace(':', '.', $this->jam_buka).' sampai '.str_replace(':', '.', $this->jam_tutup).' WIB.';
    }
}
