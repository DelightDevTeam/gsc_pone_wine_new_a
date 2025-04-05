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
        Schema::create('seamless_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('message_id');
            $table->string('product_id');
            $table->json('raw_data')->nullable();

            // $table->string('operator_id')->nullable();
            // $table->string('provider_id')->nullable();
            // $table->string('provider_line_id')->nullable();
            // $table->string('currency_id')->nullable();
            // $table->string('game_type');
            // $table->string('game_id');
            // $table->string('game_round_id');
            // $table->text('payout_detail')->nullable();
            // $table->decimal('commission_amount', 12)->nullable();
            // $table->decimal('jackpot_amount', 12)->nullable();
            // $table->string('operator_code')->nullable();
            // $table->string('sign')->nullable();
            // $table->decimal('jp_bet', 12)->nullable();
            $table->timestamp('request_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seamless_events');
    }
};
