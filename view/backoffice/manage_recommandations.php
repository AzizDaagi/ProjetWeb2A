<?php
$pageTitle = 'Options: Règles & Recommandations';
require_once __DIR__ . '/../../controler/RecommandationController.php';

$controller = new RecommandationController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $controller->addRecommandation($_POST['titre'], $_POST['type_objectif'], $_POST['contenu_regle']);
        } elseif ($_POST['action'] === 'delete') {
            $controller->deleteRecommandation($_POST['id']);
        }
        header('Location: manage_recommandations.php');
        exit;
    }
}

$recommandations = $controller->listRecommandations();
require_once __DIR__ . '/../template_only/layouts/header.php'; 
?>

<div class="container admin-dashboard">
    <a href="index.php" class="btn" style="background: #6c757d; color: white; text-decoration: none; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> Retour Dashboard
    </a>
    
    <h1>Créer une Recommandation Nutritionnelle</h1>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        
        <div class="form-group">
            <label>Titre de la Règle</label>
            <input type="text" name="titre" required>
        </div>
        <div class="form-group">
            <label>Type d'Objectif</label>
            <select name="type_objectif" required style="width: 100%; padding: 10px;">
                <option value="Perte de poids">Perte de poids</option>
                <option value="Prise de masse">Prise de masse</option>
                <option value="Maintien">Maintien</option>
                <option value="Santé Globale">Santé Globale</option>
            </select>
        </div>
        <div class="form-group">
            <label>Contenu / Explication</label>
            <textarea name="contenu_regle" required style="min-height: 100px;"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Ajouter Règle</button>
    </form>

    <h2 style="margin-top: 40px;">Liste des Recommandations</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Objectif</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recommandations as $r): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $r['titre']) ?></td>
                    <td><span style="background: #17a2b8; color: white; padding: 5px; border-radius: 4px;"><?= htmlspecialchars((string) $r['type_objectif']) ?></span></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
