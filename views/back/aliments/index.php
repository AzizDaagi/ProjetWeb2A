<?php
$aliments = $aliments ?? [];
$successMessage = $_SESSION['admin_aliment_success'] ?? null;
$errorMessage = $_SESSION['admin_aliment_error'] ?? null;
unset($_SESSION['admin_aliment_success'], $_SESSION['admin_aliment_error']);
?>
<div class="admin-page">
    <div class="admin-page-head admin-page-head-inline">
        <div>
            <h1><i class="fa-solid fa-apple-whole icon"></i> Gestion des aliments</h1>
            <p class="subtitle">Catalogue des aliments disponibles dans l'application nutritionnelle.</p>
        </div>

        <a href="index.php?controller=adminAliment&action=create" class="admin-btn admin-btn-primary">
            <i class="fa-solid fa-plus"></i>
            Ajouter un aliment
        </a>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="admin-alert admin-alert-success"><?= htmlspecialchars((string) $successMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="admin-alert admin-alert-error"><?= htmlspecialchars((string) $errorMessage) ?></div>
    <?php endif; ?>

    <section class="admin-widget">
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
                            <td><span class="admin-badge"><?= htmlspecialchars((string) ($aliment['type'] ?? '-')) ?></span></td>
                            <td>
                                <div class="admin-action-group">
                                    <a href="index.php?controller=adminAliment&action=edit&id=<?= urlencode((string) $aliment['id']) ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
                                        <i class="fa-solid fa-pen"></i>
                                        Modifier
                                    </a>

                                    <a href="index.php?controller=adminAliment&action=delete&id=<?= urlencode((string) $aliment['id']) ?>" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('Supprimer cet aliment ?')">
                                        <i class="fa-solid fa-trash"></i>
                                        Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="admin-empty-cell">Aucun aliment enregistre pour le moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>
