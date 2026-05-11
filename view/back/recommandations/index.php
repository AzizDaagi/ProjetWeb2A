<?php
$pageTitle = 'Recommandations';
$currentSection = 'recommendations';
$backofficeReturnUrl = 'index.php?action=recipes-management';
$backofficeReturnLabel = 'Retour au module recettes';

require_once __DIR__ . '/../../../controller/RecommandationController.php';

$controller = new RecommandationController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string) $_POST['action'];
    $isSuccessful = false;

    if ($action === 'add') {
        $isSuccessful = $controller->addRecommandation(
            $_POST['titre'] ?? '',
            $_POST['type_objectif'] ?? '',
            $_POST['contenu_regle'] ?? ''
        );
        $_SESSION['admin_recommandation_success'] = $isSuccessful ? 'Recommandation ajoutee avec succes.' : null;
        $_SESSION['admin_recommandation_error'] = $isSuccessful ? null : 'Impossible d\'ajouter la recommandation.';
    } elseif ($action === 'delete') {
        $isSuccessful = $controller->deleteRecommandation($_POST['id'] ?? 0);
        $_SESSION['admin_recommandation_success'] = $isSuccessful ? 'Recommandation supprimee avec succes.' : null;
        $_SESSION['admin_recommandation_error'] = $isSuccessful ? null : 'Impossible de supprimer la recommandation.';
    }

    header('Location: ' . ($baseUrl ?? '/projet-web-25-26') . '/index.php?action=admin-recommendations');
    exit;
}

$recommandations = $controller->listRecommandations();
$successMessage = $_SESSION['admin_recommandation_success'] ?? null;
$errorMessage = $_SESSION['admin_recommandation_error'] ?? null;
unset($_SESSION['admin_recommandation_success'], $_SESSION['admin_recommandation_error']);

require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
?>
<div class="main-content">
    <div class="admin-page">
        <div class="admin-page-head">
            <h1><i class="fa-solid fa-heart-pulse icon"></i> Recommandations nutritionnelles</h1>
            <p class="subtitle">Base de regles simple pour enrichir le module recettes sans impacter les autres modules.</p>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="admin-alert admin-alert-success"><?= htmlspecialchars((string) $successMessage) ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="admin-alert admin-alert-error"><?= htmlspecialchars((string) $errorMessage) ?></div>
        <?php endif; ?>

        <section class="admin-widget">
            <div class="admin-widget-head">
                <div>
                    <h2 style="margin: 0;">Nouvelle recommandation</h2>
                    <p style="margin: 6px 0 0; color: var(--text-muted);">Ces recommandations peuvent etre reutilisees dans les evolutions futures du module recettes.</p>
                </div>
            </div>

            <form method="POST" action="index.php?action=admin-recommendations" class="admin-form">
                <input type="hidden" name="action" value="add">

                <div class="form-grid">
                    <div class="field">
                        <label for="recommendation-title">Titre</label>
                        <input type="text" id="recommendation-title" name="titre" required>
                    </div>

                    <div class="field">
                        <label for="recommendation-type">Objectif</label>
                        <select id="recommendation-type" name="type_objectif" required>
                            <option value="Perte de poids">Perte de poids</option>
                            <option value="Prise de masse">Prise de masse</option>
                            <option value="Maintien">Maintien</option>
                            <option value="Sante globale">Sante globale</option>
                        </select>
                    </div>

                    <div class="field field-full">
                        <label for="recommendation-content">Contenu</label>
                        <textarea id="recommendation-content" name="contenu_regle" required style="width: 100%; min-height: 140px; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.04); color: inherit;"></textarea>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        Ajouter la recommandation
                    </button>
                </div>
            </form>
        </section>

        <section class="admin-widget">
            <div class="admin-widget-head">
                <div>
                    <h2 style="margin: 0;">Catalogue recommandations</h2>
                    <p style="margin: 6px 0 0; color: var(--text-muted);">Vue centralisee des recommandations importees depuis GestionRecettes.</p>
                </div>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Objectif</th>
                        <th>Contenu</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recommandations)): ?>
                        <?php foreach ($recommandations as $recommandation): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($recommandation['titre'] ?? '')) ?></td>
                                <td><span class="admin-badge"><?= htmlspecialchars((string) ($recommandation['type_objectif'] ?? '')) ?></span></td>
                                <td><?= nl2br(htmlspecialchars((string) ($recommandation['contenu_regle'] ?? ''))) ?></td>
                                <td>
                                    <form method="POST" action="index.php?action=admin-recommendations" onsubmit="return confirm('Supprimer cette recommandation ?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($recommandation['id'] ?? '')) ?>">
                                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">
                                            <i class="fa-solid fa-trash"></i>
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="admin-empty-cell">Aucune recommandation disponible. Verifiez la table recommandations si besoin.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
