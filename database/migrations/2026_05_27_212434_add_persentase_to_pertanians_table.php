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
            $table->integer('persentase_investor')->default(0)->after('status');
            $table->integer('persentase_pengelola')->default(0)->after('persentase_investor');
            $table->integer('persentase_admin')->default(0)->after('persentase_pengelola');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanians', function (Blueprint $table) {
            $table->dropColumn(['persentase_investor', 'persentase_pengelola', 'persentase_admin']);
        });
    }
};
