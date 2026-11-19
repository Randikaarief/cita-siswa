<?php

namespace Database\Seeders;

use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun contoh untuk tim yang mengerjakan sisi mentor dan admin.
 * PASSWORD SEMENTARA, WAJIB DIGANTI sebelum website dipakai sungguhan.
 * (Atur lewat .env: SEED_ADMIN_PASSWORD dan SEED_MENTOR_PASSWORD.)
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@berasiswa.test']);
        $admin->name = 'Admin Contoh';
        $admin->password = env('SEED_ADMIN_PASSWORD', 'ganti-password-admin');
        $admin->role = User::ROLE_ADMIN;
        $admin->must_change_password = true;
        $admin->save();

        $mentor = User::firstOrNew(['email' => 'mentor@berasiswa.test']);
        $mentor->name = 'Mentor Contoh';
        $mentor->password = env('SEED_MENTOR_PASSWORD', 'ganti-password-mentor');
        $mentor->role = User::ROLE_MENTOR;
        $mentor->must_change_password = true;
        $mentor->save();

        // Hubungkan akun mentor contoh ke salah satu profil contoh.
        MentorProfile::withoutGlobalScopes()
            ->where('is_sample', true)->orderBy('id')->first()
            ?->update(['user_id' => $mentor->id]);
    }
}
