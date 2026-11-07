<?php

namespace App\Http\Controllers;

use App\Rules\NomorHp;
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
            'inisial' => Str::of($user->name)->explode(' ')->filter()->take(2)->map(fn ($kata) => Str::upper(Str::substr($kata, 0, 1)))->implode(''),
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

        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')->with('status', 'email-diganti');
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
        ])->save();

        return redirect()->route('profil')->with('pesan', 'Kata sandi berhasil diganti.');
    }
}
