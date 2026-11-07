<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FotoProduk
{
    public const LEBAR_MAKSIMAL = 1200;

    public const KUALITAS = 82;

    public static function simpan(UploadedFile $berkas): string
    {
        $isi = static::olah($berkas);

        if ($isi !== null) {
            $nama = 'produk/'.Str::uuid().'.jpg';
            Storage::disk('public')->put($nama, $isi);

            return $nama;
        }

        $nama = 'produk/'.Str::uuid().'.'.($berkas->guessExtension() ?: 'jpg');
        Storage::disk('public')->put($nama, file_get_contents($berkas->getRealPath()));

        return $nama;
    }

    public static function hapus(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected static function olah(UploadedFile $berkas): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return null;
        }

        $mentah = @file_get_contents($berkas->getRealPath());

        if ($mentah === false) {
            return null;
        }

        $gambar = @imagecreatefromstring($mentah);

        if (! $gambar instanceof GdImage) {
            return null;
        }

        $gambar = static::putar($gambar, $berkas);

        if (imagesx($gambar) > self::LEBAR_MAKSIMAL) {
            $kecil = imagescale($gambar, self::LEBAR_MAKSIMAL, -1, IMG_BICUBIC);

            if ($kecil instanceof GdImage) {
                $gambar = $kecil;
            }
        }

        $lebar = imagesx($gambar);
        $tinggi = imagesy($gambar);

        $kanvas = imagecreatetruecolor($lebar, $tinggi);
        imagefill($kanvas, 0, 0, imagecolorallocate($kanvas, 255, 255, 255));
        imagecopy($kanvas, $gambar, 0, 0, 0, 0, $lebar, $tinggi);

        ob_start();
        imagejpeg($kanvas, null, self::KUALITAS);
        $hasil = ob_get_clean();

        return $hasil === false || $hasil === '' ? null : $hasil;
    }

    protected static function putar(GdImage $gambar, UploadedFile $berkas): GdImage
    {
        if (! function_exists('exif_read_data') || $berkas->getMimeType() !== 'image/jpeg') {
            return $gambar;
        }

        $exif = @exif_read_data($berkas->getRealPath());

        $sudut = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($sudut === 0) {
            return $gambar;
        }

        $putar = imagerotate($gambar, $sudut, 0);

        return $putar instanceof GdImage ? $putar : $gambar;
    }
}
