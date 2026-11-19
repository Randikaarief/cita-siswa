<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Peran akun: user (siswa) | mentor | admin
            $table->string('role', 20)->default('user')->after('password')->index();

            // Kolom halaman Settings (semuanya opsional, hanya terlihat oleh pemilik akun)
            $table->string('headline')->nullable();
            $table->string('experience', 50)->nullable();
            $table->string('education', 50)->nullable();        // Settings > Personal
            $table->string('education_level', 50)->nullable();  // Settings > Profile
            $table->string('website')->nullable();
            $table->string('nationality', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('location')->nullable();
            $table->string('photo_path')->nullable();           // disimpan di disk privat
            $table->json('social_links')->nullable();           // {facebook, twitter, instagram, youtube}
            $table->json('notify_prefs')->nullable();           // {shortlisted, expiring, alerts}
            $table->boolean('is_profile_public')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'headline', 'experience', 'education', 'education_level', 'website',
                'nationality', 'birth_date', 'gender', 'phone', 'location', 'photo_path',
                'social_links', 'notify_prefs', 'is_profile_public',
            ]);
        });
    }
};
