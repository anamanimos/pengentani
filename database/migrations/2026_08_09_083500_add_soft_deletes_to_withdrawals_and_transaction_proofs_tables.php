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
        if (!Schema::hasColumn('withdrawals', 'deleted_at')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('transaction_proofs', 'deleted_at')) {
            Schema::table('transaction_proofs', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('withdrawals', 'deleted_at')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('transaction_proofs', 'deleted_at')) {
            Schema::table('transaction_proofs', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
