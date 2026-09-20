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
        if (Schema::hasTable('incomes') && !Schema::hasColumn('incomes', 'key')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->string('key')->nullable()->after('description');
            });
        }

        if (Schema::hasTable('purchases') && !Schema::hasColumn('purchases', 'key')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->string('key')->nullable()->after('invoice_number');
            });
        }

        if (Schema::hasTable('purchase_items') && !Schema::hasColumn('purchase_items', 'key')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->string('key')->nullable()->after('description');
            });
        }

        if (Schema::hasTable('worker_jobs') && !Schema::hasColumn('worker_jobs', 'key')) {
            Schema::table('worker_jobs', function (Blueprint $table) {
                $table->string('key')->nullable()->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'key')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropColumn('key');
            });
        }

        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'key')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('key');
            });
        }

        if (Schema::hasTable('purchase_items') && Schema::hasColumn('purchase_items', 'key')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->dropColumn('key');
            });
        }

        if (Schema::hasTable('worker_jobs') && Schema::hasColumn('worker_jobs', 'key')) {
            Schema::table('worker_jobs', function (Blueprint $table) {
                $table->dropColumn('key');
            });
        }
    }
};
