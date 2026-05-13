<?php

class Produit
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getAll(): array
    {
        $stmt = $this->conn->query('SELECT * FROM produit ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllApproved(): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM produit WHERE is_approved = 1 ORDER BY id DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPending(): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM produit WHERE is_approved = 0 ORDER BY id DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM produit WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO produit (name, description, price, calories, image, added_by, is_approved)
             VALUES (:name, :description, :price, :calories, :image, :added_by, :is_approved)'
        );

        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'calories' => $data['calories'],
            'image' => $data['image'] ?? '',
            'added_by' => $data['added_by'],
            'is_approved' => (int) ($data['is_approved'] ?? 0),
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE produit
             SET name = :name,
                 description = :description,
                 price = :price,
                 calories = :calories,
                 added_by = :added_by
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'calories' => $data['calories'],
            'added_by' => $data['added_by'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM produit WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function approve(int $id): bool
    {
        $stmt = $this->conn->prepare('UPDATE produit SET is_approved = 1 WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
