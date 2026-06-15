<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pertanians', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        // Populate existing records with UUIDs
        $pertanians = DB::table('pertanians')->get();
        foreach ($pertanians as $p) {
            DB::table('pertanians')
                ->where('id', $p->id)
                ->update(['uuid' => (string) Str::uuid()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanians', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
