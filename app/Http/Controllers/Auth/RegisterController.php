<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Kelompok;
use App\Models\Shop;
use App\Models\User;
use App\Support\Telepon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function pilihPeran(): View
    {
        return view('auth.pilih-peran');
    }

    public function lanjut(Request $request): RedirectResponse
    {
        return $request->query('peran') === 'penjual'
            ? redirect()->route('daftar.penjual')
            : redirect()->route('daftar.pembeli');
    }

    public function formPembeli(): View
    {
        return view('auth.daftar-pembeli');
    }

    public function simpanPembeli(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['bail', 'required', $this->aturanTelepon()],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'terms' => ['accepted'],
        ]);

        $user = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => Telepon::normalisasi($data['phone']),
            'password' => $data['password'],
        ]);
        $user->role = User::PEMBELI;
        $user->save();

        return $this->selesaiDaftar($request, $user);
    }

    public function formPenjual(): View
    {
        return view('auth.daftar-penjual', [
            'kelompoks' => Kelompok::orderBy('nama')->get(),
        ]);
    }

    public function simpanPenjual(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'shop_name' => ['required', 'string', 'max:120'],
            'kelompok_id' => ['required', 'exists:kelompoks,id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['bail', 'required', $this->aturanTelepon()],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = new User([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => Telepon::normalisasi($data['phone']),
                'password' => $data['password'],
            ]);
            $user->role = User::PENJUAL;
            $user->save();

            $user->shop()->create([
                'nama' => $data['shop_name'],
                'kelompok_id' => $data['kelompok_id'],
                'status' => Shop::MENUNGGU,
            ]);

            return $user;
        });

        return $this->selesaiDaftar($request, $user);
    }

    public function berhasil(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== User::PENJUAL || ! $user->shop) {
            return redirect()->route('dashboard');
        }

        return view('auth.pendaftaran-berhasil', [
            'shop' => $user->shop->load('kelompok'),
        ]);
    }

    private function selesaiDaftar(Request $request, User $user): RedirectResponse
    {
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    private function aturanTelepon(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! preg_match('/^628\d{8,12}$/', Telepon::normalisasi((string) $value))) {
                $fail('Nomor HP/WhatsApp belum benar. Contoh: 0812-3456-7890.');
            }
        };
    }
}
