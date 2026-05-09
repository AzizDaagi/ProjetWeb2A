<?php

class Utilisateur
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function countAll()
    {
        try {
            return (int) $this->pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
        } catch (PDOException $exception) {
            return 0;
        }
    }

    public function getAll()
    {
        return $this->fetchUsers();
    }

    public function getRecent($limit = 5)
    {
        return $this->fetchUsers(max(1, (int) $limit));
    }

    private function fetchUsers($limit = null)
    {
        try {
            $sql = "SELECT * FROM utilisateur ORDER BY id DESC";

            if ($limit !== null) {
                $sql .= " LIMIT " . (int) $limit;
            }

            $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'normalizeUserRow'], $rows);
        } catch (PDOException $exception) {
            return [];
        }
    }

    private function normalizeUserRow(array $row)
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'nom' => trim((string) ($row['nom'] ?? '')),
            'prenom' => trim((string) ($row['prenom'] ?? '')),
            'email' => trim((string) ($row['email'] ?? '')),
            'age' => isset($row['age']) ? (int) $row['age'] : null,
            'poids' => isset($row['poids']) ? (float) $row['poids'] : null,
            'taille' => isset($row['taille']) ? (float) $row['taille'] : null,
            'objectif_calories' => isset($row['objectif_calories']) ? (float) $row['objectif_calories'] : null,
        ];
    }
}
