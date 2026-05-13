<?php

class Aliment
{
    private $pdo;
    private $columnExistsCache = [];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll()
    {
        return $this->pdo->query("SELECT * FROM aliments ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll()
    {
        try {
            return (int) $this->pdo->query("SELECT COUNT(*) FROM aliments")->fetchColumn();
        } catch (PDOException $exception) {
            return 0;
        }
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM aliments WHERE id = ?");
        $stmt->execute([(int) $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchByTypeAndName($query, $type, $limit = 5)
    {
        $query = trim((string) $query);
        $type = trim((string) $type);
        $limit = max(1, (int) $limit);

        if ($query === '' || $type === '') {
            return [];
        }

        $stmt = $this->pdo->prepare(
            "SELECT id, nom, type, calories, unite, proteines, glucides, lipides" . ($this->columnExists('aliments', 'sucre_g') ? ", sucre_g" : "") . "
             FROM aliments
             WHERE nom LIKE :query
             AND type = :type
             ORDER BY nom ASC
             LIMIT {$limit}"
        );

        $stmt->execute([
            ':query' => '%' . $query . '%',
            ':type' => $type,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $columns = ['nom', 'calories', 'unite', 'type', 'proteines', 'glucides', 'lipides'];
        $params = [
            $data['nom'],
            (float) ($data['calories'] ?? 0),
            $data['unite'] ?? 'g',
            $data['type'] ?? 'proteine',
            (float) ($data['proteines'] ?? 0),
            (float) ($data['glucides'] ?? 0),
            (float) ($data['lipides'] ?? 0),
        ];

        if ($this->columnExists('aliments', 'sucre_g')) {
            $columns[] = 'sucre_g';
            $params[] = (float) ($data['sucre_g'] ?? 0);
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $stmt = $this->pdo->prepare("
            INSERT INTO aliments (" . implode(', ', $columns) . ")
            VALUES ($placeholders)
        ");

        return $stmt->execute($params);
    }

    public function update($data)
    {
        $assignments = [
            'nom = ?',
            'calories = ?',
            'unite = ?',
            'type = ?',
            'proteines = ?',
            'glucides = ?',
            'lipides = ?',
        ];
        $params = [
            $data['nom'],
            (float) $data['calories'],
            $data['unite'] ?? 'g',
            $data['type'],
            (float) ($data['proteines'] ?? 0),
            (float) ($data['glucides'] ?? 0),
            (float) ($data['lipides'] ?? 0),
        ];

        if ($this->columnExists('aliments', 'sucre_g')) {
            $assignments[] = 'sucre_g = ?';
            $params[] = (float) ($data['sucre_g'] ?? 0);
        }

        $params[] = (int) $data['id'];
        $stmt = $this->pdo->prepare("
            UPDATE aliments
            SET " . implode(', ', $assignments) . "
            WHERE id = ?
        ");

        return $stmt->execute($params);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM aliments WHERE id = ?");
        return $stmt->execute([(int) $id]);
    }

    private function columnExists($tableName, $columnName)
    {
        $cacheKey = $tableName . '.' . $columnName;

        if (array_key_exists($cacheKey, $this->columnExistsCache)) {
            return $this->columnExistsCache[$cacheKey];
        }

        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `$tableName` LIKE ?");
            $stmt->execute([$columnName]);
            $exists = (bool) $stmt->fetchColumn();
        } catch (Exception $exception) {
            $exists = false;
        }

        $this->columnExistsCache[$cacheKey] = $exists;
        return $exists;
    }
}
