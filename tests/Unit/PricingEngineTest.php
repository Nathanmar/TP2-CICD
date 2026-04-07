<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PricingEngine;
use InvalidArgumentException;

class PricingEngineTest extends TestCase
{
    // ============================================
    // CAS NORMAUX
    // ============================================

    public function test_should_return_base_fee_when_distance_is_under_3km_and_weight_under_5kg(): void
    {
        // 2 km, 1 kg → que renvoie-t-il ? 2.00€
        $result = PricingEngine::calculateDeliveryFee(2, 1);
        $this->assertEquals(2.00, $result);
    }

    public function test_should_add_distance_surcharge_when_distance_is_between_3_and_10km(): void
    {
        // 7 km, 3 kg → quel prix ? Base (2.00) + 4 km de supplément (4 * 0.50 = 2.00) = 4.00€
        $result = PricingEngine::calculateDeliveryFee(7, 3);
        $this->assertEquals(4.00, $result);
    }

    public function test_should_add_weight_surcharge_when_weight_is_above_5kg(): void
    {
        // 5 km, 8 kg (lourd) → supplement ? Base (2.00) + 2 km (2 * 0.50 = 1.00) + poids (1.50) = 4.50€
        $result = PricingEngine::calculateDeliveryFee(5, 8);
        $this->assertEquals(4.50, $result);
    }

    public function test_should_calculate_complex_case_correctly(): void
    {
        // 10 km, 6 kg → 2.00 + (7 * 0.50) + 1.50 = 7.00 ? Oui.
        $result = PricingEngine::calculateDeliveryFee(10, 6);
        $this->assertEquals(7.00, $result);
    }

    // ============================================
    // CAS LIMITES
    // ============================================

    public function test_should_not_add_distance_surcharge_when_exactly_3km(): void
    {
        // Exactement 3 km → base ou supplement ? Base uniquement (pas de supplément).
        $result = PricingEngine::calculateDeliveryFee(3, 2);
        $this->assertEquals(2.00, $result);
    }

    public function test_should_accept_delivery_when_exactly_10km(): void
    {
        // Exactement 10 km → accepté ou refusé ? Accepté, limite max.
        $result = PricingEngine::calculateDeliveryFee(10, 2);
        // Base (2.00) + 7 km supplémentaires (7 * 0.50 = 3.50) = 5.50
        $this->assertEquals(5.50, $result);
    }

    public function test_should_not_add_weight_surcharge_when_exactly_5kg(): void
    {
        // Exactement 5 kg → supplement ou pas ? Pas de supplément.
        $result = PricingEngine::calculateDeliveryFee(2, 5);
        $this->assertEquals(2.00, $result);
    }

    // ============================================
    // CAS D'ERREURS
    // ============================================

    public function test_should_refuse_delivery_when_distance_is_above_10km(): void
    {
        // 15 km → refusé (retourne null ici, comme implémenté)
        $result = PricingEngine::calculateDeliveryFee(15, 2);
        $this->assertNull($result);
    }

    public function test_should_throw_error_when_distance_is_negative(): void
    {
        // Distance négative → erreur
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La distance et le poids ne peuvent pas être négatifs.');
        PricingEngine::calculateDeliveryFee(-1, 2);
    }

    public function test_should_throw_error_when_weight_is_negative(): void
    {
        // Poids négatif → erreur
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La distance et le poids ne peuvent pas être négatifs.');
        PricingEngine::calculateDeliveryFee(2, -1);
    }

    public function test_should_allow_delivery_when_distance_is_zero(): void
    {
        // Distance = 0 → c'est valide ou pas ? Oui, le prix de base s'applique (ex: voisin direct).
        $result = PricingEngine::calculateDeliveryFee(0, 1);
        $this->assertEquals(2.00, $result);
    }
}

