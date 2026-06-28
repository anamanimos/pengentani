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
        // 1. Create `entities` table
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('investor')->comment('investor, pengelola');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // 2. Create `entity_user` table
        Schema::create('entity_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('primary');
            $table->timestamps();
        });

        // 3. Add entity_id to pertanian_investors
        Schema::table('pertanian_investors', function (Blueprint $table) {
            $table->foreignId('entity_id')->nullable()->constrained('entities')->onDelete('cascade')->after('id');
        });

        // 4. Data Migration for pertanian_investors
        $investors = DB::table('pertanian_investors')->select('user_id')->distinct()->get();
        foreach ($investors as $investor) {
            $user = DB::table('users')->where('id', $investor->user_id)->first();
            if ($user) {
                $existingEntityUser = DB::table('entity_user')->where('user_id', $user->id)->first();
                if (!$existingEntityUser) {
                    $entityId = DB::table('entities')->insertGetId([
                        'name' => $user->name . ' (Investor)',
                        'type' => 'investor',
                        'phone' => $user->whatsapp ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    DB::table('entity_user')->insert([
                        'entity_id' => $entityId,
                        'user_id' => $user->id,
                        'role' => 'primary',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $entityId = $existingEntityUser->entity_id;
                }
                
                DB::table('pertanian_investors')
                    ->where('user_id', $user->id)
                    ->update(['entity_id' => $entityId]);
            }
        }

        // 5. Add pengelola_entity_id to pertanians
        Schema::table('pertanians', function (Blueprint $table) {
            $table->foreignId('pengelola_entity_id')->nullable()->constrained('entities')->onDelete('set null')->after('user_id');
        });

        // 6. Data Migration for pengelola
        $pengelolas = DB::table('pertanians')->whereNotNull('pengelola_id')->select('pengelola_id')->distinct()->get();
        foreach ($pengelolas as $p) {
            $user = DB::table('users')->where('id', $p->pengelola_id)->first();
            if ($user) {
                $existingEntityUser = DB::table('entity_user')->where('user_id', $user->id)->first();
                if (!$existingEntityUser) {
                    $entityId = DB::table('entities')->insertGetId([
                        'name' => $user->name . ' (Pengelola)',
                        'type' => 'pengelola',
                        'phone' => $user->whatsapp ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    DB::table('entity_user')->insert([
                        'entity_id' => $entityId,
                        'user_id' => $user->id,
                        'role' => 'primary',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $entityId = $existingEntityUser->entity_id;
                }
                
                DB::table('pertanians')
                    ->where('pengelola_id', $user->id)
                    ->update(['pengelola_entity_id' => $entityId]);
            }
        }

        // 7. Drop user_id from pertanian_investors
        Schema::table('pertanian_investors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // 8. Drop pengelola_id from pertanians
        Schema::table('pertanians', function (Blueprint $table) {
            $table->dropForeign(['pengelola_id']);
            $table->dropColumn('pengelola_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanian_investors', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
        });

        Schema::table('pertanians', function (Blueprint $table) {
            $table->foreignId('pengelola_id')->nullable()->constrained('users')->onDelete('set null');
        });

        $investors = DB::table('pertanian_investors')->whereNotNull('entity_id')->get();
        foreach ($investors as $investor) {
            $entityUser = DB::table('entity_user')->where('entity_id', $investor->entity_id)->where('role', 'primary')->first();
            if ($entityUser) {
                DB::table('pertanian_investors')->where('id', $investor->id)->update(['user_id' => $entityUser->user_id]);
            }
        }

        $pertanians = DB::table('pertanians')->whereNotNull('pengelola_entity_id')->get();
        foreach ($pertanians as $pertanian) {
            $entityUser = DB::table('entity_user')->where('entity_id', $pertanian->pengelola_entity_id)->where('role', 'primary')->first();
            if ($entityUser) {
                DB::table('pertanians')->where('id', $pertanian->id)->update(['pengelola_id' => $entityUser->user_id]);
            }
        }

        Schema::table('pertanian_investors', function (Blueprint $table) {
            $table->dropForeign(['entity_id']);
            $table->dropColumn('entity_id');
        });

        Schema::table('pertanians', function (Blueprint $table) {
            $table->dropForeign(['pengelola_entity_id']);
            $table->dropColumn('pengelola_entity_id');
        });

        Schema::dropIfExists('entity_user');
        Schema::dropIfExists('entities');
    }
};
