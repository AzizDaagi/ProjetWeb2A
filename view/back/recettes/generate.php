<?php
$pageTitle = 'Generation de recette';
$currentSection = 'recipes';
$backofficeReturnUrl = 'index.php?action=admin-recipes';
$backofficeReturnLabel = 'Retour aux recettes';

require_once __DIR__ . '/../../../controller/RecetteController.php';

$controller = new RecetteController();
$generated = null;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $generated = $controller->generateRecipeFromConstraints(
        (float) ($_POST['max_kcal'] ?? 0),
        (float) ($_POST['min_prot'] ?? 0),
        (float) ($_POST['max_lipides'] ?? 0),
        $_POST['diet_type'] ?? 'standard'
    );

    if (!$generated) {
        $errorMessage = 'Impossible de generer une recette avec ces contraintes. Verifiez le catalogue aliments ou les migrations SQL.';
    }
}

require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
?>
<div class="main-content">
    <div class="admin-page">
        <div class="admin-page-head">
            <h1><i class="fa-solid fa-wand-magic-sparkles icon"></i> Generation de recettes</h1>
            <p class="subtitle">Moteur de proposition rapide base sur les macros et le regime choisis.</p>
        </div>

        <?php if ($errorMessage !== ''): ?>
            <div class="admin-alert admin-alert-error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <section class="admin-widget">
            <form method="POST" action="index.php?action=admin-recipe-generate" class="admin-form">
                <input type="hidden" name="generate" value="1">

                <div class="form-grid">
                    <div class="field">
                        <label for="max_kcal">Calories max</label>
                        <input type="number" id="max_kcal" name="max_kcal" min="1" value="<?= htmlspecialchars((string) ($_POST['max_kcal'] ?? '500')) ?>" required>
                    </div>

                    <div class="field">
                        <label for="min_prot">Proteines min</label>
                        <input type="number" id="min_prot" name="min_prot" min="0" value="<?= htmlspecialchars((string) ($_POST['min_prot'] ?? '30')) ?>" required>
                    </div>

                    <div class="field">
                        <label for="max_lipides">Lipides max</label>
                        <input type="number" id="max_lipides" name="max_lipides" min="0" value="<?= htmlspecialchars((string) ($_POST['max_lipides'] ?? '20')) ?>" required>
                    </div>

                    <div class="field">
                        <label for="diet_type">Regime</label>
                        <?php
                        $dietOptions = [
                            'standard' => 'Standard',
                            'vegetarien' => 'Vegetarien',
                            'sans_gluten' => 'Sans gluten',
                        ];
                        $selectedDiet = $_POST['diet_type'] ?? 'standard';
                        ?>
                        <select id="diet_type" name="diet_type">
                            <?php foreach ($dietOptions as $dietValue => $dietLabel): ?>
                                <option value="<?= htmlspecialchars($dietValue) ?>" <?= $selectedDiet === $dietValue ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dietLabel) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fa-solid fa-cogs"></i>
                        Generer une combinaison
                    </button>
                </div>
            </form>
        </section>

        <?php if (!empty($generated)): ?>
            <section class="admin-widget">
                <div class="admin-widget-head">
                    <div>
                        <h2 style="margin: 0;">Combinaison proposee</h2>
                        <p style="margin: 6px 0 0; color: var(--text-muted);">Score d'optimisation : <?= number_format((float) ($generated['score'] ?? 0), 2, '.', ' ') ?>.</p>
                    </div>
                </div>

                <div style="display: grid; gap: 12px; margin-bottom: 18px;">
                    <?php foreach (($generated['aliments'] ?? []) as $item): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; border-radius: 12px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div>
                                <strong><?= htmlspecialchars((string) ($item['aliment']['nom'] ?? 'Aliment')) ?></strong><br>
                                <span style="color: var(--text-muted);"><?= number_format((float) ($item['quantite'] ?? 0), 0, '.', ' ') ?> g</span>
                            </div>
                            <span class="admin-badge"><?= number_format((float) ($item['aliment']['calories'] ?? 0), 0, '.', ' ') ?> kcal / 100g</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-grid" style="margin-bottom: 18px;">
                    <div class="admin-widget" style="padding: 14px;">
                        <strong><?= number_format((float) ($generated['totaux']['calories'] ?? 0), 0, '.', ' ') ?> kcal</strong>
                        <p style="margin: 6px 0 0; color: var(--text-muted);">Calories estimees</p>
                    </div>
                    <div class="admin-widget" style="padding: 14px;">
                        <strong><?= number_format((float) ($generated['totaux']['proteines'] ?? 0), 1, '.', ' ') ?> g</strong>
                        <p style="margin: 6px 0 0; color: var(--text-muted);">Proteines</p>
                    </div>
                    <div class="admin-widget" style="padding: 14px;">
                        <strong><?= number_format((float) ($generated['totaux']['glucides'] ?? 0), 1, '.', ' ') ?> g</strong>
                        <p style="margin: 6px 0 0; color: var(--text-muted);">Glucides</p>
                    </div>
                    <div class="admin-widget" style="padding: 14px;">
                        <strong><?= number_format((float) ($generated['totaux']['lipides'] ?? 0), 1, '.', ' ') ?> g</strong>
                        <p style="margin: 6px 0 0; color: var(--text-muted);">Lipides</p>
                    </div>
                </div>

                <form method="POST" action="index.php?action=admin-recipes" class="admin-form">
                    <input type="hidden" name="action" value="add">

                    <div class="form-grid">
                        <div class="field">
                            <label for="generated-name">Nom de la recette</label>
                            <input type="text" id="generated-name" name="nom" value="Recette generee automatiquement" required>
                        </div>

                        <div class="field">
                            <label for="generated-time">Temps de preparation</label>
                            <input type="text" id="generated-time" name="temps_preparation" value="15 minutes" required>
                        </div>

                        <div class="field">
                            <label for="generated-level">Difficulte</label>
                            <select id="generated-level" name="niveau_difficulte" required>
                                <option value="Tres Facile">Tres Facile</option>
                                <option value="Facile">Facile</option>
                                <option value="Moyen">Moyen</option>
                            </select>
                        </div>

                        <div class="field field-full">
                            <label for="generated-description">Description</label>
                            <textarea id="generated-description" name="description" required style="width: 100%; min-height: 120px; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.04); color: inherit;">Recette generee automatiquement a partir des contraintes de macros selectionnees.</textarea>
                        </div>
                    </div>

                    <?php foreach (($generated['aliments'] ?? []) as $item): ?>
                        <?php $alimentId = (int) ($item['aliment']['id'] ?? 0); ?>
                        <input type="hidden" name="aliments[]" value="<?= htmlspecialchars((string) $alimentId) ?>">
                        <input type="hidden" name="quantites[<?= $alimentId ?>]" value="<?= htmlspecialchars((string) ($item['quantite'] ?? 0)) ?>">
                    <?php endforeach; ?>

                    <div class="admin-form-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Enregistrer cette recette
                        </button>
                    </div>
                </form>
            </section>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
