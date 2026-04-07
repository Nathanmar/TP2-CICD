<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PricingEngine;
use Illuminate\Support\Facades\Cache;
use Exception;
use InvalidArgumentException;

class OrderController extends Controller
{
    // Mock des codes promo en mémoire pour cet exercice
    private array $promoCodes = [
        ['code' => 'BIENVENUE20', 'type' => 'percentage', 'value' => 20, 'minOrder' => 15.00, 'expiresAt' => '2026-12-31'],
        ['code' => 'EXPIRE', 'type' => 'percentage', 'value' => 10, 'minOrder' => 0.0, 'expiresAt' => '2000-01-01']
    ];

    public function simulate(Request $request)
    {
        try {
            $result = PricingEngine::calculateOrderTotal(
                $request->input('items', []),
                $request->input('distance', 0),
                $request->input('weight', 0),
                $request->input('promoCode'),
                $request->input('hour', '12:00'),
                $request->input('dayOfWeek', 'lundi'),
                $this->promoCodes
            );

            return response()->json($result, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        try {
            $result = PricingEngine::calculateOrderTotal(
                $request->input('items', []),
                $request->input('distance', 0),
                $request->input('weight', 0),
                $request->input('promoCode'),
                $request->input('hour', '12:00'),
                $request->input('dayOfWeek', 'lundi'),
                $this->promoCodes
            );

            // Génération d'un ID unique et stockage en mémoire via le Cache
            $id = uniqid('order_');
            $order = array_merge(['id' => $id, 'items' => $request->input('items')], $result);

            Cache::put('orders.' . $id, $order);

            return response()->json($order, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $order = Cache::get('orders.' . $id);

        if (!$order) {
            return response()->json(['error' => 'Commande introuvable'], 404);
        }

        return response()->json($order, 200);
    }

    public function validatePromo(Request $request)
    {
        $code = $request->input('code');
        $subtotal = $request->input('subtotal');

        if (!$code) {
            return response()->json(['error' => 'Code manquant'], 400);
        }

        try {
            // application "virtuelle"
            $newSubtotal = PricingEngine::applyPromoCode((float) $subtotal, $code, $this->promoCodes);

            return response()->json([
                'valid' => true,
                'discount' => round($subtotal - $newSubtotal, 2),
                'newSubtotal' => round($newSubtotal, 2)
            ], 200);
        } catch (InvalidArgumentException $e) {
            // Différenciation de l'erreur 404 pour un code non trouvé
            if (str_contains(strtolower($e->getMessage()), 'inconnu') || str_contains(strtolower($e->getMessage()), 'invalide')) {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
