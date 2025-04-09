<?php

use App\Enums\TransactionStatus;
use App\Enums\WagerStatus;
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
        Schema::create('seamless_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seamless_event_id');
            $table->foreignId('user_id');
            $table->foreignId('game_type_id');
            $table->foreignId('product_id');
            $table->unsignedBigInteger('wager_id')->nullable();
            $table->decimal('valid_bet_amount', 12);
            $table->decimal('bet_amount', 12);
            $table->decimal('transaction_amount', 12);
            $table->string('transaction_id')->unique();
            $table->decimal('rate')->nullable();
            $table->decimal('payout_amount', 12)->default('0.00');
            //$table->timestamp('settlement_date')->nullable();
            $table->string('status')->default(TransactionStatus::Pending);
            $table->string('wager_status')->default(WagerStatus::Ongoing->value);
            //$table->timestamp('created_on')->nullable();
            //$table->timestamp('modified_on')->nullable();
            $table->string('member_name')->nullable();
            //$table->timestamp('request_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seamless_transactions');
    }
};
