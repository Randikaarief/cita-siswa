<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Isi database dengan data CONTOH (is_sample = true).
     * Sebelum peluncuran: php artisan samples:purge   (atau --hide untuk menyembunyikan saja)
     */
    public function run(): void
    {
        $this->call([
            ScholarshipSeeder::class,
            CompetitionSeeder::class,
            MentorProfileSeeder::class,
            UserSeeder::class,
        ]);
    }
}
