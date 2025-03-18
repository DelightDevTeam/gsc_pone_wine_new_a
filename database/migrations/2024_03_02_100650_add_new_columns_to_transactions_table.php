<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            <<<'SQL'
            ALTER TABLE transactions
            ADD COLUMN new_balance VARCHAR(191) GENERATED ALWAYS AS ( json_unquote(json_extract(meta, '$.new_balance'))) STORED,
            ADD COLUMN old_balance VARCHAR(191) GENERATED ALWAYS AS ( json_unquote(json_extract(meta, '$.old_balance'))) STORED
            SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
