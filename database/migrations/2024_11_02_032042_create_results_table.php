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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); // User ID
            $table->string('player_name')->nullable();
            $table->string('game_provide_name', 100);    // GameProvideName field
            $table->string('game_name', 100);
            $table->string('operator_id', 20);
            $table->string('request_date_time', 50);
            $table->string('signature', 50);
            $table->string('player_id', 50);
            $table->string('currency', 5);
            $table->string('round_id', 30);
            $table->json('bet_ids'); // Array of Bet IDs
            $table->string('result_id', 30);
            $table->string('game_code', 50);
            $table->decimal('total_bet_amount', 18, 4);
            $table->decimal('win_amount', 18, 4);
            $table->decimal('net_win', 18, 4);
            $table->string('tran_date_time', 100)->default(now());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
