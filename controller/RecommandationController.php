<?php

require_once __DIR__ . '/../model/Recommandation.php';
require_once __DIR__ . '/../model/Database.php';

class RecommandationController
{
    private PDO $db;
    private string $baseUrl = '/projet-web-25-26';

    public function __construct($pdo = null)
    {
        $this->db = $pdo instanceof PDO ? $pdo : Database::getConnection();
    }

    public function listRecommandations(): array
    {
        if (!Database::tableExists($this->db, 'recommandations')) {
            return [];
        }

        $query = $this->db->query("SELECT * FROM recommandations ORDER BY id DESC");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addRecommandation($titre, $type_objectif, $contenu_regle): void
    {
        $query = $this->db->prepare("INSERT INTO recommandations (titre, type_objectif, contenu_regle) VALUES (:titre, :type_objectif, :contenu_regle)");
        $query->execute([
            'titre' => $titre,
            'type_objectif' => $type_objectif,
            'contenu_regle' => $contenu_regle,
        ]);
    }

    public function updateRecommandation($id, $titre, $type_objectif, $contenu_regle): void
    {
        $query = $this->db->prepare("UPDATE recommandations SET titre = :titre, type_objectif = :type_objectif, contenu_regle = :contenu_regle WHERE id = :id");
        $query->execute([
            'titre' => $titre,
            'type_objectif' => $type_objectif,
            'contenu_regle' => $contenu_regle,
            'id' => $id,
        ]);
    }

    public function deleteRecommandation($id): void
    {
        $query = $this->db->prepare("DELETE FROM recommandations WHERE id = :id");
        $query->execute(['id' => $id]);
    }

    public function adminIndex(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'add') {
                $this->addRecommandation($_POST['titre'] ?? '', $_POST['type_objectif'] ?? '', $_POST['contenu_regle'] ?? '');
            } elseif ($_POST['action'] === 'delete') {
                $this->deleteRecommandation((int) ($_POST['id'] ?? 0));
            }

            header('Location: ' . $this->baseUrl . '/index.php?action=admin-recommendations');
            exit;
        }

        $recommandations = $this->listRecommandations();
        $pageTitle = 'Back Office - Recommandations';
        $isAdminTemplate = true;
        $bodyClass = 'backoffice-page recommendations-admin-page';
        $baseUrl = $this->baseUrl;
        require __DIR__ . '/../view/layouts/header.php';
        require __DIR__ . '/../view/back/recommandations/index.php';
        require __DIR__ . '/../view/layouts/footer.php';
    }
}
