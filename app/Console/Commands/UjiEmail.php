<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class UjiEmail extends Command
{
    protected $signature = 'email:uji {alamat : Alamat email tujuan uji}';

    protected $description = 'Kirim satu email uji untuk memastikan pengaturan Gmail di .env sudah benar';

    public function handle(): int
    {
        $this->line('Pengirim : '.config('mail.default').' / '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port'));
        $this->line('Dari     : '.config('mail.from.address'));

        if (config('mail.default') === 'log') {
            $this->warn('MAIL_MAILER masih "log": email hanya ditulis ke storage/logs/laravel.log, tidak dikirim. Ubah ke "smtp" di .env.');
        }

        try {
            Mail::raw(
                'Ini email uji dari '.config('app.name').'. Kalau Anda membaca ini, pengiriman email sudah berfungsi.',
                fn ($pesan) => $pesan->to($this->argument('alamat'))->subject('Uji email '.config('app.name'))
            );
        } catch (\Throwable $galat) {
            $this->error('Gagal mengirim: '.$galat->getMessage());
            $this->line('Periksa: MAIL_USERNAME harus alamat Gmail asli, MAIL_PASSWORD harus sandi aplikasi 16 huruf (tanpa spasi), dan verifikasi 2 langkah Google harus aktif.');

            return self::FAILURE;
        }

        $this->info('Perintah kirim selesai tanpa galat. Cek kotak masuk dan folder Spam di '.$this->argument('alamat').'.');

        return self::SUCCESS;
    }
}
