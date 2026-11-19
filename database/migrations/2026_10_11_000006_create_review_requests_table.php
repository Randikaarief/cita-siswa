<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scholarship_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('competition_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->string('status', 20)->default('pending')->index(); // pending | replied
            $table->timestamp('replied_at')->nullable(); // diisi oleh sisi mentor
            $table->timestamps();
        });

        // Catatan klik "Apply Now". Hanya riwayat klik, BUKAN bukti pendaftaran.
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_type', 20); // scholarship | competition
            $table->unsignedBigInteger('target_id');
            $table->string('status', 20)->default('opened');
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('review_requests');
    }
};
