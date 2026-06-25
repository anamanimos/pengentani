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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('transaction_proof_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('transaction_proof_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('worker_jobs', function (Blueprint $table) {
            $table->foreignId('transaction_proof_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['transaction_proof_id']);
            $table->dropColumn('transaction_proof_id');
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['transaction_proof_id']);
            $table->dropColumn('transaction_proof_id');
        });

        Schema::table('worker_jobs', function (Blueprint $table) {
            $table->dropForeign(['transaction_proof_id']);
            $table->dropColumn('transaction_proof_id');
        });
    }
};
