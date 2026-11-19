<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Competition;
use App\Models\MentorProfile;
use App\Models\Scholarship;
use App\Models\Testimonial;
use Illuminate\Console\Command;

class PurgeSamples extends Command
{
    protected $signature = 'samples:purge {--hide : Sembunyikan saja (is_visible = false), jangan dihapus}';

    protected $description = 'Hapus atau sembunyikan semua data contoh (is_sample = true) sebelum peluncuran';

    public function handle(): int
    {
        $models = [
            'beasiswa' => Scholarship::class,
            'lomba' => Competition::class,
            'mentor' => MentorProfile::class,
            'testimoni' => Testimonial::class,
        ];

        foreach ($models as $label => $model) {
            $query = $model::withoutGlobalScopes()->where('is_sample', true);
            $count = (clone $query)->count();

            if ($this->option('hide')) {
                $query->update(['is_visible' => false]);
                $this->line("Disembunyikan: {$count} {$label}");
            } else {
                $query->get()->each->delete(); // pengajuan bedah berkas ikut terhapus (cascade)
                $this->line("Dihapus: {$count} {$label}");
            }
        }

        if (! $this->option('hide')) {
            // Bersihkan riwayat klik yang targetnya sudah tidak ada.
            $removed = 0;
            Application::query()->get()->each(function (Application $app) use (&$removed) {
                if ($app->target() === null) {
                    $app->delete();
                    $removed++;
                }
            });
            $this->line("Riwayat klik yatim dihapus: {$removed}");
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }
}
