<?php

class Cart
{
    private const SESSION_KEY = 'shopping_cart';

    public static function init(): void
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [
                'items' => [],
                'total' => 0.0,
            ];
        }
    }

    public static function items(): array
    {
        self::init();
        return $_SESSION[self::SESSION_KEY]['items'];
    }

    public static function total(): float
    {
        self::init();
        return (float) $_SESSION[self::SESSION_KEY]['total'];
    }

    public static function count(): int
    {
        return array_sum(array_map(static fn(array $item): int => (int) ($item['quantity'] ?? 0), self::items()));
    }

    public static function addItem(int $productId, int $quantity, float $unitPrice): void
    {
        self::init();

        foreach ($_SESSION[self::SESSION_KEY]['items'] as &$item) {
            if ((int) $item['product_id'] === $productId) {
                $item['quantity'] = (int) $item['quantity'] + $quantity;
                self::recalculateTotal();
                return;
            }
        }

        $_SESSION[self::SESSION_KEY]['items'][] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ];

        self::recalculateTotal();
    }

    public static function updateItem(int $productId, int $quantity): bool
    {
        self::init();

        foreach ($_SESSION[self::SESSION_KEY]['items'] as &$item) {
            if ((int) $item['product_id'] !== $productId) {
                continue;
            }

            if ($quantity <= 0) {
                return self::removeItem($productId);
            }

            $item['quantity'] = $quantity;
            self::recalculateTotal();
            return true;
        }

        return false;
    }

    public static function removeItem(int $productId): bool
    {
        self::init();

        foreach ($_SESSION[self::SESSION_KEY]['items'] as $index => $item) {
            if ((int) $item['product_id'] !== $productId) {
                continue;
            }

            unset($_SESSION[self::SESSION_KEY]['items'][$index]);
            $_SESSION[self::SESSION_KEY]['items'] = array_values($_SESSION[self::SESSION_KEY]['items']);
            self::recalculateTotal();
            return true;
        }

        return false;
    }

    public static function clear(): void
    {
        $_SESSION[self::SESSION_KEY] = [
            'items' => [],
            'total' => 0.0,
        ];
    }

    public static function isEmpty(): bool
    {
        return self::items() === [];
    }

    public static function itemsWithProducts(Produit $productModel): array
    {
        $items = [];

        foreach (self::items() as $item) {
            $product = $productModel->getById((int) $item['product_id']);
            if (!$product) {
                continue;
            }

            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $items[] = [
                'product_id' => (int) $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'product_name' => $product['name'],
                'product_image' => $product['image'],
                'subtotal' => $quantity * $unitPrice,
            ];
        }

        usort($items, static fn(array $left, array $right): int => strcmp((string) $left['product_name'], (string) $right['product_name']));

        return $items;
    }

    public static function toOrderData(array $buyerData): array
    {
        self::init();

        return [
            'buyer_name' => $buyerData['buyer_name'] ?? '',
            'buyer_phone' => $buyerData['buyer_phone'] ?? '',
            'buyer_address' => $buyerData['buyer_address'] ?? '',
            'buyer_email' => $buyerData['buyer_email'] ?? '',
            'items' => $_SESSION[self::SESSION_KEY]['items'],
            'total_price' => $_SESSION[self::SESSION_KEY]['total'],
        ];
    }

    private static function recalculateTotal(): void
    {
        $total = 0.0;
        foreach ($_SESSION[self::SESSION_KEY]['items'] as $item) {
            $total += (int) $item['quantity'] * (float) $item['unit_price'];
        }

        $_SESSION[self::SESSION_KEY]['total'] = round($total, 2);
    }
}
