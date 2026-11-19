<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            // Nama kolom sengaja sama dengan kunci array lama di routes/web.php
            // supaya view Blade tidak perlu diubah.
            $table->string('nama');
            $table->string('sub')->nullable();
            $table->string('kategori')->nullable()->index();
            $table->string('tipe')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('deadline')->nullable();
            $table->string('posted')->nullable();
            $table->string('level')->nullable();
            $table->string('color')->nullable();
            $table->string('logo_text', 10)->nullable();
            $table->string('founded')->nullable();
            $table->string('org_type')->nullable();
            $table->string('size')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();   // link RESMI penyelenggara
            $table->text('desc')->nullable();
            $table->text('requirements')->nullable();
            $table->date('last_checked_at')->nullable(); // terakhir dicek ke sumber resmi
            $table->boolean('is_sample')->default(false)->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
