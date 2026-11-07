<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelompok;
use App\Models\User;
use App\Rules\NomorHp;
use App\Support\Telepon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminKelompokController extends Controller
{
    public function index(): View
    {
        return view('admin.admin-kelompok.index', [
            'akun' => User::where('role', User::ADMIN_KELOMPOK)->with('kelompok')->orderBy('name')->get(),
            'kelompok' => Kelompok::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', new NomorHp],
            'kelompok_id' => ['required', 'exists:kelompoks,id'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $akun = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => filled($data['phone'] ?? null) ? Telepon::normalisasi($data['phone']) : null,
            'password' => $data['password'],
        ]);
        $akun->role = User::ADMIN_KELOMPOK;
        $akun->kelompok_id = (int) $data['kelompok_id'];
        $akun->email_verified_at = now();
        $akun->save();

        return redirect()->route('admin.admin-kelompok.index')->with('pesan', 'Akun admin kelompok untuk '.$akun->name.' sudah dibuat. Sampaikan email dan kata sandinya secara langsung.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $akun = User::where('role', User::ADMIN_KELOMPOK)->findOrFail($id);
        $nama = $akun->name;
        $akun->delete();

        return redirect()->route('admin.admin-kelompok.index')->with('pesan', 'Akun admin kelompok '.$nama.' dihapus.');
    }
}
