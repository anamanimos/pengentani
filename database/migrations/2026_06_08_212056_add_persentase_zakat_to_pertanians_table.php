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
            $table->decimal('persentase_zakat', 5, 2)->default(5.00)->after('status')->comment('zakat percentage (e.g. 2.5, 5, 10)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanians', function (Blueprint $table) {
            $table->dropColumn('persentase_zakat');
        });
    }
};
