<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PricingEngine;

class SurgePricingTest extends TestCase
{
    // ============================================
    // CAS NORMAUX ET MULTIPLICATEURS
    // ============================================

    public function test_should_return_normal_multiplier_on_tuesday_afternoon(): void
    {
        // Mardi 15h → 1.0 (normal)
        $result = PricingEngine::calculateSurge('15h', 'mardi');
        $this->assertEquals(1.0, $result);
    }

    public function test_should_return_lunch_multiplier_on_wednesday(): void
    {
        // Mercredi 12h30 → 1.3 (dejeuner)
        $result = PricingEngine::calculateSurge('12:30', 'mercredi');
        $this->assertEquals(1.3, $result);
    }

    public function test_should_return_dinner_multiplier_on_thursday(): void
    {
        // Jeudi 20h → 1.5 (diner)
        $result = PricingEngine::calculateSurge('20h00', 'jeudi');
        $this->assertEquals(1.5, $result);
    }

    public function test_should_return_weekend_evening_multiplier_on_friday(): void
    {
        // Vendredi 21h → 1.8 (weekend soir)
        $result = PricingEngine::calculateSurge('21h', 'vendredi');
        $this->assertEquals(1.8, $result);
    }

    public function test_should_return_sunday_multiplier_on_sunday(): void
    {
        // Dimanche 14h → 1.2 (dimanche)
        $result = PricingEngine::calculateSurge('14:00', 'dimanche');
        $this->assertEquals(1.2, $result);
    }

    // ============================================
    // TRANSITIONS ET LIMITES
    // ============================================

    public function test_should_return_normal_multiplier_at_exactly_11_30(): void
    {
        // 11h30 pile → encore normal ou deja dejeuner ? Normal (le dej commence à 12h)
        $result = PricingEngine::calculateSurge('11:30', 'lundi');
        $this->assertEquals(1.0, $result);
    }

    public function test_should_return_dinner_multiplier_at_exactly_19_00(): void
    {
        // 19h00 pile → quel creneau ? Dîner (1.5)
        $result = PricingEngine::calculateSurge('19:00', 'lundi');
        $this->assertEquals(1.5, $result);
    }

    public function test_should_return_weekend_evening_multiplier_at_exactly_19_00_on_saturday(): void
    {
        // 19h00 pile le Samedi → Dîner weekend soir (1.8)
        $result = PricingEngine::calculateSurge('19:00', 'samedi');
        $this->assertEquals(1.8, $result);
    }

    public function test_should_return_closed_multiplier_at_exactly_22_00(): void
    {
        // 22h00 pile → encore ouvert ou ferme ? Fermé (0)
        $result = PricingEngine::calculateSurge('22:00', 'mardi');
        $this->assertEquals(0.0, $result);
    }

    public function test_should_return_closed_multiplier_at_9_59(): void
    {
        // 9h59 → ferme (0)
        $result = PricingEngine::calculateSurge('09:59', 'mardi');
        $this->assertEquals(0.0, $result);
    }

    public function test_should_return_normal_multiplier_at_10_00(): void
    {
        // 10h00 → ouvert (1.0)
        $result = PricingEngine::calculateSurge('10:00', 'mardi');
        $this->assertEquals(1.0, $result);
    }
}
