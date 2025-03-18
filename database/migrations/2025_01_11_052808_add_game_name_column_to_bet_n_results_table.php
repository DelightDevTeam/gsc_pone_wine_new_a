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
        Schema::table('bet_n_results', function (Blueprint $table) {
            $table->string('game_name', 100)->nullable()->after('game_code'); // Add game_name column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bet_n_results', function (Blueprint $table) {
            $table->dropColumn('game_name'); // Rollback
        });
    }
};
