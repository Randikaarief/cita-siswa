<?php

namespace Database\Seeders;

use App\Models\MentorProfile;
use Illuminate\Database\Seeder;

class MentorProfileSeeder extends Seeder
{
    public function run(): void
    {
        foreach (require __DIR__.'/data/mentors.php' as $row) {
            MentorProfile::updateOrCreate(
                ['nama' => $row['nama']],
                $row + [
                    'is_sample' => true,
                    // Contoh dibuat "bersedia" supaya alur "Minta masukan mentor" bisa dicoba.
                    // Nomor WhatsApp SENGAJA kosong: mentor asli mengisi nomornya sendiri.
                    'is_willing_to_help' => true,
                    'whatsapp' => null,
                ],
            );
        }
    }
}
