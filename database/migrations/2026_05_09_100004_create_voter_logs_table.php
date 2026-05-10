<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel voter_logs HANYA menyimpan siapa yang sudah voting — TANPA candidate_id.
 * Ini kunci anonimitas: tidak ada cara menghubungkan pemilih ke pilihan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voter_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('election_id')->constrained();
            $table->string('evidence_token', 64)->unique();
            $table->string('ip_address', 45);
            $table->timestamp('voted_at');
            // TIDAK ADA candidate_id — ini kunci anonimitas
            $table->unique(['user_id', 'election_id']); // Cegah voting ganda di level DB
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voter_logs');
    }
};
