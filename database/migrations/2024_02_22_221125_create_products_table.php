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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('provider_code', 50);  // ProviderCode field
            $table->string('provider_name', 100);  // ProviderName field
            $table->boolean('is_active');  // IsActive field
            $table->integer('order')->default(0);
            $table->integer('status')->default(1);
            $table->boolean('game_list_status')->default(1); // 1 game list ok / 0 lobby
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
