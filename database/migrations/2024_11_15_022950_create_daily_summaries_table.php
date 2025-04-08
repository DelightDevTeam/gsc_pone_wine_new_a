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
            $table->date('report_date');
            $table->string('member_name')->nullable(); // Null for agent/overall summaries
            $table->unsignedBigInteger('agent_id')->nullable(); // Null for overall summaries
            $table->bigInteger('total_valid_bet_amount')->default(0);
            $table->bigInteger('total_payout_amount')->default(0);
            $table->bigInteger('total_bet_amount')->default(0);
            $table->bigInteger('total_win_amount')->default(0);
            $table->bigInteger('total_lose_amount')->default(0);
            $table->integer('total_stake_count')->default(0);
            $table->timestamps();
            $table->unique(['report_date', 'member_name', 'agent_id']);
            $table->index('report_date');
            $table->index('member_name');
            $table->index('agent_id');
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
