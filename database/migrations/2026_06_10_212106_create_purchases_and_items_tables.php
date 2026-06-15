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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertanian_id')->constrained('pertanians')->onDelete('cascade');
            $table->string('store');
            $table->string('invoice_number')->nullable();
            $table->date('date');
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->string('category');
            $table->string('description');
            $table->decimal('qty', 10, 2);
            $table->decimal('unit_price', 20, 2);
            $table->decimal('total_price', 20, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
