<?php
require_once __DIR__ . '/../model/Recette.php';
require_once __DIR__ . '/config.php';

class RecetteController {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function listRecettes() {
        $query = $this->db->query("SELECT * FROM recettes");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countRecettes() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM recettes");
        return $query->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getLatestRecettes($limit = 5) {
        $query = $this->db->query("SELECT * FROM recettes ORDER BY id DESC LIMIT $limit");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecette($id) {
        $query = $this->db->prepare("SELECT * FROM recettes WHERE id = :id");
        $query->execute(['id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Récupérer les aliments associés à une recette
    public function getAlimentsByRecette($id_recette) {
        $query = $this->db->prepare("
            SELECT a.* FROM aliments a
            JOIN recette_aliment ra ON a.id = ra.id_aliment
            WHERE ra.id_recette = :id_recette
        ");
        $query->execute(['id_recette' => $id_recette]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addRecette($nom, $description, $temps_preparation, $niveau_difficulte, $image_url = null, $aliments_ids = []) {
        $query = $this->db->prepare("INSERT INTO recettes (nom, description, temps_preparation, niveau_difficulte, image_url) VALUES (:nom, :description, :temps_preparation, :niveau_difficulte, :image_url)");
        $query->execute([
            'nom' => $nom,
            'description' => $description,
            'temps_preparation' => $temps_preparation,
            'niveau_difficulte' => $niveau_difficulte,
            'image_url' => $image_url
        ]);
        
        $recetteId = $this->db->lastInsertId();

        // Insérer les aliments associés
        if (!empty($aliments_ids)) {
            $stmt = $this->db->prepare("INSERT INTO recette_aliment (id_recette, id_aliment) VALUES (:id_recette, :id_aliment)");
            foreach ($aliments_ids as $id_aliment) {
                $stmt->execute(['id_recette' => $recetteId, 'id_aliment' => $id_aliment]);
            }
        }
    }

    public function updateRecette($id, $nom, $description, $temps_preparation, $niveau_difficulte, $image_url = null, $aliments_ids = []) {
        $query = $this->db->prepare("UPDATE recettes SET nom = :nom, description = :description, temps_preparation = :temps_preparation, niveau_difficulte = :niveau_difficulte, image_url = :image_url WHERE id = :id");
        $query->execute([
            'nom' => $nom,
            'description' => $description,
            'temps_preparation' => $temps_preparation,
            'niveau_difficulte' => $niveau_difficulte,
            'image_url' => $image_url,
            'id' => $id
        ]);

        // Mettre à jour les aliments associés (supprimer puis recréer)
        $del = $this->db->prepare("DELETE FROM recette_aliment WHERE id_recette = :id_recette");
        $del->execute(['id_recette' => $id]);

        if (!empty($aliments_ids)) {
            $stmt = $this->db->prepare("INSERT INTO recette_aliment (id_recette, id_aliment) VALUES (:id_recette, :id_aliment)");
            foreach ($aliments_ids as $id_aliment) {
                $stmt->execute(['id_recette' => $id, 'id_aliment' => $id_aliment]);
            }
        }
    }

    public function deleteRecette($id) {
        $query = $this->db->prepare("DELETE FROM recettes WHERE id = :id");
        $query->execute(['id' => $id]);
    }
}
?>
