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
        Schema::table('pertanians', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pengelola_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanians', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['pengelola_id']);
            $table->dropColumn(['admin_id', 'pengelola_id']);
        });
    }
};
