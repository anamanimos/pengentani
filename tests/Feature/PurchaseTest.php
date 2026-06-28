<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pertanian;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Kebun;
use App\Models\Entity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTest extends TestCase {
    use RefreshDatabase;
    public function test_store_purchase() {
        $user = User::factory()->create();
        
        $kebun = Kebun::create([
            'user_id' => $user->id,
            'name' => 'Kebun A',
            'status' => 'published',
        ]);
        $pengelola = Entity::create([
            'name' => 'Pengelola A',
            'type' => 'pengelola',
        ]);
        $pertanian = Pertanian::create([
            'user_id' => $user->id,
            'kebun_id' => $kebun->id,
            'admin_id' => $user->id,
            'pengelola_entity_id' => $pengelola->id,
            'name' => 'Tanaman Cabe',
            'status' => 'Sedang Berjalan',
        ]);
        
        $response = $this->actingAs($user)->postJson('/console/purchases', [
            'data' => [
                [
                    'index' => 5,
                    'date' => '2026-06-25',
                    'pertanian_id' => 'Tanaman Cabe',
                    'qty' => '10',
                    'unit_price' => '15000',
                ]
            ]
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals(1, PurchaseItem::count());
    }
}
