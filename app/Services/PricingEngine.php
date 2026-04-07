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
}
