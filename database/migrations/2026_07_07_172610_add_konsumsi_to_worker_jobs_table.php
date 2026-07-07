<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_jobs', function (Blueprint $table) {
            $table->decimal('konsumsi', 15, 2)->default(0)->after('wage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_jobs', function (Blueprint $table) {
            $table->dropColumn('konsumsi');
        });
    }
};
