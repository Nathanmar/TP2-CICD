<?php

namespace App\Utils;

class Utils
{
    /**
     * Met la première lettre en majuscule, le reste en minuscule.
     */
    public static function capitalize(?string $str): string
    {
        if (empty($str)) {
            return "";
        }
        return ucfirst(strtolower($str));
    }

    /**
     * Calcule la moyenne d'un tableau de nombres, arrondie à 2 décimales.
     */
    public static function calculateAverage(?array $numbers): float
    {
        if (empty($numbers)) {
            return 0.0;
        }

        $average = array_sum($numbers) / count($numbers);
        return (float) round($average, 2);
    }

    /**
     * Transforme un texte en slug URL : minuscules, espaces remplacés par des tirets, caractères spéciaux supprimés.
     */
    public static function slugify(?string $text): string
    {
        if ($text === null || $text === "") {
            return "";
        }

        // Supprimer les apostrophes sans les remplacer par un tiret
        $text = str_replace("'", "", $text);
        
        // Remplacer tous les caractères non alphanumériques par des tirets
        $text = preg_replace('/[^a-zA-Z0-9]+/', '-', $text);
        
        // Supprimer les tirets multiples et les tirets de début/fin
        $text = trim(preg_replace('/-+/', '-', $text), '-');
        
        return strtolower($text);
    }

    /**
     * Limite une valeur entre un minimum et un maximum.
     */
    public static function clamp(int|float $value, int|float $min, int|float $max): int|float
    {
        return max($min, min($value, $max));
    }
}
