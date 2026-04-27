<?php

namespace App\Model;

class Cart
{
    private const SESSION_KEY = 'shopping_cart';

    /**
     * Initialize cart session if not exists
     */
    public static function init(): void
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [
                'items' => [],
                'total' => 0,
            ];
        }
    }

    /**
     * Get all items in cart
     */
    public static function items(): array
    {
        self::init();
        return $_SESSION[self::SESSION_KEY]['items'];
    }

    /**
     * Get cart total
     */
    public static function total(): float
    {
        self::init();
        return $_SESSION[self::SESSION_KEY]['total'];
    }

    /**
     * Get item count
     */
    public static function count(): int
    {
        return count(self::items());
    }

    /**
     * Get full cart
     */
    public static function get(): array
    {
        self::init();
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Add product to cart or update quantity if exists
     */
    public static function addItem(int $productId, int $quantity, float $unitPrice): void
    {
        self::init();

        $found = false;
        
        // Check if product already in cart
        foreach ($_SESSION[self::SESSION_KEY]['items'] as &$item) {
            if ($item['product_id'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        // Add new item if not found
        if (!$found) {
            $_SESSION[self::SESSION_KEY]['items'][] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }

        self::recalculateTotal();
    }

    /**
     * Update item quantity in cart
     */
    public static function updateItem(int $productId, int $quantity): bool
    {
        self::init();

        foreach ($_SESSION[self::SESSION_KEY]['items'] as &$item) {
            if ($item['product_id'] == $productId) {
                if ($quantity <= 0) {
                    // Remove if quantity is 0 or negative
                    return self::removeItem($productId);
                }
                $item['quantity'] = $quantity;
                self::recalculateTotal();
                return true;
            }
        }

        return false;
    }

    /**
     * Remove item from cart
     */
    public static function removeItem(int $productId): bool
    {
        self::init();

        foreach ($_SESSION[self::SESSION_KEY]['items'] as $key => $item) {
            if ($item['product_id'] == $productId) {
                unset($_SESSION[self::SESSION_KEY]['items'][$key]);
                // Re-index array
                $_SESSION[self::SESSION_KEY]['items'] = array_values($_SESSION[self::SESSION_KEY]['items']);
                self::recalculateTotal();
                return true;
            }
        }

        return false;
    }

    /**
     * Clear entire cart
     */
    public static function clear(): void
    {
        $_SESSION[self::SESSION_KEY] = [
            'items' => [],
            'total' => 0,
        ];
    }

    /**
     * Check if cart is empty
     */
    public static function isEmpty(): bool
    {
        return empty(self::items());
    }

    /**
     * Get item details with product info
     */
    public static function itemsWithProducts(Product $productModel): array
    {
        $items = self::items();
        $enriched = [];

        foreach ($items as $item) {
            $product = $productModel->find($item['product_id']);
            if ($product) {
                $enriched[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'product_name' => $product['name'],
                    'product_image' => $product['image'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ];
            }
        }

        return $enriched;
    }

    /**
     * Convert cart to order format for checkout
     */
    public static function toOrderData(array $buyerData): array
    {
        self::init();

        return [
            'buyer_name' => $buyerData['buyer_name'] ?? '',
            'buyer_phone' => $buyerData['buyer_phone'] ?? '',
            'buyer_address' => $buyerData['buyer_address'] ?? '',
            'items' => $_SESSION[self::SESSION_KEY]['items'],
            'total_price' => $_SESSION[self::SESSION_KEY]['total'],
        ];
    }

    /**
     * Helper: Recalculate cart total
     */
    private static function recalculateTotal(): void
    {
        $total = 0;
        foreach ($_SESSION[self::SESSION_KEY]['items'] as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }
        $_SESSION[self::SESSION_KEY]['total'] = round($total, 2);
    }
}
