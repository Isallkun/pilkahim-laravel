<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            // Toggle visibility countdown sisa waktu di public (landing, ballot, etc).
            // Default true = perilaku existing tetap.
            $table->boolean('show_countdown')->default(true)->after('result_visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn('show_countdown');
        });
    }
};
