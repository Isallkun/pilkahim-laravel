<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot table: DPT per election.
 * Tracking has_voted per election (bukan global di users).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('has_voted')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->unique(['election_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_user');
    }
};
