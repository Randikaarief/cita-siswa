<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('tipe')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('mode', 20)->nullable();
            $table->string('beasiswa')->nullable();
            $table->string('kategori')->nullable()->index();
            $table->string('founded')->nullable();
            $table->string('biaya')->nullable();
            $table->string('website')->nullable();   // link RESMI penyelenggara
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->text('benefits')->nullable();
            $table->string('avatar_color')->nullable();
            $table->string('youtube', 50)->nullable();
            $table->date('last_checked_at')->nullable();
            $table->boolean('is_sample')->default(false)->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
