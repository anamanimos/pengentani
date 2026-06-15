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
            $table->decimal('batasan_investasi', 15, 2)->nullable()->after('persentase_admin')->comment('Batas maksimal investasi, opsional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanians', function (Blueprint $table) {
            $table->dropColumn('batasan_investasi');
        });
    }
};
