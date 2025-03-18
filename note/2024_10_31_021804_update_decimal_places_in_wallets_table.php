<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            //$table->unsignedSmallInteger('decimal_places')->default(4)->change();
            DB::statement('ALTER TABLE wallets MODIFY decimal_places SMALLINT UNSIGNED DEFAULT 4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->unsignedSmallInteger('decimal_places')->default(2)->change();
        });
    }
};
