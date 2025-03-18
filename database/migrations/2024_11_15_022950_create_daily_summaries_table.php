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
        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date');
            $table->string('currency_code', 10);
            $table->decimal('turnover', 18, 2);
            $table->decimal('valid_turnover', 18, 2);
            $table->decimal('payout', 18, 2);
            $table->decimal('win_lose', 18, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
    }
};
