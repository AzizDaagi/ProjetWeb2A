<?php
require_once __DIR__ . '/../model/Recommandation.php';
require_once __DIR__ . '/config.php';

class RecommandationController {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function listRecommandations() {
        $query = $this->db->query("SELECT * FROM recommandations");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addRecommandation($titre, $type_objectif, $contenu_regle) {
        $query = $this->db->prepare("INSERT INTO recommandations (titre, type_objectif, contenu_regle) VALUES (:titre, :type_objectif, :contenu_regle)");
        $query->execute([
            'titre' => $titre,
            'type_objectif' => $type_objectif,
            'contenu_regle' => $contenu_regle
        ]);
    }

    public function updateRecommandation($id, $titre, $type_objectif, $contenu_regle) {
        $query = $this->db->prepare("UPDATE recommandations SET titre = :titre, type_objectif = :type_objectif, contenu_regle = :contenu_regle WHERE id = :id");
        $query->execute([
            'titre' => $titre,
            'type_objectif' => $type_objectif,
            'contenu_regle' => $contenu_regle,
            'id' => $id
        ]);
    }

    public function deleteRecommandation($id) {
        $query = $this->db->prepare("DELETE FROM recommandations WHERE id = :id");
        $query->execute(['id' => $id]);
    }
}
?>
