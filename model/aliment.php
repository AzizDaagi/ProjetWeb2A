<?php

require_once __DIR__ . '/Database.php';

class Aliment
{
    private $db;
    private $id;
    private $nom;
    private $calories;
    private $proteines;
    private $glucides;
    private $lipides;
    private $fibres;
    private $type;
    private $image_url;

    public function __construct($id = null, $nom = null, $calories = null, $proteines = 0.0, $glucides = 0.0, $lipides = 0.0, $fibres = 0.0, $type = null, $image_url = null)
    {
        if ($id instanceof PDO) {
            $this->db = $id;
            $this->id = null;
            $this->nom = $nom;
            $this->calories = $calories;
            $this->proteines = $proteines;
            $this->glucides = $glucides;
            $this->lipides = $lipides;
            $this->fibres = $fibres;
            $this->type = $type;
            $this->image_url = $image_url;
            return;
        }

        $this->db = Database::getConnection();
        $this->id = $id;
        $this->nom = $nom;
        $this->calories = $calories;
        $this->proteines = $proteines;
        $this->glucides = $glucides;
        $this->lipides = $lipides;
        $this->fibres = $fibres;
        $this->type = $type;
        $this->image_url = $image_url;
    }

    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getCalories() { return $this->calories; }
    public function getProteines() { return $this->proteines; }
    public function getGlucides() { return $this->glucides; }
    public function getLipides() { return $this->lipides; }
    public function getFibres() { return $this->fibres; }
    public function getType() { return $this->type; }
    public function getImageUrl() { return $this->image_url; }

    public function setNom($nom) { $this->nom = $nom; }
    public function setCalories($calories) { $this->calories = $calories; }
    public function setProteines($proteines) { $this->proteines = $proteines; }
    public function setGlucides($glucides) { $this->glucides = $glucides; }
    public function setLipides($lipides) { $this->lipides = $lipides; }
    public function setFibres($fibres) { $this->fibres = $fibres; }
    public function setType($type) { $this->type = $type; }
    public function setImageUrl($image_url) { $this->image_url = $image_url; }

    private function getDb(): PDO
    {
        if (!$this->db instanceof PDO) {
            $this->db = Database::getConnection();
        }

        return $this->db;
    }

    public function getAll()
    {
        $stmt = $this->getDb()->query("
            SELECT
                id,
                nom,
                calories,
                COALESCE(type, '') AS type,
                COALESCE(proteines, 0) AS proteines,
                COALESCE(glucides, 0) AS glucides,
                COALESCE(lipides, 0) AS lipides,
                COALESCE(unite, 'g') AS unite,
                COALESCE(sucre_g, 0) AS sucre_g,
                COALESCE(fibres, 0) AS fibres,
                image_url
            FROM aliments
            ORDER BY nom ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAll()
    {
        return $this->getAll();
    }

    public function getAllAliments()
    {
        return $this->getAll();
    }

    public function getById($id)
    {
        $stmt = $this->getDb()->prepare("
            SELECT
                id,
                nom,
                calories,
                COALESCE(type, '') AS type,
                COALESCE(proteines, 0) AS proteines,
                COALESCE(glucides, 0) AS glucides,
                COALESCE(lipides, 0) AS lipides,
                COALESCE(unite, 'g') AS unite,
                COALESCE(sucre_g, 0) AS sucre_g,
                COALESCE(fibres, 0) AS fibres,
                image_url
            FROM aliments
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => (int) $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function countAll(): int
    {
        return (int) $this->getDb()->query('SELECT COUNT(*) FROM aliments')->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->getDb()->prepare("
            INSERT INTO aliments (
                nom, calories, type, proteines, glucides, lipides, unite, sucre_g, fibres, image_url
            ) VALUES (
                :nom, :calories, :type, :proteines, :glucides, :lipides, :unite, :sucre_g, :fibres, :image_url
            )
        ");

        return $stmt->execute([
            'nom' => $data['nom'] ?? '',
            'calories' => $data['calories'] ?? 0,
            'type' => $data['type'] ?? null,
            'proteines' => $data['proteines'] ?? 0,
            'glucides' => $data['glucides'] ?? 0,
            'lipides' => $data['lipides'] ?? 0,
            'unite' => $data['unite'] ?? 'g',
            'sucre_g' => $data['sucre_g'] ?? 0,
            'fibres' => $data['fibres'] ?? 0,
            'image_url' => $data['image_url'] ?? null,
        ]);
    }

    public function update($idOrData, ?array $data = null): bool
    {
        if (is_array($idOrData) && $data === null) {
            $data = $idOrData;
            $id = (int) ($data['id'] ?? 0);
        } else {
            $id = (int) $idOrData;
        }

        if ($id <= 0 || !is_array($data)) {
            return false;
        }

        $stmt = $this->getDb()->prepare("
            UPDATE aliments
            SET
                nom = :nom,
                calories = :calories,
                type = :type,
                proteines = :proteines,
                glucides = :glucides,
                lipides = :lipides,
                unite = :unite,
                sucre_g = :sucre_g,
                fibres = :fibres,
                image_url = :image_url
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'nom' => $data['nom'] ?? '',
            'calories' => $data['calories'] ?? 0,
            'type' => $data['type'] ?? null,
            'proteines' => $data['proteines'] ?? 0,
            'glucides' => $data['glucides'] ?? 0,
            'lipides' => $data['lipides'] ?? 0,
            'unite' => $data['unite'] ?? 'g',
            'sucre_g' => $data['sucre_g'] ?? 0,
            'fibres' => $data['fibres'] ?? 0,
            'image_url' => $data['image_url'] ?? null,
        ]);
    }

    public function delete($id): bool
    {
        $stmt = $this->getDb()->prepare('DELETE FROM aliments WHERE id = :id');
        return $stmt->execute(['id' => (int) $id]);
    }
}
