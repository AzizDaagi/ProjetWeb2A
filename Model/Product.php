<?php

namespace App\Model;

use mysqli;

class Product
{
    public function __construct(private mysqli $connection)
    {
    }

    public function all(): array
    {
        $result = $this->connection->query('SELECT * FROM produit ORDER BY id DESC');

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function approved(): array
    {
        $result = $this->connection->query('SELECT * FROM produit WHERE is_approved = 1 ORDER BY id DESC');

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function pending(): array
    {
        $result = $this->connection->query('SELECT * FROM produit WHERE is_approved = 0 ORDER BY id DESC');

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM produit WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $result = $statement->get_result();

        return $result ? ($result->fetch_assoc() ?: null) : null;
    }

    public function create(array $data): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO produit (name, description, price, calories, image, added_by, is_approved)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $statement->bind_param(
            'ssdissi',
            $data['name'],
            $data['description'],
            $data['price'],
            $data['calories'],
            $data['image'],
            $data['added_by'],
            $data['is_approved']
        );

        return $statement->execute();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE produit
             SET name = ?, description = ?, price = ?, calories = ?, added_by = ?
             WHERE id = ?'
        );

        $statement->bind_param(
            'ssdisi',
            $data['name'],
            $data['description'],
            $data['price'],
            $data['calories'],
            $data['added_by'],
            $id
        );

        return $statement->execute();
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM produit WHERE id = ?');
        $statement->bind_param('i', $id);

        return $statement->execute();
    }

    public function approve(int $id): bool
    {
        $statement = $this->connection->prepare('UPDATE produit SET is_approved = 1 WHERE id = ?');
        $statement->bind_param('i', $id);

        return $statement->execute();
    }
}
