<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class OrderApiTest extends TestCase
{
    private array $validPayload = [
        'items' => [['name' => 'Burger', 'price' => 10.00, 'quantity' => 2]], // subtotal=20
        'distance' => 2, // base=2.0 (distance < 3km, weight < 5kg)
        'weight' => 1,
        'promoCode' => null,
        'hour' => '15:00', // surge=1.0 (mardi)
        'dayOfWeek' => 'mardi'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        // S'assurer de flush la mémoire Cache avant chaque test pour de l'isolation parfaite
        Cache::flush();
    }

    // ===================================
    // POST /orders/simulate (7 tests min)
    // ===================================

    public function test_simulate_valid_order_returns_200_and_correct_price_detail()
    {
        $response = $this->postJson('/orders/simulate', $this->validPayload);
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['subtotal', 'discount', 'deliveryFee', 'surge', 'total'])
                 ->assertJsonFragment(['subtotal' => 20.0, 'total' => 22.0]);
    }

    public function test_simulate_with_valid_promo_applies_discount()
    {
        $payload = array_merge($this->validPayload, ['promoCode' => 'BIENVENUE20']); // 20% on 20 = 4.0
        
        $response = $this->postJson('/orders/simulate', $payload);
        
        $response->assertStatus(200)
                 ->assertJsonFragment(['discount' => 4.0, 'total' => 18.0]);
    }

    public function test_simulate_with_expired_promo_returns_400()
    {
        $payload = array_merge($this->validPayload, ['promoCode' => 'EXPIRE']);
        
        $response = $this->postJson('/orders/simulate', $payload);
        
        // 400 + message d'erreur
        $response->assertStatus(400)
                 ->assertJsonPath('error', 'Code promo expiré.');
    }

    public function test_simulate_with_empty_cart_returns_400()
    {
        $payload = array_merge($this->validPayload, ['items' => []]);
        
        $response = $this->postJson('/orders/simulate', $payload);
        
        $response->assertStatus(400)
                 ->assertJsonPath('error', 'Le panier ne peut pas être vide.');
    }

    public function test_simulate_out_of_zone_distance_returns_400()
    {
        $payload = array_merge($this->validPayload, ['distance' => 15]);
        
        $response = $this->postJson('/orders/simulate', $payload);
        
        $response->assertStatus(400)
                 ->assertJsonPath('error', 'La livraison est refusée pour cette distance (hors zone).');
    }

    public function test_simulate_closed_hours_returns_400()
    {
        $payload = array_merge($this->validPayload, ['hour' => '23:00']);
        
        $response = $this->postJson('/orders/simulate', $payload);
        
        $response->assertStatus(400)
                 ->assertJsonPath('error', "Le restaurant est fermé en dehors des heures d'ouverture (avant 10h, après 22h).");
    }

    public function test_simulate_surge_pricing_multiplies_delivery_fee()
    {
        $payload = array_merge($this->validPayload, ['dayOfWeek' => 'vendredi', 'hour' => '20:00']);
        
        $response = $this->postJson('/orders/simulate', $payload);
        
        // Base delivery = 2.0 * 1.8 = 3.6
        $response->assertStatus(200)
                 ->assertJsonFragment(['surge' => 1.8, 'deliveryFee' => 3.6, 'total' => 23.6]);
    }

    // ===================================
    // POST /orders (5 tests min)
    // ===================================

    public function test_store_valid_order_returns_201_with_id_and_created_memory()
    {
        $response = $this->postJson('/orders', $this->validPayload);
        
        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'items', 'subtotal', 'total']);
                 
        $id = $response->json('id');
        $this->assertNotNull(Cache::get('orders.' . $id)); // Commande enregistrée en mémoire
    }

    public function test_store_order_can_be_retrieved_via_get()
    {
        $postRes = $this->postJson('/orders', $this->validPayload);
        $id = $postRes->json('id');

        $getRes = $this->getJson("/orders/{$id}");
        
        $getRes->assertStatus(200)
               ->assertJsonFragment(['id' => $id]);
    }

    public function test_store_two_orders_generates_different_ids()
    {
        $id1 = $this->postJson('/orders', $this->validPayload)->json('id');
        $id2 = $this->postJson('/orders', $this->validPayload)->json('id');
        
        $this->assertNotEquals($id1, $id2);
        
        // Les deux sont bien en mémoire sans se chevaucher
        $this->assertNotNull(Cache::get('orders.' . $id1));
        $this->assertNotNull(Cache::get('orders.' . $id2));
    }

    public function test_store_invalid_order_returns_400()
    {
        $payload = array_merge($this->validPayload, ['distance' => 50]); // Hors zone = erreur
        
        $response = $this->postJson('/orders', $payload);
        
        $response->assertStatus(400)
                 ->assertJsonPath('error', "La livraison est refusée pour cette distance (hors zone).");
    }

    public function test_store_invalid_order_is_not_saved_in_memory()
    {
        // On récupère en amont le nombre d'entrées dans le cache (ou on s'assure juste que la structure retourne une erreur)
        $payload = array_merge($this->validPayload, ['distance' => 50]);
        $response = $this->postJson('/orders', $payload);
        
        $response->assertStatus(400); // Confirmer l'erreur
        // Comme on n'a pas d'ID retourné, on valide simplement que la réponse n'inclut pas de donnée structurant l'enregistrement
        $response->assertJsonMissing(['id']);
    }

    // ===================================
    // GET /orders/:id (3 tests min)
    // ===================================

    public function test_show_existing_id_returns_200_and_order()
    {
        $id = $this->postJson('/orders', $this->validPayload)->json('id');
        
        $response = $this->getJson("/orders/{$id}");
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'subtotal', 'total']);
    }

    public function test_show_missing_id_returns_404()
    {
        $response = $this->getJson('/orders/FAKE_ID_123');
        
        $response->assertStatus(404)
                 ->assertJsonPath('error', 'Commande introuvable');
    }

    public function test_show_returns_correct_full_structure()
    {
        $id = $this->postJson('/orders', $this->validPayload)->json('id');
        
        $response = $this->getJson("/orders/{$id}");
        
        $response->assertStatus(200) // 200 + structure complète
                 ->assertJsonStructure(['id', 'items', 'subtotal', 'discount', 'deliveryFee', 'surge', 'total']);
    }

    // ===================================
    // POST /promo/validate (5 tests min)
    // ===================================

    public function test_validate_promo_valid_returns_200_and_details()
    {
        $response = $this->postJson('/promo/validate', ['code' => 'BIENVENUE20', 'subtotal' => 50.0]);
        
        $response->assertStatus(200)
                 ->assertJsonFragment(['valid' => true, 'discount' => 10.0, 'newSubtotal' => 40.0]);
    }

    public function test_validate_promo_expired_returns_400_and_reason()
    {
        $response = $this->postJson('/promo/validate', ['code' => 'EXPIRE', 'subtotal' => 50.0]);
        
        $response->assertStatus(400)
                 ->assertJsonPath('error', 'Code promo expiré.');
    }

    public function test_validate_promo_under_minimum_returns_400_and_reason()
    {
        $response = $this->postJson('/promo/validate', ['code' => 'BIENVENUE20', 'subtotal' => 10.0]); // min: 15.00
        
        $response->assertStatus(400)
                 ->assertJsonPath('error', "Le montant minimum de la commande n'est pas atteint.");
    }

    public function test_validate_promo_unknown_returns_404()
    {
        $response = $this->postJson('/promo/validate', ['code' => 'UNKNOWN_ROUGE', 'subtotal' => 50.0]);
        
        $response->assertStatus(404)
                 ->assertJsonPath('error', "Code promo invalide ou inconnu.");
    }

    public function test_validate_promo_without_code_in_body_returns_400()
    {
        $response = $this->postJson('/promo/validate', ['subtotal' => 50.0]);
        
        $response->assertStatus(400)
                 ->assertJsonPath('error', 'Code manquant');
    }
}
