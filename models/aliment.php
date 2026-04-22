<?php

class Aliment
{
    private $pdo;

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

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO aliments (nom, calories, unite, type, proteines, glucides, lipides)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['nom'],
            (float) ($data['calories'] ?? 0),
            $data['unite'] ?? 'g',
            $data['type'] ?? 'proteine',
            (float) ($data['proteines'] ?? 0),
            (float) ($data['glucides'] ?? 0),
            (float) ($data['lipides'] ?? 0)
        ]);
    }

    public function update($data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE aliments
            SET nom = ?, calories = ?, unite = ?, type = ?, proteines = ?, glucides = ?, lipides = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nom'],
            (float) $data['calories'],
            $data['unite'] ?? 'g',
            $data['type'],
            (float) ($data['proteines'] ?? 0),
            (float) ($data['glucides'] ?? 0),
            (float) ($data['lipides'] ?? 0),
            (int) $data['id']
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM aliments WHERE id = ?");
        return $stmt->execute([(int) $id]);
    }
}
