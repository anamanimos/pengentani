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
        Schema::table('incomes', function (Blueprint $table) {
            $table->date('date')->nullable()->change();
            $table->enum('type', ['Panen', 'Lain-lain'])->nullable()->default(null)->change();
            $table->decimal('amount', 15, 2)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->date('date')->nullable(false)->change();
            $table->enum('type', ['Panen', 'Lain-lain'])->nullable(false)->default('Panen')->change();
            $table->decimal('amount', 15, 2)->nullable(false)->default(0)->change();
        });
    }
};
