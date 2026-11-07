<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\NomorHp;
use App\Support\Telepon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class OperatorController extends Controller
{
    public function index(): View
    {
        return view('admin.operator.index', [
            'akun' => User::where('role', User::ADMIN_DINAS)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', new NomorHp],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $akun = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => filled($data['phone'] ?? null) ? Telepon::normalisasi($data['phone']) : null,
            'password' => $data['password'],
        ]);
        $akun->role = User::ADMIN_DINAS;
        $akun->email_verified_at = now();
        $akun->save();

        return redirect()->route('admin.operator.index')->with('pesan', 'Akun operator '.$akun->name.' sudah dibuat. Sampaikan email dan kata sandinya secara langsung.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $akun = User::where('role', User::ADMIN_DINAS)->findOrFail($id);

        if ($akun->id === $request->user()->id) {
            return redirect()->route('admin.operator.index')->with('galat', 'Akun yang sedang Anda pakai tidak bisa dihapus.');
        }

        $nama = $akun->name;
        $akun->delete();

        return redirect()->route('admin.operator.index')->with('pesan', 'Akun operator '.$nama.' dihapus.');
    }
}
