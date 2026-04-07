<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PricingEngine;
use InvalidArgumentException;
use Exception;

class OrderTotalTest extends TestCase
{
    private array $promoCodes = [];
    private array $defaultItems = [];

    protected function setUp(): void
    {
        parent::setUp();

        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $this->promoCodes = [
            ['code' => 'PERCENT20', 'type' => 'percentage', 'value' => 20, 'minOrder' => 10.0, 'expiresAt' => $tomorrow],
        ];

        // 2 pizzas à 12.50€ (sous-total: 25.0)
        $this->defaultItems = [
            ['name' => 'Pizza', 'price' => 12.50, 'quantity' => 2]
        ];
    }

    // ============================================
    // CAS NORMAUX / SCÉNARIOS COMPLETS
    // ============================================

    public function test_should_calculate_full_scenario_without_promo_on_tuesday(): void
    {
        // 2 pizzas a 12.50€ (sub=25) + 5 km (base=3.0) + 1kg + mardi 15h (surge=1.0) + pas de promo
        $result = PricingEngine::calculateOrderTotal(
            $this->defaultItems,
            5,
            1,
            null,
            '15h',
            'mardi',
            $this->promoCodes
        );

        $this->assertEquals(25.0, $result['subtotal']);
        $this->assertEquals(0.0, $result['discount']);
        $this->assertEquals(3.0, $result['deliveryFee']);
        $this->assertEquals(1.0, $result['surge']);
        $this->assertEquals(28.0, $result['total']); // subtotal + deliveryFee - discount
    }

    public function test_should_calculate_full_scenario_with_20_percent_promo(): void
    {
        // Même scénario avec promo PERCENT20
        $result = PricingEngine::calculateOrderTotal(
            $this->defaultItems,
            5,
            1,
            'PERCENT20',
            '15h',
            'mardi',
            $this->promoCodes
        );

        $this->assertEquals(25.0, $result['subtotal']);
        $this->assertEquals(5.0, $result['discount']); // 20% de 25€ = 5€
        $this->assertEquals(3.0, $result['deliveryFee']);
        $this->assertEquals(1.0, $result['surge']);
        $this->assertEquals(23.0, $result['total']); // 20.0 + 3.0
    }

    public function test_should_calculate_full_scenario_on_friday_evening_with_high_surge(): void
    {
        // Vendredi à 20h : surge = 1.8
        // Livraison base = 3.0 * 1.8 = 5.4
        $result = PricingEngine::calculateOrderTotal(
            $this->defaultItems,
            5,
            1,
            null,
            '20h',
            'vendredi',
            $this->promoCodes
        );

        $this->assertEquals(25.0, $result['subtotal']);
        $this->assertEquals(0.0, $result['discount']);
        $this->assertEquals(5.4, $result['deliveryFee']);
        $this->assertEquals(1.8, $result['surge']);
        $this->assertEquals(30.4, $result['total']); // 25.0 + 5.4
    }

    // ============================================
    // VÉRIFICATIONS MATHÉMATIQUES & STRUCTURE
    // ============================================

    public function test_should_return_correct_structure_and_round_decimals_correctly(): void
    {
        $items = [['name' => 'Burger', 'price' => 11.99, 'quantity' => 1]]; // subtotal = 11.99
        $result = PricingEngine::calculateOrderTotal($items, 2, 1, null, '12:45', 'mercredi'); // dej=1.3, delivBase=2.0 -> deliv=2.6

        // On vérifie que la structure est stricte avec des floats
        $this->assertIsArray($result);
        $this->assertArrayHasKey('subtotal', $result);
        $this->assertArrayHasKey('discount', $result);
        $this->assertArrayHasKey('deliveryFee', $result);
        $this->assertArrayHasKey('surge', $result);
        $this->assertArrayHasKey('total', $result);

        $this->assertEquals(11.99, $result['subtotal']); // subtotal
        $this->assertEquals(0.0, $result['discount']); // discount = 0 sans code promo
        $this->assertEquals(2.60, $result['deliveryFee']); // Arrondi à 2 décimales
        $this->assertEquals(14.59, $result['total']); // 11.99 + 2.6
    }

    public function test_should_ensure_surge_applies_strictly_only_to_delivery(): void
    {
        // Ce test prouve bien que subtotal * surge N'EST PAS fait.
        $result = PricingEngine::calculateOrderTotal($this->defaultItems, 5, 1, null, '20h', 'vendredi', $this->promoCodes);

        // base = 2+2*0.5 = 3
        $expectedDeliveryWithSurge = 3.0 * 1.8;
        $this->assertEquals($expectedDeliveryWithSurge, $result['deliveryFee']);

        $this->assertEquals($result['subtotal'] + $result['deliveryFee'], $result['total']);
    }

    // ============================================
    // CAS D'ERREURS (ROUGES)
    // ============================================

    public function test_should_throw_error_when_cart_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Le panier ne peut pas être vide.");
        PricingEngine::calculateOrderTotal([], 5, 1, null, '15h', 'mardi');
    }

    public function test_should_throw_error_when_item_quantity_is_zero(): void
    {
        $items = [['name' => 'Pizza', 'price' => 12.50, 'quantity' => 0]];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("La quantité d'un article doit être supérieure à 0.");
        PricingEngine::calculateOrderTotal($items, 5, 1, null, '15h', 'mardi');
    }

    public function test_should_throw_error_when_item_price_is_negative(): void
    {
        $items = [['name' => 'Pizza', 'price' => -2.00, 'quantity' => 1]];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Le prix d'un article ne peut pas être négatif.");
        PricingEngine::calculateOrderTotal($items, 5, 1, null, '15h', 'mardi');
    }

    public function test_should_throw_error_when_ordering_at_closed_hours(): void
    {
        // 23h = Fermé
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le restaurant est fermé en dehors des heures d'ouverture (avant 10h, après 22h).");
        PricingEngine::calculateOrderTotal($this->defaultItems, 5, 1, null, '23:00', 'mardi');
    }

    public function test_should_throw_error_when_distance_is_out_of_zone(): void
    {
        // 15 km = Hors zone
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("La livraison est refusée pour cette distance (hors zone).");
        PricingEngine::calculateOrderTotal($this->defaultItems, 15, 1, null, '15h', 'mardi');
    }
}
