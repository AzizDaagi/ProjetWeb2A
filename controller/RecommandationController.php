<?php

require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Recommandation.php';

class RecommandationController
{
    private $db;

    public function __construct($pdo = null)
    {
        $this->db = $pdo instanceof PDO ? $pdo : Database::getConnection();
    }

    public function listRecommandations()
    {
        try {
            $query = $this->db->query('SELECT * FROM recommandations ORDER BY id DESC');
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function addRecommandation($titre, $typeObjectif, $contenuRegle)
    {
        try {
            $query = $this->db->prepare(
                'INSERT INTO recommandations (titre, type_objectif, contenu_regle)
                 VALUES (:titre, :type_objectif, :contenu_regle)'
            );

            return $query->execute([
                'titre' => trim((string) $titre),
                'type_objectif' => trim((string) $typeObjectif),
                'contenu_regle' => trim((string) $contenuRegle),
            ]);
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function updateRecommandation($id, $titre, $typeObjectif, $contenuRegle)
    {
        try {
            $query = $this->db->prepare(
                'UPDATE recommandations
                 SET titre = :titre,
                     type_objectif = :type_objectif,
                     contenu_regle = :contenu_regle
                 WHERE id = :id'
            );

            return $query->execute([
                'id' => (int) $id,
                'titre' => trim((string) $titre),
                'type_objectif' => trim((string) $typeObjectif),
                'contenu_regle' => trim((string) $contenuRegle),
            ]);
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function deleteRecommandation($id)
    {
        try {
            $query = $this->db->prepare('DELETE FROM recommandations WHERE id = :id');
            return $query->execute(['id' => (int) $id]);
        } catch (Throwable $exception) {
            return false;
        }
    }
}
