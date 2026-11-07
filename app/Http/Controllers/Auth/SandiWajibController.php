<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SandiWajibController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->wajib_ganti_sandi) {
            return redirect()->route('dashboard');
        }

        return view('auth.sandi-wajib');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->wajib_ganti_sandi) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Kata sandi baru harus berbeda dari sandi sementara.',
            ]);
        }

        $user->forceFill([
            'password' => $request->input('password'),
            'remember_token' => Str::random(60),
            'wajib_ganti_sandi' => false,
        ])->save();

        return redirect()
            ->to($user->halamanAwal())
            ->with('pesan', 'Kata sandi baru tersimpan. Selamat datang kembali!');
    }
}
