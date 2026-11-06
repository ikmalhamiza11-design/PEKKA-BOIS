<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminDinasSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_DINAS_EMAIL');
        $password = env('ADMIN_DINAS_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_DINAS_EMAIL dan ADMIN_DINAS_PASSWORD belum diisi di .env, admin dinas dilewati.');

            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = 'Admin Dinas';
        $user->password = $password;
        $user->role = User::ADMIN_DINAS;
        $user->email_verified_at = now();
        $user->save();
    }
}
