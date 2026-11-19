<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use Illuminate\Database\Seeder;

class ScholarshipSeeder extends Seeder
{
    public function run(): void
    {
        foreach (require __DIR__.'/data/scholarships.php' as $row) {
            Scholarship::updateOrCreate(['nama' => $row['nama']], $row + ['is_sample' => true]);
        }
    }
}
