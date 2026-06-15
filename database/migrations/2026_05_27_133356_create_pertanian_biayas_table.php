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
        Schema::create('pertanian_biayas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertanian_id')->constrained('pertanians')->onDelete('cascade');
            $table->string('name');
            $table->integer('qty');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertanian_biayas');
    }
};
