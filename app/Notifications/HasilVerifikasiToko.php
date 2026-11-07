<?php

namespace App\Notifications;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HasilVerifikasiToko extends Notification
{
    use Queueable;

    public function __construct(private Shop $toko)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pesan = (new MailMessage)->greeting('Halo, '.$notifiable->name.'!');

        if ($this->toko->status === Shop::TERVERIFIKASI) {
            return $pesan
                ->subject('Toko Anda Sudah Terverifikasi')
                ->line('Kabar baik! Toko "'.$this->toko->nama.'" sudah diverifikasi oleh admin PEKKA. Produk aktif Anda kini tampil di katalog.')
                ->action('Buka Dasbor Penjual', route('dashboard'))
                ->salutation('Salam, Tim PEKKA Bogor Istimewa');
        }

        return $pesan
            ->subject('Toko Anda Belum Dapat Disetujui')
            ->line('Toko "'.$this->toko->nama.'" belum dapat disetujui oleh admin PEKKA.')
            ->line('Alasan: '.$this->toko->alasan_penolakan)
            ->line('Silakan hubungi admin kelompok PEKKA Anda untuk melengkapi atau memperbaiki data toko.')
            ->action('Buka Dasbor Penjual', route('dashboard'))
            ->salutation('Salam, Tim PEKKA Bogor Istimewa');
    }
}
