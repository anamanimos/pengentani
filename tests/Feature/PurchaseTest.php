<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pertanian;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTest extends TestCase {
    use RefreshDatabase;
    public function test_store_purchase() {
        $user = User::factory()->create();
        $pertanian = Pertanian::factory()->create(['user_id' => $user->id, 'name' => 'Tanaman Cabe']);
        
        $response = $this->actingAs($user)->postJson('/console/purchases/store', [
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
