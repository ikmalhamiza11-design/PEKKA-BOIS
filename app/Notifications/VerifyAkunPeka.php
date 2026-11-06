<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyAkunPeka extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Akun PEKKA Bogor Istimewa')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Terima kasih sudah mendaftar di PEKKA Bogor Istimewa. Klik tombol di bawah untuk memastikan alamat email ini milik Anda.')
            ->action('Verifikasi Akun PEKKA', $url)
            ->line('Jika Anda tidak merasa mendaftar, abaikan saja email ini.')
            ->salutation('Salam, Tim PEKKA Bogor Istimewa');
    }
}
