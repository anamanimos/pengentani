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
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
        });

        // Migrate existing data (optional but good practice)
        $purchases = \DB::table('purchases')->get();
        foreach ($purchases as $p) {
            if ($p->store) {
                $store = \DB::table('stores')->where('name', $p->store)->first();
                if (!$store) {
                    $storeId = \DB::table('stores')->insertGetId([
                        'name' => $p->store,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $storeId = $store->id;
                }
                \DB::table('purchases')->where('id', $p->id)->update(['store_id' => $storeId]);
            }
        }

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('store');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('store')->nullable();
        });

        $purchases = \DB::table('purchases')->get();
        foreach ($purchases as $p) {
            if ($p->store_id) {
                $storeName = \DB::table('stores')->where('id', $p->store_id)->value('name');
                \DB::table('purchases')->where('id', $p->id)->update(['store' => $storeName]);
            }
        }

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
