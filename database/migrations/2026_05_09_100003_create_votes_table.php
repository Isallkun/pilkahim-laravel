<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel votes HANYA menyimpan pilihan kandidat — TANPA user_id.
 * Ini kunci anonimitas: tidak ada cara menghubungkan suara ke pemilih.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained();
            $table->foreignId('candidate_id')->constrained();
            $table->timestamp('voted_at');
            // TIDAK ADA user_id — ini kunci anonimitas
            $table->index(['election_id', 'candidate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
