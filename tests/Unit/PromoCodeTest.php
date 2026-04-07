<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PricingEngine;
use InvalidArgumentException;

class PromoCodeTest extends TestCase
{
    private array $promoCodes = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $this->promoCodes = [
            ['code' => 'PERCENT20', 'type' => 'percentage', 'value' => 20, 'minOrder' => 10.0, 'expiresAt' => $tomorrow],
            ['code' => 'FIXED5', 'type' => 'fixed', 'value' => 5, 'minOrder' => 15.0, 'expiresAt' => $tomorrow],
            ['code' => 'EXPIRED', 'type' => 'fixed', 'value' => 5, 'minOrder' => 10.0, 'expiresAt' => $yesterday],
            ['code' => 'MINORDER50', 'type' => 'fixed', 'value' => 10, 'minOrder' => 50.0, 'expiresAt' => $tomorrow],
            ['code' => 'NEGATIVE_TOTAL', 'type' => 'fixed', 'value' => 10, 'minOrder' => 5.0, 'expiresAt' => $tomorrow],
            ['code' => '100PERCENT', 'type' => 'percentage', 'value' => 100, 'minOrder' => 0.0, 'expiresAt' => $tomorrow],
            ['code' => 'TODAY', 'type' => 'fixed', 'value' => 5, 'minOrder' => 10.0, 'expiresAt' => $today],
        ];
    }

    public function test_should_apply_percentage_discount(): void
    {
        $result = PricingEngine::applyPromoCode(50.0, 'PERCENT20', $this->promoCodes);
        $this->assertEquals(40.0, $result);
    }

    public function test_should_apply_fixed_discount(): void
    {
        $result = PricingEngine::applyPromoCode(30.0, 'FIXED5', $this->promoCodes);
        $this->assertEquals(25.0, $result);
    }

    public function test_should_throw_error_if_promo_expired(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Code promo expiré.");
        PricingEngine::applyPromoCode(30.0, 'EXPIRED', $this->promoCodes);
    }

    public function test_should_throw_error_if_min_order_not_met(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Le montant minimum de la commande n'est pas atteint.");
        PricingEngine::applyPromoCode(30.0, 'MINORDER50', $this->promoCodes);
    }

    public function test_should_throw_error_if_promo_unknown(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Code promo invalide ou inconnu.");
        PricingEngine::applyPromoCode(30.0, 'UNKNOWN', $this->promoCodes);
    }

    public function test_should_not_drop_total_below_zero_with_fixed_discount(): void
    {
        $result = PricingEngine::applyPromoCode(5.0, 'NEGATIVE_TOTAL', $this->promoCodes);
        $this->assertEquals(0.0, $result);
    }

    public function test_should_return_zero_for_100_percent_discount(): void
    {
        $result = PricingEngine::applyPromoCode(50.0, '100PERCENT', $this->promoCodes);
        $this->assertEquals(0.0, $result);
    }

    public function test_should_return_zero_when_applying_promo_on_empty_subtotal(): void
    {
        $result = PricingEngine::applyPromoCode(0.0, '100PERCENT', $this->promoCodes);
        $this->assertEquals(0.0, $result);
    }

    public function test_should_accept_promo_expiring_today(): void
    {
        $result = PricingEngine::applyPromoCode(30.0, 'TODAY', $this->promoCodes);
        $this->assertEquals(25.0, $result);
    }

    public function test_should_return_subtotal_if_promo_code_is_empty_or_null(): void
    {
        $this->assertEquals(50.0, PricingEngine::applyPromoCode(50.0, null, $this->promoCodes));
        $this->assertEquals(50.0, PricingEngine::applyPromoCode(50.0, "", $this->promoCodes));
    }

    public function test_should_throw_error_if_subtotal_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Le sous-total ne peut pas être négatif.");
        PricingEngine::applyPromoCode(-10.0, null, $this->promoCodes);
    }
}
