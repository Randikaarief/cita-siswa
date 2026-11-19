<?php

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (require __DIR__.'/data/competitions.php' as $row) {
            Competition::updateOrCreate(['nama' => $row['nama']], $row + ['is_sample' => true]);
        }
    }
}
