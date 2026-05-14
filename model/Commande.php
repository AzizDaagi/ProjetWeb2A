<?php

class Commande
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function allSorted(string $sortBy = 'id', string $sortOrder = 'DESC', string $search = ''): array
    {
        $allowedSorts = ['id', 'buyer_name', 'buyer_phone', 'buyer_email', 'buyer_address', 'quantity', 'total_price', 'product_id', 'created_at'];
        $sortBy = in_array($sortBy, $allowedSorts, true) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $sql = 'SELECT * FROM commande';
        $params = [];

        if ($search !== '') {
            $sql .= ' WHERE buyer_name LIKE :search OR buyer_phone LIKE :search OR buyer_email LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY {$sortBy} {$sortOrder}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $this->enrichOrdersWithItems($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function find(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM commande WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        $order['items'] = $this->itemsByOrderId((int) $order['id']);
        return $order;
    }

    public function create(array $data): int|false
    {
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        $totalPrice = (float) ($data['total_price'] ?? 0);

        if ($items !== []) {
            $totalPrice = 0.0;
            foreach ($items as $item) {
                $totalPrice += (int) $item['quantity'] * (float) $item['unit_price'];
            }
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO commande (product_id, buyer_name, buyer_phone, buyer_address, buyer_email, quantity, total_price)
             VALUES (:product_id, :buyer_name, :buyer_phone, :buyer_address, :buyer_email, :quantity, :total_price)'
        );

        $stmt->execute([
            'product_id' => $items !== [] ? null : ($data['product_id'] ?? null),
            'buyer_name' => $data['buyer_name'],
            'buyer_phone' => $data['buyer_phone'],
            'buyer_address' => $data['buyer_address'],
            'buyer_email' => $data['buyer_email'] ?? null,
            'quantity' => $items !== [] ? 0 : (int) ($data['quantity'] ?? 0),
            'total_price' => $totalPrice,
        ]);

        $orderId = (int) $this->conn->lastInsertId();
        foreach ($items as $item) {
            $this->addItem($orderId, (int) $item['product_id'], (int) $item['quantity'], (float) $item['unit_price']);
        }

        return $orderId;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE commande
             SET product_id = :product_id,
                 buyer_name = :buyer_name,
                 buyer_phone = :buyer_phone,
                 buyer_address = :buyer_address,
                 buyer_email = :buyer_email,
                 quantity = :quantity,
                 total_price = :total_price
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'product_id' => $data['product_id'],
            'buyer_name' => $data['buyer_name'],
            'buyer_phone' => $data['buyer_phone'],
            'buyer_address' => $data['buyer_address'],
            'buyer_email' => $data['buyer_email'] ?? null,
            'quantity' => $data['quantity'],
            'total_price' => $data['total_price'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM commande WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function itemsByOrderId(int $orderId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT ci.*, p.name AS product_name, p.image AS product_image
             FROM commande_item ci
             LEFT JOIN produit p ON p.id = ci.product_id
             WHERE ci.commande_id = :order_id
             ORDER BY ci.id ASC'
        );
        $stmt->execute(['order_id' => $orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($items !== []) {
            return $items;
        }

        $legacy = $this->conn->prepare(
            'SELECT c.product_id, c.quantity, c.total_price, p.name AS product_name, p.image AS product_image, p.price AS unit_price
             FROM commande c
             LEFT JOIN produit p ON p.id = c.product_id
             WHERE c.id = :order_id AND c.product_id IS NOT NULL'
        );
        $legacy->execute(['order_id' => $orderId]);
        $legacyOrder = $legacy->fetch(PDO::FETCH_ASSOC);

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

    private function addItem(int $orderId, int $productId, int $quantity, float $unitPrice): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO commande_item (commande_id, product_id, quantity, unit_price)
             VALUES (:commande_id, :product_id, :quantity, :unit_price)'
        );

        return $stmt->execute([
            'commande_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);
    }

    private function enrichOrdersWithItems(array $orders): array
    {
        foreach ($orders as &$order) {
            $order['items'] = $this->itemsByOrderId((int) $order['id']);
        }

        return $orders;
    }
}
