<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alumni = mentor (satu tabel).
        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama');
            $table->string('tipe')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('mode', 20)->nullable();
            $table->string('beasiswa')->nullable();
            $table->string('kategori')->nullable()->index();
            $table->string('founded')->nullable();
            $table->string('biaya')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->text('benefits')->nullable();
            $table->string('avatar_color')->nullable();
            $table->string('youtube', 50)->nullable();
            // Mentor memilih sendiri mau membantu bedah berkas atau tidak.
            $table->boolean('is_willing_to_help')->default(false)->index();
            // Diisi oleh mentor sendiri. Tidak pernah ditampilkan di halaman publik/Alumni.
            $table->string('whatsapp', 30)->nullable();
            $table->boolean('is_sample')->default(false)->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_profile_id')->constrained()->cascadeOnDelete();
            $table->string('youtube', 50)->nullable();
            $table->text('quote')->nullable();
            $table->boolean('is_sample')->default(false)->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('mentor_profiles');
    }
};
