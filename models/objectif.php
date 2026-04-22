<?php

class Objectif {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function save($data) {
        $sql = "INSERT INTO objectif (calories_cible, objectif_type, proteines, lipides, glucides, date_creation)
                VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            (int) $data['calories_cible'],
            $data['objectif_type'] ?? 'maintien',
            (float) $data['proteines'],
            (float) $data['lipides'],
            (float) $data['glucides']
        ]);

        return $this->pdo->lastInsertId();
    }

    public function getLatest() {
        $stmt = $this->pdo->query("SELECT * FROM objectif ORDER BY id DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM objectif WHERE id = ?");
        $stmt->execute([(int) $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data) {
        $stmt = $this->pdo->prepare("
            UPDATE objectif
            SET calories_cible = ?, objectif_type = ?, proteines = ?, lipides = ?, glucides = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            (int) $data['calories_cible'],
            $data['objectif_type'] ?? 'maintien',
            (float) $data['proteines'],
            (float) $data['lipides'],
            (float) $data['glucides'],
            (int) $data['id']
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM objectif WHERE id = ?");
        return $stmt->execute([(int) $id]);
    }
}
