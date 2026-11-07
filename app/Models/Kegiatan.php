<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $table = 'kegiatans';

    protected $fillable = ['judul', 'deskripsi', 'lokasi', 'mulai_at', 'selesai_at', 'dipublikasikan'];

    protected function casts(): array
    {
        return [
            'mulai_at' => 'datetime',
            'selesai_at' => 'datetime',
            'dipublikasikan' => 'boolean',
        ];
    }

    public function scopeTayang(Builder $query): void
    {
        $query->where('dipublikasikan', true);
    }

    public function scopeMendatang(Builder $query): void
    {
        $query->whereRaw('coalesce(selesai_at, mulai_at) >= ?', [now()]);
    }

    public function scopeBerlalu(Builder $query): void
    {
        $query->whereRaw('coalesce(selesai_at, mulai_at) < ?', [now()]);
    }

    public function waktuTeks(): string
    {
        $teks = $this->mulai_at->translatedFormat('l, j F Y, H.i');

        if (! $this->selesai_at) {
            return $teks;
        }

        if ($this->selesai_at->isSameDay($this->mulai_at)) {
            return $teks.' - '.$this->selesai_at->format('H.i');
        }

        return $teks.' sampai '.$this->selesai_at->translatedFormat('l, j F Y, H.i');
    }
}
