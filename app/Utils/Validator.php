<?php

namespace App\Utils;

class Validator
{
    /**
     * Retourne true si l'email est valide (contient @ et un domaine avec un point).
     */
    public static function isValidEmail(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Retourne un objet (tableau associatif) { valid: boolean, errors: string[] }.
     * Règles : > 8 chars, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.
     */
    public static function isValidPassword(?string $password): array
    {
        $errors = [];

        if ($password === null || strlen($password) < 8) {
            $errors[] = 'Minimum 8 caracteres';
        }
        if ($password === null || !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Au moins 1 majuscule';
        }
        if ($password === null || !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Au moins 1 minuscule';
        }
        if ($password === null || !preg_match('/\d/', $password)) {
            $errors[] = 'Au moins 1 chiffre';
        }
        if ($password === null || !preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = 'Au moins 1 caractere special';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Retourne true si l'age est un entier entre 0 et 150.
     */
    public static function isValidAge(mixed $age): bool
    {
        if (!is_int($age)) {
            return false;
        }

        return $age >= 0 && $age <= 150;
    }
}
