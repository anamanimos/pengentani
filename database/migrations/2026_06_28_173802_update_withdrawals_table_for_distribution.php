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
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('type')->default('bagi_hasil')->after('user_id')->comment('bagi_hasil, pengembalian_modal, zakat');
            $table->foreignId('user_id')->nullable()->change();
            $table->string('role')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->foreignId('user_id')->nullable(false)->change();
            $table->string('role')->nullable(false)->change();
        });
    }
};
