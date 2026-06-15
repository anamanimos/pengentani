<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default categories
        $defaultCategories = [
            'Bibit / Benih' => 'Kategori untuk bibit tanaman, benih, dan sejenisnya.',
            'Pupuk' => 'Kategori untuk pupuk organik, kimia, kompos, dan sejenisnya.',
            'Pestisida' => 'Kategori untuk obat-obatan tanaman, pembasmi hama, insektisida, dan sejenisnya.',
            'Peralatan' => 'Kategori untuk alat-alat tani seperti cangkul, sabit, instalasi pengairan, dll.',
            'Tenaga Kerja' => 'Kategori untuk biaya upah pekerja harian atau borongan.',
            'Lain-lain' => 'Kategori untuk pengeluaran di luar kategori utama.'
        ];

        $categoryMapping = [];
        foreach ($defaultCategories as $name => $desc) {
            $id = DB::table('purchase_categories')->insertGetId([
                'name' => $name,
                'description' => $desc,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $categoryMapping[$name] = $id;
        }

        // Alter purchase_items table to add foreign key
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('purchase_category_id')
                ->nullable()
                ->after('purchase_id')
                ->constrained('purchase_categories')
                ->onDelete('set null');
        });

        // Migrate existing items to new categories
        foreach ($categoryMapping as $name => $id) {
            DB::table('purchase_items')
                ->where('category', $name)
                ->update(['purchase_category_id' => $id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_category_id']);
            $table->dropColumn('purchase_category_id');
        });

        Schema::dropIfExists('purchase_categories');
    }
};
