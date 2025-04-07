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
        Schema::table('results', function (Blueprint $table) {
            $table->decimal('old_balance', 16, 4)->nullable()->after('tran_date_time');
            $table->decimal('new_balance', 16, 4)->nullable()->after('old_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn(['old_balance', 'new_balance']);
        });
    }
};
