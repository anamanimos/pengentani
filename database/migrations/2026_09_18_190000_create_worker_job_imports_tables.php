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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_hash')->nullable()->index();
            $table->string('source_channel')->default('photo'); // photo, json_manual
            $table->string('detected_worker_name')->nullable();
            $table->string('period_summary')->nullable();
            $table->string('status')->default('draft'); // draft, processing, waiting_review, completed, failed
            $table->integer('total_rows')->default(0);
            $table->decimal('total_wage', 15, 2)->default(0);
            $table->decimal('total_konsumsi', 15, 2)->default(0);
            $table->text('error_message')->nullable();
            $table->longText('raw_json')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('import_staging_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->constrained('import_logs')->cascadeOnDelete();
            $table->date('date');
            $table->string('raw_date_text')->nullable();
            $table->foreignId('pertanian_id')->nullable()->constrained('pertanians')->nullOnDelete();
            $table->string('raw_kebun_code')->nullable();
            $table->foreignId('worker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('raw_worker_name')->nullable();
            $table->foreignId('job_category_id')->nullable()->constrained('job_categories')->nullOnDelete();
            $table->string('raw_job_name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('wage', 15, 2)->default(0);
            $table->decimal('konsumsi', 15, 2)->default(0);
            $table->string('status')->default('unpaid'); // paid, unpaid
            $table->boolean('confidence_rendah')->default(false);
            $table->string('status_baris')->default('ok'); // ok, perlu_review
            $table->text('review_notes')->nullable();
            $table->boolean('disertakan')->default(true);
            $table->foreignId('created_worker_job_id')->nullable()->constrained('worker_jobs')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('import_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // pertanian, pekerja, kategori
            $table->string('raw_label')->index();
            $table->unsignedBigInteger('target_id');
            $table->string('target_name')->nullable();
            $table->timestamps();

            $table->unique(['type', 'raw_label']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_mappings');
        Schema::dropIfExists('import_staging_rows');
        Schema::dropIfExists('import_logs');
    }
};
