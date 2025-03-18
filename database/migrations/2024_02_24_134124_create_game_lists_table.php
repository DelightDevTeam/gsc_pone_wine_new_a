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
        Schema::create('game_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id')->index(); // GameId field
            $table->unsignedBigInteger('game_type_id');
            $table->unsignedBigInteger('product_id');
            $table->boolean('status')->default(1);
            $table->boolean('hot_status')->default(0);
            $table->string('game_code', 50);             // GameCode field
            $table->string('game_name', 100);            // GameName field
            $table->unsignedInteger('game_type');        // GameType field
            $table->string('image_url');                 // ImageUrl field
            $table->string('method');                    // Method (e.g., Slots)
            $table->boolean('is_h5_support');            // IsH5Support field
            $table->string('maintenance')->nullable();   // Maintenance field
            $table->string('game_lobby_config')->nullable(); // GameLobbyConfig field
            $table->json('other_name')->nullable();       // OtherName field (JSON format)
            $table->boolean('has_demo');                 // HasDemo field
            $table->unsignedInteger('sequence');         // Sequence field
            $table->string('game_event')->nullable();    // GameEvent field (nullable)
            $table->string('game_provide_code', 50);     // GameProvideCode field
            $table->string('game_provide_name', 100);    // GameProvideName field

            $table->timestamps();
            $table->foreign('game_type_id')->references('id')->on('game_types')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_lists');
    }
};
