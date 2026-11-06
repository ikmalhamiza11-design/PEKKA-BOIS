<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetKataSandi extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $menit = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi PEKKA Bogor Istimewa')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line('Tautan ini berlaku selama '.$menit.' menit.')
            ->line('Jika Anda tidak meminta ini, abaikan saja email ini. Kata sandi Anda tidak berubah.')
            ->salutation('Salam, Tim PEKKA Bogor Istimewa');
    }
}
