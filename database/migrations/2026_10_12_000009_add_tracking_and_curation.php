<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---- Pelacakan sumber kampanye (UTM) ----
        Schema::table('users', function (Blueprint $table) {
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable()->index();
        });

        // Satu baris per pengunjung per kampanye (alamat IP tidak disimpan).
        Schema::create('campaign_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id', 36);
            $table->string('utm_source', 100);
            $table->string('utm_medium', 100)->default('');
            $table->string('utm_campaign', 100)->default('')->index();
            $table->string('landing_path')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['visitor_id', 'utm_source', 'utm_medium', 'utm_campaign'], 'campaign_visits_unique');
        });

        // Angka impresi dari Instagram, diisi manual oleh admin (untuk menghitung CTR).
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('utm_campaign', 100)->unique();
            $table->unsignedBigInteger('impressions')->nullable();
            $table->timestamps();
        });

        // ---- Kurasi berkas oleh mentor ----
        Schema::table('review_requests', function (Blueprint $table) {
            $table->text('feedback')->nullable();           // masukan mentor
            $table->string('decline_reason')->nullable();   // alasan kalau mentor belum bisa membantu
        });

        // Berkas yang dibagikan siswa pada satu pengajuan. Dihapus otomatis kalau berkas/pengajuan dihapus.
        Schema::create('review_request_submission', function (Blueprint $table) {
            $table->foreignId('review_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->primary(['review_request_id', 'submission_id']);
        });

        // Batas pengajuan aktif per mentor (kosong = tanpa batas).
        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_active_requests')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mentor_profiles', fn (Blueprint $t) => $t->dropColumn('max_active_requests'));
        Schema::dropIfExists('review_request_submission');
        Schema::table('review_requests', fn (Blueprint $t) => $t->dropColumn(['feedback', 'decline_reason']));
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('campaign_visits');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['utm_source', 'utm_medium', 'utm_campaign']));
    }
};
