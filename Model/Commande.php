<?php

namespace App\Model;

use mysqli;

class Commande
{
    public function __construct(private mysqli $connection)
    {
    }

    /**
     * Get all orders with their items
     */
    public function all(): array
    {
        $sql = 'SELECT c.* FROM commande c ORDER BY c.id DESC';
        $result = $this->connection->query($sql);
        
        if (!$result) {
            return [];
        }

        $orders = $result->fetch_all(MYSQLI_ASSOC);
        return $this->enrichOrdersWithItems($orders);
    }

    /**
     * Get single order by ID with its items
     */
    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM commande WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $result = $statement->get_result();
        
        $order = $result ? ($result->fetch_assoc() ?: null) : null;
        
        if ($order) {
            $order['items'] = $this->itemsByOrderId($order['id']);
        }
        
        return $order;
    }

    /**
     * Get orders by customer phone with their items
     */
    public function findByPhone(string $phone): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM commande WHERE buyer_phone = ? ORDER BY id DESC'
        );
        $statement->bind_param('s', $phone);
        $statement->execute();
        $result = $statement->get_result();

        $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        return $this->enrichOrdersWithItems($orders);
    }

    /**
     * Create a new order (without items - items added via addItem method)
     * Supports both old format (product_id) and new format (items array)
     */
    public function create(array $data): int|false
    {
        // Extract items if provided (new cart format)
        $items = $data['items'] ?? [];
        
        // Calculate total from items if provided, otherwise use provided total_price
        $totalPrice = 0;
        if (!empty($items)) {
            foreach ($items as $item) {
                $totalPrice += $item['quantity'] * $item['unit_price'];
            }
        } else {
            $totalPrice = $data['total_price'] ?? 0;
        }

        $statement = $this->connection->prepare(
            'INSERT INTO commande (product_id, buyer_name, buyer_phone, buyer_address, quantity, total_price)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        // For cart orders with items, use NULL; for legacy single-product orders, use provided product_id
        $productId = !empty($items) ? null : ($data['product_id'] ?? null);
        $quantity = !empty($items) ? 0 : ($data['quantity'] ?? 0);  // 0 for multi-item, specific qty for single-product
        
        $statement->bind_param(
            'isssid',
            $productId,
            $data['buyer_name'],
            $data['buyer_phone'],
            $data['buyer_address'],
            $quantity,
            $totalPrice
        );

        if (!$statement->execute()) {
            return false;
        }

        $orderId = $this->connection->insert_id;

        // Add items if provided
        if (!empty($items)) {
            foreach ($items as $item) {
                $this->addItem($orderId, $item['product_id'], $item['quantity'], $item['unit_price']);
            }
        }

        return $orderId;
    }

    /**
     * Add an item to an order
     */
    public function addItem(int $orderId, int $productId, int $quantity, float $unitPrice): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO commande_item (commande_id, product_id, quantity, unit_price)
             VALUES (?, ?, ?, ?)'
        );
        
        $statement->bind_param('iiii', $orderId, $productId, $quantity, $unitPrice);
        
        if (!$statement->execute()) {
            return false;
        }

        // Update commande total_price
        $this->updateOrderTotalPrice($orderId);
        
        return true;
    }

    /**
     * Get all items for a specific order
     */
    public function itemsByOrderId(int $orderId): array
    {
        $statement = $this->connection->prepare(
            'SELECT ci.*, p.name AS product_name, p.image AS product_image
             FROM commande_item ci
             LEFT JOIN produit p ON p.id = ci.product_id
             WHERE ci.commande_id = ?
             ORDER BY ci.id ASC'
        );
        
        $statement->bind_param('i', $orderId);
        $statement->execute();
        $result = $statement->get_result();

        $items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        if ($items !== []) {
            return $items;
        }

        $legacyStatement = $this->connection->prepare(
            'SELECT c.product_id, c.quantity, c.total_price, p.name AS product_name, p.image AS product_image, p.price AS unit_price
             FROM commande c
             LEFT JOIN produit p ON p.id = c.product_id
             WHERE c.id = ? AND c.product_id IS NOT NULL'
        );
        $legacyStatement->bind_param('i', $orderId);
        $legacyStatement->execute();
        $legacyResult = $legacyStatement->get_result();
        $legacyOrder = $legacyResult ? $legacyResult->fetch_assoc() : null;

        if (!$legacyOrder) {
            return [];
        }

        return [[
            'id' => null,
            'commande_id' => $orderId,
            'product_id' => (int) $legacyOrder['product_id'],
            'quantity' => (int) $legacyOrder['quantity'],
            'unit_price' => (float) ($legacyOrder['unit_price'] ?? 0),
            'product_name' => $legacyOrder['product_name'],
            'product_image' => $legacyOrder['product_image'],
        ]];
    }

    /**
     * Update order (for legacy single-product orders)
     */
    public function update(int $id, array $data): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE commande
             SET product_id = ?, buyer_name = ?, buyer_phone = ?, buyer_address = ?, quantity = ?, total_price = ?
             WHERE id = ?'
        );

        $statement->bind_param(
            'isssidi',
            $data['product_id'],
            $data['buyer_name'],
            $data['buyer_phone'],
            $data['buyer_address'],
            $data['quantity'],
            $data['total_price'],
            $id
        );

        return $statement->execute();
    }

    /**
     * Delete an order (cascades to items due to FK)
     */
    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM commande WHERE id = ?');
        $statement->bind_param('i', $id);

        return $statement->execute();
    }

    /**
     * Delete a specific item from order
     */
    public function deleteItem(int $itemId): bool
    {
        $statement = $this->connection->prepare('DELETE FROM commande_item WHERE id = ?');
        $statement->bind_param('i', $itemId);
        
        if (!$statement->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Update item quantity
     */
    public function updateItemQuantity(int $itemId, int $quantity): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE commande_item SET quantity = ? WHERE id = ?'
        );
        
        $statement->bind_param('ii', $quantity, $itemId);
        
        if (!$statement->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Helper: Enrich orders with their items
     */
    private function enrichOrdersWithItems(array $orders): array
    {
        foreach ($orders as &$order) {
            $order['items'] = $this->itemsByOrderId($order['id']);
        }
        return $orders;
    }

    /**
     * Helper: Recalculate and update order total price from items
     */
    private function updateOrderTotalPrice(int $orderId): void
    {
        $statement = $this->connection->prepare(
            'SELECT SUM(quantity * unit_price) as total FROM commande_item WHERE commande_id = ?'
        );
        
        $statement->bind_param('i', $orderId);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();
        
        $total = $row['total'] ?? 0;
        
        $updateStmt = $this->connection->prepare(
            'UPDATE commande SET total_price = ? WHERE id = ?'
        );
        
        $updateStmt->bind_param('di', $total, $orderId);
        $updateStmt->execute();
    }
}
