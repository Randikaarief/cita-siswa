<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notifikasi sederhana untuk halaman Alert.
 * Dipakai nanti oleh sisi mentor/admin:
 *   $user->notify(new SimpleNotice('Judul', 'Keterangan singkat', '/riwayat'));
 */
class SimpleNotice extends Notification
{
    public function __construct(
        public string $title,
        public ?string $subtitle = null,
        public ?string $url = null,
        public ?string $badge = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'url' => $this->url,
            'badge' => $this->badge,
        ];
    }
}
