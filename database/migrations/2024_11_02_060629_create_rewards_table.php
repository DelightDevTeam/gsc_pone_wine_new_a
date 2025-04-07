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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('operator_id', 20);
            $table->string('request_date_time', 50);
            $table->string('signature', 50);
            $table->string('player_id', 50);
            $table->string('currency', 5);
            $table->string('tran_id', 30)->unique();
            $table->string('reward_id', 50);
            $table->string('reward_name', 100);
            $table->decimal('amount', 18, 4);
            $table->string('tran_date_time', 100);
            $table->string('reward_detail', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
