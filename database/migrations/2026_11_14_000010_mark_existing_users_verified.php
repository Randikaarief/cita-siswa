<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Verifikasi email baru diwajibkan mulai sekarang.
 * Akun yang sudah ada sebelumnya ditandai terverifikasi supaya tidak terkunci dari fitur unggah berkas.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Tidak ada yang perlu dikembalikan.
    }
};
