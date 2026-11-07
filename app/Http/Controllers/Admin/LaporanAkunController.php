<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanAkun;
use App\Models\User;
use App\Support\SandiSementara;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LaporanAkunController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $status = in_array($status, [LaporanAkun::BARU, LaporanAkun::SELESAI, 'semua'], true) ? $status : LaporanAkun::BARU;

        $laporan = LaporanAkun::with(['user.shop', 'penangan'])
            ->when($status !== 'semua', fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.laporan.index', [
            'laporan' => $laporan,
            'status' => $status,
            'hitungBaru' => LaporanAkun::baru()->count(),
            'sandiSementara' => session('sandi_sementara'),
        ]);
    }

    public function cocokkan(Request $request, int $id): RedirectResponse
    {
        $laporan = LaporanAkun::findOrFail($id);

        $data = $request->validate([
            'email_akun' => ['required', 'email'],
        ], [
            'email_akun.required' => 'Tulis email akun yang mau dicocokkan.',
        ]);

        $akun = User::whereIn('role', [User::PEMBELI, User::PENJUAL])
            ->whereRaw('lower(email) = ?', [mb_strtolower(trim($data['email_akun']))])
            ->first();

        if (! $akun) {
            return back()->with('galat', 'Akun dengan email itu tidak ditemukan.');
        }

        $laporan->update(['user_id' => $akun->id]);

        return back()->with('pesan', 'Laporan dicocokkan dengan akun '.$akun->name.'.');
    }

    public function sandiSementara(Request $request, int $id): RedirectResponse
    {
        $laporan = LaporanAkun::with('user')->findOrFail($id);
        $akun = $laporan->user;

        if (! $akun || ! in_array($akun->role, [User::PEMBELI, User::PENJUAL], true)) {
            return back()->with('galat', 'Cocokkan laporan dengan akun pembeli atau penjual dulu.');
        }

        $sandi = SandiSementara::buat();

        $akun->forceFill([
            'password' => $sandi,
            'wajib_ganti_sandi' => true,
            'remember_token' => Str::random(60),
        ])->save();

        // Keluarkan akun itu dari semua perangkat agar sandi lama benar-benar tidak berlaku.
        if (config('session.driver') === 'database' && Schema::hasTable(config('session.table', 'sessions'))) {
            DB::table(config('session.table', 'sessions'))->where('user_id', $akun->id)->delete();
        }

        $laporan->tandaiSelesai($request->user());

        $pesanWa = 'Halo Bu '.$laporan->nama.', ini operator PEKKA Bogor Istimewa. '
            .'Sandi sementara akun Ibu: '.$sandi.' . '
            .'Silakan masuk di '.route('login').' lalu Ibu akan diminta membuat sandi baru. '
            .'Jangan berikan sandi ini kepada siapa pun.';

        return back()->with('sandi_sementara', [
            'laporan_id' => $laporan->id,
            'nama' => $laporan->nama,
            'email' => $akun->email,
            'sandi' => $sandi,
            'wa' => 'https://wa.me/'.$laporan->phone.'?text='.rawurlencode($pesanWa),
        ]);
    }

    public function verifikasiEmail(Request $request, int $id): RedirectResponse
    {
        $laporan = LaporanAkun::with('user')->findOrFail($id);
        $akun = $laporan->user;

        if (! $akun || ! in_array($akun->role, [User::PEMBELI, User::PENJUAL], true)) {
            return back()->with('galat', 'Cocokkan laporan dengan akun pembeli atau penjual dulu.');
        }

        if ($akun->hasVerifiedEmail()) {
            return back()->with('galat', 'Email akun ini sudah terverifikasi.');
        }

        $akun->markEmailAsVerified();
        $laporan->tandaiSelesai($request->user());

        return back()->with('pesan', 'Email akun '.$akun->name.' ditandai terverifikasi. Kabari ibunya lewat WhatsApp.');
    }

    public function selesai(Request $request, int $id): RedirectResponse
    {
        LaporanAkun::findOrFail($id)->tandaiSelesai($request->user());

        return back()->with('pesan', 'Laporan ditandai selesai.');
    }
}
