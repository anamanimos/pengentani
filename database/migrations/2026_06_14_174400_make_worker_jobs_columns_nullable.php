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
        Schema::table('worker_jobs', function (Blueprint $table) {
            $table->foreignId('worker_id')->nullable()->change();
            $table->foreignId('job_category_id')->nullable()->change();
            $table->date('date')->nullable()->change();
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
            $table->decimal('wage', 15, 2)->nullable()->default(null)->change();
            $table->string('status')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_jobs', function (Blueprint $table) {
            $table->foreignId('worker_id')->nullable(false)->change();
            $table->foreignId('job_category_id')->nullable(false)->change();
            $table->date('date')->nullable(false)->change();
            $table->time('start_time')->nullable(false)->change();
            $table->time('end_time')->nullable(false)->change();
            $table->decimal('wage', 15, 2)->nullable(false)->default(0)->change();
            $table->string('status')->nullable(false)->default('unpaid')->change();
        });
    }
};
