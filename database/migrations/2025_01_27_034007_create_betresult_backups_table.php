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
        Schema::create('betresult_backups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // User_id
            $table->string('operator_id', 20);
            $table->string('request_date_time', 50); // Using string for format flexibility
            $table->string('signature', 50);
            $table->string('player_id', 50);
            $table->string('currency', 5)->default('THB');
            $table->string('tran_id', 30);
            $table->string('game_code', 50);
            $table->decimal('bet_amount', 18, 4)->default('0.00');   // Decimal with 4 precision
            $table->decimal('win_amount', 18, 4)->default('0.00');   // Decimal with 4 precision
            $table->decimal('net_win', 18, 4)->default(0.00);      // Decimal with 4 precision
            //$table->timestamp('tran_date_time')->useCurrent(); // ISO 8601 Standard timestamp
            //$table->timestamp('tran_date_time');

            $table->string('tran_date_time', 100);
            $table->string('provider_code')->default('code_1');
            $table->string('auth_token', 500)->default('default_password');
            $table->string('status')->default('unprocessed'); // Default to 'unprocessed' for new transactions
            $table->timestamp('cancelled_at')->nullable(); // Set when transaction is cancelled

            $table->decimal('old_balance', 18, 4)->nullable();      // Decimal with 4 precision
            $table->decimal('new_balance', 18, 4)->nullable();      // Decimal with 4 precision
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('betresult_backups');
    }
};
