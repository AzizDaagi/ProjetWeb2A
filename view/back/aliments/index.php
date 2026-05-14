<?php
$aliments = $aliments ?? [];
$baseUrl = $baseUrl ?? '/projet-web-25-26';
$alimentsError = $alimentsError ?? null;
$successMessage = $_SESSION['admin_aliment_success'] ?? null;
$errorMessage = $_SESSION['admin_aliment_error'] ?? null;
unset($_SESSION['admin_aliment_success'], $_SESSION['admin_aliment_error']);
?>
<!-- BACKOFFICE_ALIMENTS_OK -->
<div class="admin-page admin-module-page">
    <div class="admin-page-header">
        <span class="admin-page-kicker">Catalogue approuve</span>
        <h1>Aliments</h1>
        <p>Gestion du catalogue alimentaire utilise par le suivi nutritionnel et les recettes.</p>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="admin-alert admin-alert-success"><?= htmlspecialchars((string) $successMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="admin-alert admin-alert-error"><?= htmlspecialchars((string) $errorMessage) ?></div>
    <?php endif; ?>

    <section class="admin-card">
        <div class="admin-actions">
            <a href="<?= $baseUrl ?>/index.php?action=admin-aliment-create" class="admin-btn">
                <i class="fa-solid fa-plus"></i>
                Ajouter un aliment
            </a>
        </div>
    </section>

    <section class="admin-card">
        <?php if (!empty($alimentsError)): ?>
            <div class="admin-empty-state">
                Service temporairement indisponible. Impossible de charger les aliments pour le moment.
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Calories</th>
                            <th>Prot.</th>
                            <th>Gluc.</th>
                            <th>Lip.</th>
                            <th>Unite</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($aliments)): ?>
                            <?php foreach ($aliments as $aliment): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) $aliment['nom']) ?></td>
                                    <td><?= number_format((float) $aliment['calories'], 0, '.', ' ') ?> <?= ($aliment['unite'] ?? 'g') === 'piece' ? 'kcal / piece' : 'kcal / 100g' ?></td>
                                    <td><?= htmlspecialchars((string) ($aliment['proteines'] ?? 0)) ?> g</td>
                                    <td><?= htmlspecialchars((string) ($aliment['glucides'] ?? 0)) ?> g</td>
                                    <td><?= htmlspecialchars((string) ($aliment['lipides'] ?? 0)) ?> g</td>
                                    <td><?= htmlspecialchars((string) ($aliment['unite'] ?? 'g')) ?></td>
                                    <td><span class="admin-inline-badge"><?= htmlspecialchars((string) ($aliment['type'] ?? '-')) ?></span></td>
                                    <td>
                                        <div class="admin-actions admin-actions-inline">
                                            <a href="<?= $baseUrl ?>/index.php?action=admin-aliment-edit&id=<?= urlencode((string) $aliment['id']) ?>" class="admin-btn secondary admin-btn-sm">
                                                <i class="fa-solid fa-pen"></i>
                                                Modifier
                                            </a>
                                            <a href="<?= $baseUrl ?>/index.php?action=admin-aliment-delete&id=<?= urlencode((string) $aliment['id']) ?>" class="admin-btn danger admin-btn-sm" onclick="return confirm('Supprimer cet aliment ?')">
                                                <i class="fa-solid fa-trash"></i>
                                                Supprimer
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="admin-empty-state">Aucun aliment enregistre pour le moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
