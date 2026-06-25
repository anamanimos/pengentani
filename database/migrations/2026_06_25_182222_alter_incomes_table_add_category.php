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
        // 1. Create categories if not exists
        $panenId = DB::table('income_categories')->insertGetId(['name' => 'Panen', 'created_at' => now(), 'updated_at' => now()]);
        $lainId = DB::table('income_categories')->insertGetId(['name' => 'Lain-lain', 'created_at' => now(), 'updated_at' => now()]);

        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('income_category_id')->nullable()->constrained()->nullOnDelete();
        });

        // 2. Migrate existing data
        DB::table('incomes')->where('type', 'Panen')->update(['income_category_id' => $panenId]);
        DB::table('incomes')->where('type', 'Lain-lain')->update(['income_category_id' => $lainId]);
        DB::table('incomes')->whereNull('income_category_id')->update(['income_category_id' => $panenId]); // fallback

        // 3. Drop old column
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->enum('type', ['Panen', 'Lain-lain'])->default('Panen');
        });
        
        // Cannot easily reverse data, but we can set defaults
        DB::table('incomes')->update(['type' => 'Panen']);

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['income_category_id']);
            $table->dropColumn('income_category_id');
        });
    }
};
