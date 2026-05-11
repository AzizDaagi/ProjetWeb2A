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

    public function getForRecipeSelection()
    {
        $columns = ['id', 'nom', 'calories', 'type', 'proteines', 'glucides', 'lipides', 'unite'];

        if ($this->columnExists('aliments', 'sucre_g')) {
            $columns[] = 'sucre_g';
        }
        if ($this->columnExists('aliments', 'fibres')) {
            $columns[] = 'fibres';
        }
        if ($this->columnExists('aliments', 'image_url')) {
            $columns[] = 'image_url';
        }

        $stmt = $this->pdo->query(
            "SELECT " . implode(', ', $columns) . " FROM aliments ORDER BY nom ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function findById($id)
    {
        return $this->getById($id);
    }

    public function findManyByIds(array $ids)
    {
        $ids = array_values(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        }));

        if (empty($ids)) {
            return [];
        }

        $columns = ['id', 'nom', 'calories', 'proteines', 'glucides', 'lipides', 'type', 'unite'];

        if ($this->columnExists('aliments', 'sucre_g')) {
            $columns[] = 'sucre_g';
        }
        if ($this->columnExists('aliments', 'fibres')) {
            $columns[] = 'fibres';
        }
        if ($this->columnExists('aliments', 'image_url')) {
            $columns[] = 'image_url';
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT " . implode(', ', $columns) . "
             FROM aliments
             WHERE id IN ({$placeholders})"
        );
        $stmt->execute($ids);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($query, $limit = 20)
    {
        $query = trim((string) $query);
        $limit = max(1, (int) $limit);

        if ($query === '') {
            return [];
        }

        $columns = ['id', 'nom', 'calories', 'type', 'proteines', 'glucides', 'lipides', 'unite'];

        if ($this->columnExists('aliments', 'sucre_g')) {
            $columns[] = 'sucre_g';
        }
        if ($this->columnExists('aliments', 'fibres')) {
            $columns[] = 'fibres';
        }
        if ($this->columnExists('aliments', 'image_url')) {
            $columns[] = 'image_url';
        }

        $stmt = $this->pdo->prepare(
            "SELECT " . implode(', ', $columns) . "
             FROM aliments
             WHERE nom LIKE :query
             ORDER BY nom ASC
             LIMIT {$limit}"
        );
        $stmt->execute([':query' => '%' . $query . '%']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        if ($this->columnExists('aliments', 'fibres')) {
            $columns[] = 'fibres';
            $params[] = (float) ($data['fibres'] ?? 0);
        }

        if ($this->columnExists('aliments', 'image_url')) {
            $columns[] = 'image_url';
            $params[] = $this->normalizeImageUrl($data['image_url'] ?? null);
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

        if ($this->columnExists('aliments', 'fibres')) {
            $assignments[] = 'fibres = ?';
            $params[] = (float) ($data['fibres'] ?? 0);
        }

        if ($this->columnExists('aliments', 'image_url')) {
            $assignments[] = 'image_url = ?';
            $params[] = $this->normalizeImageUrl($data['image_url'] ?? null);
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

    public function updateImageAndFibres($id, $fibres = 0, $imageUrl = null)
    {
        $updates = [];
        $params = [];

        if ($this->columnExists('aliments', 'fibres')) {
            $updates[] = 'fibres = ?';
            $params[] = (float) $fibres;
        }

        if ($this->columnExists('aliments', 'image_url')) {
            $updates[] = 'image_url = ?';
            $params[] = $this->normalizeImageUrl($imageUrl);
        }

        if (empty($updates)) {
            return true;
        }

        $params[] = (int) $id;
        $stmt = $this->pdo->prepare("
            UPDATE aliments
            SET " . implode(', ', $updates) . "
            WHERE id = ?
        ");

        return $stmt->execute($params);
    }

    public function hasColumn($columnName)
    {
        return $this->columnExists('aliments', $columnName);
    }

    private function normalizeImageUrl($imageUrl)
    {
        $imageUrl = trim((string) $imageUrl);

        return $imageUrl !== '' ? $imageUrl : null;
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
