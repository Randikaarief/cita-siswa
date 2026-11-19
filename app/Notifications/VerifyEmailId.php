<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

/** Email verifikasi dalam bahasa Indonesia (menggantikan teks Inggris bawaan Laravel). */
class VerifyEmailId extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $minutes = (int) Config::get('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject('Verifikasi email CITA SISWA')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Terima kasih sudah daftar di CITA SISWA. Tekan tombol di bawah untuk memastikan ini emailmu.')
            ->action('Verifikasi email', $this->verificationUrl($notifiable))
            ->line("Link ini berlaku {$minutes} menit. Kalau sudah kedaluwarsa, minta link baru dari website.")
            ->line('Kalau kamu tidak merasa mendaftar, abaikan saja email ini.')
            ->salutation('Salam hangat, CITA SISWA');
    }
}
