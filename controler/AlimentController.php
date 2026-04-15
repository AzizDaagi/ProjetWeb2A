<?php
require_once __DIR__ . '/../model/Aliment.php';
require_once __DIR__ . '/config.php';

class AlimentController {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function listAliments() {
        $query = $this->db->query("SELECT * FROM aliments");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAliment($id) {
        $query = $this->db->prepare("SELECT * FROM aliments WHERE id = :id");
        $query->execute(['id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function countAliments() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM aliments");
        return $query->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function addAliment($nom, $calories, $proteines, $glucides, $lipides, $type, $image_url = null) {
        $query = $this->db->prepare("INSERT INTO aliments (nom, calories, proteines, glucides, lipides, type, image_url) VALUES (:nom, :calories, :proteines, :glucides, :lipides, :type, :image_url)");
        $query->execute([
            'nom' => $nom,
            'calories' => $calories,
            'proteines' => $proteines,
            'glucides' => $glucides,
            'lipides' => $lipides,
            'type' => $type,
            'image_url' => $image_url
        ]);
    }

    public function updateAliment($id, $nom, $calories, $proteines, $glucides, $lipides, $type, $image_url = null) {
        $query = $this->db->prepare("UPDATE aliments SET nom = :nom, calories = :calories, proteines = :proteines, glucides = :glucides, lipides = :lipides, type = :type, image_url = :image_url WHERE id = :id");
        $query->execute([
            'nom' => $nom,
            'calories' => $calories,
            'proteines' => $proteines,
            'glucides' => $glucides,
            'lipides' => $lipides,
            'type' => $type,
            'image_url' => $image_url,
            'id' => $id
        ]);
    }

    public function deleteAliment($id) {
        $query = $this->db->prepare("DELETE FROM aliments WHERE id = :id");
        $query->execute(['id' => $id]);
    }
}
?>
