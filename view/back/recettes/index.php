<?php
$pageTitle = 'Gestion des recettes';
$currentSection = 'recipes';
$backofficeReturnUrl = 'index.php?action=recipes-management';
$backofficeReturnLabel = 'Retour a la liste publique';

require_once __DIR__ . '/../../../controller/RecetteController.php';

$projectBaseUrl = $baseUrl ?? '/projet-web-25-26';
$routeBase = $projectBaseUrl . '/index.php';
$controller = new RecetteController();

$storeRecipeImage = static function ($projectBaseUrl, $uploadedFile) {
    if (
        !is_array($uploadedFile)
        || ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
        || empty($uploadedFile['tmp_name'])
    ) {
        return null;
    }

    $uploadDir = __DIR__ . '/../../uploads/recettes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = basename((string) ($uploadedFile['name'] ?? ''));
    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'recipe.jpg';
    $fileName = uniqid('recette_', true) . '_' . $safeName;
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
        return null;
    }

    return $projectBaseUrl . '/view/uploads/recettes/' . $fileName;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string) $_POST['action'];
    $selectedAliments = array_map('intval', $_POST['aliments'] ?? []);
    $rawQuantites = is_array($_POST['quantites'] ?? null) ? $_POST['quantites'] : [];
    $alimentsQuantites = [];

    foreach ($selectedAliments as $alimentId) {
        if ($alimentId > 0) {
            $alimentsQuantites[$alimentId] = $rawQuantites[$alimentId] ?? 0;
        }
    }

    $imageUrl = trim((string) ($_POST['existing_image_url'] ?? '')) ?: null;
    $uploadedImage = $storeRecipeImage($projectBaseUrl, $_FILES['image'] ?? null);
    if ($uploadedImage !== null) {
        $imageUrl = $uploadedImage;
    }

    $isSuccessful = false;
    if ($action === 'add') {
        $isSuccessful = $controller->addRecette(
            $_POST['nom'] ?? '',
            $_POST['description'] ?? '',
            $_POST['temps_preparation'] ?? '',
            $_POST['niveau_difficulte'] ?? '',
            $imageUrl,
            $alimentsQuantites
        );
        $_SESSION['admin_recette_success'] = $isSuccessful ? 'Recette creee avec succes.' : null;
        $_SESSION['admin_recette_error'] = $isSuccessful ? null : 'Impossible de creer la recette. Verifiez les tables recettes.';
    } elseif ($action === 'update') {
        $isSuccessful = $controller->updateRecette(
            $_POST['id'] ?? 0,
            $_POST['nom'] ?? '',
            $_POST['description'] ?? '',
            $_POST['temps_preparation'] ?? '',
            $_POST['niveau_difficulte'] ?? '',
            $imageUrl,
            $alimentsQuantites
        );
        $_SESSION['admin_recette_success'] = $isSuccessful ? 'Recette mise a jour avec succes.' : null;
        $_SESSION['admin_recette_error'] = $isSuccessful ? null : 'Impossible de mettre a jour la recette.';
    } elseif ($action === 'delete') {
        $isSuccessful = $controller->deleteRecette($_POST['id'] ?? 0);
        $_SESSION['admin_recette_success'] = $isSuccessful ? 'Recette supprimee avec succes.' : null;
        $_SESSION['admin_recette_error'] = $isSuccessful ? null : 'Impossible de supprimer la recette.';
    }

    if ($isSuccessful && in_array($action, ['add', 'update'], true)) {
        $_SESSION['admin_recette_warnings'] = $controller->checkEquilibreNutritionnel($alimentsQuantites);
    }

    $redirectUrl = $routeBase . '?action=admin-recipes';
    if ($action === 'update' && !empty($_POST['id']) && !$isSuccessful) {
        $redirectUrl .= '&edit_id=' . urlencode((string) $_POST['id']);
    }

    header('Location: ' . $redirectUrl);
    exit;
}

$successMessage = $_SESSION['admin_recette_success'] ?? null;
$errorMessage = $_SESSION['admin_recette_error'] ?? null;
$warningMessages = $_SESSION['admin_recette_warnings'] ?? [];
unset($_SESSION['admin_recette_success'], $_SESSION['admin_recette_error'], $_SESSION['admin_recette_warnings']);

$recettes = $controller->listRecettes();
$tousAliments = $controller->listAliments();
$editId = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;
$recetteToEdit = $editId > 0 ? $controller->getRecette($editId) : null;
$selectedQuantites = [];

if ($recetteToEdit) {
    foreach ($controller->getAlimentsByRecette($editId) as $alimentAssocie) {
        $selectedQuantites[(int) $alimentAssocie['id']] = (float) ($alimentAssocie['quantite'] ?? 0);
    }
}

require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
?>
<div class="main-content">
    <div class="admin-page">
        <div class="admin-page-head admin-page-head-inline">
            <div>
                <h1><i class="fa-solid fa-book-open icon"></i> Gestion des recettes</h1>
                <p class="subtitle">Le module recettes est branche sur le routeur principal et reutilise le catalogue d'aliments existant.</p>
            </div>

            <div class="admin-action-group">
                <a href="index.php?action=admin-recipes" class="admin-btn admin-btn-secondary">
                    <i class="fa-solid fa-plus"></i>
                    Nouvelle recette
                </a>
                <a href="index.php?action=admin-recipe-generate" class="admin-btn admin-btn-primary">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Generer une recette
                </a>
                <a href="index.php?controller=backoffice&action=suivi" class="admin-btn admin-btn-secondary">
                    <i class="fa-solid fa-apple-whole"></i>
                    Catalogue aliments
                </a>
            </div>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="admin-alert admin-alert-success"><?= htmlspecialchars((string) $successMessage) ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="admin-alert admin-alert-error"><?= htmlspecialchars((string) $errorMessage) ?></div>
        <?php endif; ?>

        <?php if (!empty($warningMessages)): ?>
            <div class="admin-alert" style="background: rgba(243, 156, 18, 0.16); border-color: rgba(243, 156, 18, 0.35); color: #ffe3b5;">
                <strong>Equilibre nutritionnel a verifier :</strong>
                <ul style="margin: 10px 0 0; padding-left: 18px;">
                    <?php foreach ($warningMessages as $warningMessage): ?>
                        <li><?= htmlspecialchars((string) $warningMessage) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="admin-widget">
            <div class="admin-widget-head">
                <div>
                    <h2 style="margin: 0;"><?= $recetteToEdit ? 'Modifier la recette' : 'Creer une recette' ?></h2>
                    <p style="margin: 6px 0 0; color: var(--text-muted);">Associez des aliments existants pour calculer automatiquement les macros de chaque recette.</p>
                </div>
            </div>

            <form method="POST" action="index.php?action=admin-recipes<?= $recetteToEdit ? '&edit_id=' . urlencode((string) $recetteToEdit['id']) : '' ?>" enctype="multipart/form-data" class="admin-form">
                <input type="hidden" name="action" value="<?= $recetteToEdit ? 'update' : 'add' ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($recetteToEdit['id'] ?? '')) ?>">
                <input type="hidden" name="existing_image_url" value="<?= htmlspecialchars((string) ($recetteToEdit['image_url'] ?? '')) ?>">

                <div class="form-grid">
                    <div class="field">
                        <label for="recipe-name">Nom</label>
                        <input type="text" id="recipe-name" name="nom" required value="<?= htmlspecialchars((string) ($recetteToEdit['nom'] ?? '')) ?>" placeholder="Ex: Bowl proteine">
                    </div>

                    <div class="field">
                        <label for="recipe-time">Temps de preparation</label>
                        <input type="text" id="recipe-time" name="temps_preparation" required value="<?= htmlspecialchars((string) ($recetteToEdit['temps_preparation'] ?? '')) ?>" placeholder="Ex: 20 minutes">
                    </div>

                    <div class="field">
                        <label for="recipe-level">Difficulte</label>
                        <select id="recipe-level" name="niveau_difficulte" required>
                            <option value="">Selectionnez</option>
                            <?php foreach (['Tres Facile', 'Facile', 'Moyen', 'Difficile', 'Expert'] as $niveau): ?>
                                <option value="<?= htmlspecialchars($niveau) ?>" <?= (($recetteToEdit['niveau_difficulte'] ?? '') === $niveau) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($niveau) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="recipe-image">Image</label>
                        <input type="file" id="recipe-image" name="image" accept="image/*">
                    </div>

                    <div class="field field-full">
                        <label for="recipe-description">Description</label>
                        <textarea id="recipe-description" name="description" required style="width: 100%; min-height: 140px; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.04); color: inherit;"><?= htmlspecialchars((string) ($recetteToEdit['description'] ?? '')) ?></textarea>
                    </div>

                    <div class="field field-full">
                        <label>Ingredients lies</label>
                        <div style="display: grid; gap: 10px; max-height: 320px; overflow-y: auto; padding: 14px; border-radius: 14px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                            <?php if (!empty($tousAliments)): ?>
                                <?php foreach ($tousAliments as $aliment): ?>
                                    <?php
                                    $alimentId = (int) $aliment['id'];
                                    $isChecked = array_key_exists($alimentId, $selectedQuantites);
                                    $currentQuantite = $selectedQuantites[$alimentId] ?? 0;
                                    ?>
                                    <div style="display: grid; grid-template-columns: minmax(0, 1fr) 120px auto; gap: 12px; align-items: center;">
                                        <label style="display: flex; align-items: center; gap: 10px;">
                                            <input
                                                type="checkbox"
                                                name="aliments[]"
                                                value="<?= $alimentId ?>"
                                                <?= $isChecked ? 'checked' : '' ?>
                                                onchange="document.getElementById('quantite_<?= $alimentId ?>').disabled = !this.checked;"
                                            >
                                            <span><?= htmlspecialchars((string) $aliment['nom']) ?></span>
                                        </label>
                                        <input
                                            type="number"
                                            id="quantite_<?= $alimentId ?>"
                                            name="quantites[<?= $alimentId ?>]"
                                            min="1"
                                            step="1"
                                            value="<?= $currentQuantite > 0 ? htmlspecialchars((string) $currentQuantite) : '' ?>"
                                            placeholder="Quantite (g)"
                                            <?= $isChecked ? '' : 'disabled' ?>
                                        >
                                        <span class="admin-badge"><?= number_format((float) ($aliment['calories'] ?? 0), 0, '.', ' ') ?> kcal</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="margin: 0; color: var(--text-muted);">
                                    Aucun aliment disponible. Creez d'abord un aliment dans
                                    <a href="index.php?controller=backoffice&action=suiviCreate" class="admin-link-inline">le backoffice suivi</a>.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($recetteToEdit['image_url'])): ?>
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <img
                            src="<?= htmlspecialchars((string) $recetteToEdit['image_url']) ?>"
                            alt="<?= htmlspecialchars((string) $recetteToEdit['nom']) ?>"
                            style="width: 140px; height: 92px; object-fit: cover; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.08);"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                        >
                        <span class="admin-badge" style="display: none;">Image indisponible</span>
                    </div>
                <?php endif; ?>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <?= $recetteToEdit ? 'Sauvegarder les modifications' : 'Creer la recette' ?>
                    </button>

                    <?php if ($recetteToEdit): ?>
                        <a href="index.php?action=admin-recipes" class="admin-btn admin-btn-secondary">
                            <i class="fa-solid fa-xmark"></i>
                            Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="admin-widget">
            <div class="admin-widget-head">
                <div>
                    <h2 style="margin: 0;">Catalogue recettes</h2>
                    <p style="margin: 6px 0 0; color: var(--text-muted);">Vue admin sur les recettes exposees par le module front.</p>
                </div>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Recette</th>
                        <th>Difficulte</th>
                        <th>Ingredients</th>
                        <th>Nutrition</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recettes)): ?>
                        <?php foreach ($recettes as $recette): ?>
                            <?php
                            $nutrition = $controller->calculerNutritionTotale($recette['id']);
                            $ingredients = $controller->getAlimentsByRecette($recette['id']);
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars((string) $recette['nom']) ?></strong><br>
                                    <span style="color: var(--text-muted);"><?= htmlspecialchars((string) ($recette['temps_preparation'] ?? '-')) ?></span>
                                </td>
                                <td><span class="admin-badge"><?= htmlspecialchars((string) ($recette['niveau_difficulte'] ?? '-')) ?></span></td>
                                <td><?= number_format(count($ingredients), 0, '.', ' ') ?> ingredient(s)</td>
                                <td>
                                    <?= number_format((float) ($nutrition['calories'] ?? 0), 0, '.', ' ') ?> kcal<br>
                                    <span style="color: var(--text-muted);">
                                        P <?= number_format((float) ($nutrition['proteines'] ?? 0), 1, '.', ' ') ?> g /
                                        G <?= number_format((float) ($nutrition['glucides'] ?? 0), 1, '.', ' ') ?> g /
                                        L <?= number_format((float) ($nutrition['lipides'] ?? 0), 1, '.', ' ') ?> g
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-action-group">
                                        <a href="index.php?action=recipe-details&id=<?= urlencode((string) $recette['id']) ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
                                            <i class="fa-solid fa-eye"></i>
                                            Voir
                                        </a>
                                        <a href="index.php?action=admin-recipes&edit_id=<?= urlencode((string) $recette['id']) ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
                                            <i class="fa-solid fa-pen"></i>
                                            Modifier
                                        </a>
                                        <form method="POST" action="index.php?action=admin-recipes" onsubmit="return confirm('Supprimer cette recette ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $recette['id']) ?>">
                                            <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">
                                                <i class="fa-solid fa-trash"></i>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="admin-empty-cell">Aucune recette disponible. Verifiez la migration SQL du module recettes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
