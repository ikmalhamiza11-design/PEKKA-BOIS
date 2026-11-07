<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LaporanAkun;
use App\Models\User;
use App\Rules\NomorHp;
use App\Support\Telepon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BantuanMasukController extends Controller
{
    public function create(): View
    {
        return view('auth.bantuan-masuk');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'phone' => ['bail', 'required', new NomorHp],
            'email' => ['nullable', 'email', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'nama.required' => 'Tulis nama Ibu/Bapak sesuai yang dipakai saat mendaftar.',
            'phone.required' => 'Nomor WhatsApp wajib diisi agar operator bisa menghubungi.',
        ]);

        $phone = Telepon::normalisasi($data['phone']);
        $email = filled($data['email'] ?? null) ? trim($data['email']) : null;

        // Laporan ganda dari nomor yang sama dalam sehari tidak dicatat dua kali.
        $sudahAda = LaporanAkun::baru()
            ->where('phone', $phone)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if (! $sudahAda) {
            LaporanAkun::create([
                'nama' => trim($data['nama']),
                'phone' => $phone,
                'email' => $email,
                'keterangan' => filled($data['keterangan'] ?? null) ? trim($data['keterangan']) : null,
                'user_id' => $this->cariAkun($email, $phone)?->id,
            ]);
        }

        return redirect()->route('bantuan.masuk')->with('status', 'laporan-terkirim');
    }

    private function cariAkun(?string $email, string $phone): ?User
    {
        $dapat = [User::PEMBELI, User::PENJUAL];

        if ($email !== null) {
            $akun = User::whereIn('role', $dapat)->whereRaw('lower(email) = ?', [mb_strtolower($email)])->first();

            if ($akun) {
                return $akun;
            }
        }

        $cocok = User::whereIn('role', $dapat)->where('phone', $phone)->limit(2)->get();

        return $cocok->count() === 1 ? $cocok->first() : null;
    }
}
