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

    /**
     * Retourne le multiplicateur de prix selon l'heure et le jour (Surge pricing).
     * 
     * @param string $hour L'heure au format "15h" ou "15:00"
     * @param string $dayOfWeek Le jour en français ("lundi", "mardi", etc.) ou anglais
     * @return float Le multiplicateur
     * @throws InvalidArgumentException
     */
    public static function calculateSurge(string $hour, string $dayOfWeek): float
    {
        $parts = explode(':', str_replace('h', ':', strtolower($hour)));
        $h = (int) $parts[0];
        $m = isset($parts[1]) ? (int) $parts[1] : 0;
        $t = $h + ($m / 60);

        $day = strtolower($dayOfWeek);

        // En dehors des heures d'ouverture (avant 10h, à partir de 22h)
        if ($t < 10.0 || $t >= 22.0) {
            return 0.0;
        }

        // Dimanche toute la journée
        if (in_array($day, ['dimanche', 'sunday'])) {
            return 1.2;
        }

        // Vendredi et Samedi
        if (in_array($day, ['vendredi', 'friday', 'samedi', 'saturday'])) {
            if ($t >= 19.0) {
                // Vendredi-Samedi soir, 19h-22h
                return 1.8;
            }
            return 1.0;
        }

        // Lundi à Jeudi
        if (in_array($day, ['lundi', 'monday', 'mardi', 'tuesday', 'mercredi', 'wednesday', 'jeudi', 'thursday'])) {
            if ($t >= 12.0 && $t < 13.5) { // 12h-13h30
                return 1.3;
            }
            if ($t >= 19.0 && $t < 21.0) { // 19h-21h
                return 1.5;
            }
            return 1.0; // Le reste du temps
        }

        throw new InvalidArgumentException("Jour de la semaine non reconnu: $dayOfWeek");
    }
}


