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
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); // User reference
            $table->string('operator_id', 20); // OperatorId
            $table->string('request_date_time', 50); // RequestDateTime
            $table->string('signature', 50); // Signature
            $table->string('player_id', 50); // PlayerId
            $table->string('currency', 5); // Currency
            $table->string('tran_id', 30)->unique(); // TranId, unique for idempotency
            $table->decimal('amount', 18, 4); // Adjustment amount, positive for add, negative for deduct
            $table->string('tran_date_time', 100); // TranDateTime in ISO 8601
            $table->string('remark', 100)->nullable(); // Remark for adjustment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjustments');
    }
};
