<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/** Email reset password dalam bahasa Indonesia (menggantikan teks Inggris bawaan Laravel). */
class ResetPasswordId extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $minutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('Atur ulang password CITA SISWA')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kami menerima permintaan untuk mengatur ulang password akunmu.')
            ->action('Atur ulang password', $this->resetUrl($notifiable))
            ->line("Link ini berlaku {$minutes} menit.")
            ->line('Kalau bukan kamu yang meminta, abaikan saja email ini. Passwordmu tidak berubah.')
            ->salutation('Salam hangat, CITA SISWA');
    }
}
