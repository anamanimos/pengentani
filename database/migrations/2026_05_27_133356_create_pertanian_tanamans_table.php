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
        Schema::create('pertanian_tanamans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertanian_id')->constrained('pertanians')->onDelete('cascade');
            $table->foreignId('tanaman_id')->constrained('tanamans')->onDelete('cascade');
            $table->integer('qty_pohon');
            $table->decimal('estimasi_berat_per_pohon', 10, 2)->nullable();
            $table->decimal('estimasi_harga_per_kg', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertanian_tanamans');
    }
};
