<?php

namespace App\Services;

use InvalidArgumentException;

class PricingEngine
{
    /**
     * Calcule les frais de livraison selon la distance et le poids de la commande.
     * 
     * @param float|int $distance Distance en kilomètres
     * @param float|int $weight Poids en kilogrammes
     * @return float|null Retourne le montant en euros, ou null si la livraison est refusée
     * @throws InvalidArgumentException Si la distance ou le poids est négatif
     */
    public static function calculateDeliveryFee(float|int $distance, float|int $weight): ?float
    {
        if ($distance < 0 || $weight < 0) {
            throw new InvalidArgumentException("La distance et le poids ne peuvent pas être négatifs.");
        }

        if ($distance > 10) {
            return null; // Livraison refusée
        }

        $fee = 2.00; // Base pour toute livraison

        // Supplément distance au-delà de 3 km
        if ($distance > 3) {
            $fee += ($distance - 3) * 0.50;
        }

        // Supplément poids pour plus de 5 kg
        if ($weight > 5) {
            $fee += 1.50;
        }

        return $fee;
    }

    /**
     * Applique un code promo au sous-total.
     * 
     * @param float $subtotal
     * @param string|null $promoCode
     * @param array $promoCodes
     * @return float
     * @throws InvalidArgumentException
     */
    public static function applyPromoCode(float $subtotal, ?string $promoCode, array $promoCodes): float
    {
        if ($subtotal < 0) {
            throw new InvalidArgumentException("Le sous-total ne peut pas être négatif.");
        }

        if (empty($promoCode)) {
            return (float) $subtotal;
        }

        $promo = null;
        foreach ($promoCodes as $p) {
            if (isset($p['code']) && $p['code'] === $promoCode) {
                $promo = $p;
                break;
            }
        }

        if (!$promo) {
            throw new InvalidArgumentException("Code promo invalide ou inconnu.");
        }

        $today = date('Y-m-d');
        if (isset($promo['expiresAt']) && $promo['expiresAt'] < $today) {
            throw new InvalidArgumentException("Code promo expiré.");
        }

        if (isset($promo['minOrder']) && $subtotal < $promo['minOrder']) {
            throw new InvalidArgumentException("Le montant minimum de la commande n'est pas atteint.");
        }

        $discount = 0.0;
        if ($promo['type'] === 'percentage') {
            $discount = $subtotal * ($promo['value'] / 100);
        } elseif ($promo['type'] === 'fixed') {
            $discount = $promo['value'];
        }

        return max(0.0, $subtotal - $discount);
    }
}

