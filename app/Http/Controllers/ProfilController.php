<?php

namespace App\Http\Controllers;

use App\Rules\NomorHp;
use App\Support\FotoProduk;
use App\Support\KirimEmail;
use App\Support\Telepon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('profil', [
            'user' => $user,
            'shop' => $user->shop?->load('kelompok'),
            'inisial' => $user->inisial(),
            'phoneLokal' => Telepon::lokal($user->phone),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['bail', 'required', new NomorHp],
        ]);

        $request->user()->update([
            'name' => $data['name'],
            'phone' => Telepon::normalisasi($data['phone']),
        ]);

        return redirect()->route('profil')->with('pesan', 'Data diri berhasil diperbarui.');
    }

    public function foto(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'foto.required' => 'Pilih foto dulu sebelum menekan Simpan Foto.',
            'foto.image' => 'Berkas harus berupa foto.',
            'foto.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'foto.max' => 'Ukuran foto maksimal 3 MB.',
        ]);

        $user = $request->user();
        $lama = $user->foto;

        $user->forceFill(['foto' => FotoProduk::simpan($request->file('foto'), 'profil', 400)])->save();
        FotoProduk::hapus($lama);

        return redirect()->route('profil')->with('pesan', 'Foto profil berhasil diperbarui.');
    }

    public function hapusFoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        $lama = $user->foto;

        $user->forceFill(['foto' => null])->save();
        FotoProduk::hapus($lama);

        return redirect()->route('profil')->with('pesan', 'Foto profil dihapus.');
    }

    public function email(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'sandi_email' => ['required', 'current_password'],
        ]);

        if (Str::lower($data['email']) === Str::lower($user->email)) {
            throw ValidationException::withMessages(['email' => 'Email baru sama dengan email saat ini.']);
        }

        $user->forceFill([
            'email' => $data['email'],
            'email_verified_at' => null,
        ])->save();

        $terkirim = KirimEmail::coba(fn () => $user->sendEmailVerificationNotification());

        return redirect()->route('verification.notice')->with('status', $terkirim ? 'email-diganti' : 'email-gagal');
    }

    public function sandi(Request $request): RedirectResponse
    {
        $request->validate([
            'kata_sandi_lama' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:kata_sandi_lama', Password::min(8)->mixedCase()->numbers()],
        ]);

        $request->user()->forceFill([
            'password' => $request->input('password'),
            'remember_token' => Str::random(60),
            'wajib_ganti_sandi' => false,
        ])->save();

        return redirect()->route('profil')->with('pesan', 'Kata sandi berhasil diganti.');
    }
}
