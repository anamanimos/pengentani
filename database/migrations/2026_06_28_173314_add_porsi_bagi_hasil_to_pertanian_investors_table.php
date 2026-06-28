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
        Schema::table('pertanian_investors', function (Blueprint $table) {
            $table->decimal('porsi_bagi_hasil', 5, 2)->nullable()->after('besaran_investasi')->comment('Persentase porsi bagi hasil kustom, misal 50.00 untuk 50%');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanian_investors', function (Blueprint $table) {
            $table->dropColumn('porsi_bagi_hasil');
        });
    }
};
