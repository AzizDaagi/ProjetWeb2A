<?php
$objectifs = $objectifs ?? [];
$sexeOptions = $sexeOptions ?? [];
$objectifTypeOptions = $objectifTypeOptions ?? [];
$successMessage = $_SESSION['admin_objectif_success'] ?? null;
$errorMessage = $_SESSION['admin_objectif_error'] ?? null;
unset($_SESSION['admin_objectif_success'], $_SESSION['admin_objectif_error']);
?>
<!-- BACKOFFICE_OBJECTIFS_OK -->
<div class="admin-page">
    <div class="admin-page-head">
        <h1><i class="fa-solid fa-bullseye icon"></i> Objectifs nutritionnels</h1>
        <p class="subtitle">Historique complet des objectifs calcules automatiquement a partir du profil physique.</p>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="admin-alert admin-alert-success"><?= htmlspecialchars((string) $successMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="admin-alert admin-alert-error"><?= htmlspecialchars((string) $errorMessage) ?></div>
    <?php endif; ?>

    <section class="admin-widget admin-data-panel">
        <div class="admin-table-wrap">
        <table class="admin-table admin-table-enhanced admin-table-objectifs">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Calories</th>
                    <th>Profil</th>
                    <th>Activite</th>
                    <th>Type</th>
                    <th>Repas lies</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($objectifs)): ?>
                    <?php foreach ($objectifs as $objectif): ?>
                        <tr>
                            <td class="admin-cell-id"><?= htmlspecialchars((string) $objectif['id']) ?></td>
                            <td class="admin-cell-date"><?= htmlspecialchars((string) ($objectif['date_creation'] ?? '-')) ?></td>
                            <td class="admin-cell-kcal"><?= number_format((float) ($objectif['calories_cible'] ?? 0), 0, '.', ' ') ?> kcal</td>
                            <td class="admin-cell-profile">
                                <?= number_format((float) ($objectif['poids'] ?? 0), 1, '.', ' ') ?> kg
                                /
                                <?= number_format((float) ($objectif['taille'] ?? 0), 0, '.', ' ') ?> cm
                                /
                                <?= htmlspecialchars((string) ($objectif['age'] ?? '-')) ?> ans
                                <br>
                                <span class="admin-badge"><?= htmlspecialchars((string) ($sexeOptions[$objectif['sexe'] ?? ''] ?? '-')) ?></span>
                            </td>
                            <td class="admin-cell-activity"><?= htmlspecialchars((string) ($objectif['activite_label'] ?? '-')) ?></td>
                            <td><span class="admin-badge admin-badge-type"><?= htmlspecialchars((string) ($objectifTypeOptions[$objectif['objectif_type'] ?? ''] ?? '-')) ?></span></td>
                            <td class="admin-cell-count"><?= number_format((int) ($objectif['repas_count'] ?? 0), 0, '.', ' ') ?></td>
                            <td class="admin-cell-actions">
                                <div class="admin-action-group">
                                    <a href="index.php?controller=backoffice&action=objectifShow&id=<?= urlencode((string) $objectif['id']) ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
                                        <i class="fa-solid fa-eye"></i>
                                        Details
                                    </a>

                                    <?php if ((int) ($objectif['repas_count'] ?? 0) === 0): ?>
                                        <a href="index.php?controller=backoffice&action=objectifDelete&id=<?= urlencode((string) $objectif['id']) ?>" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('Supprimer cet objectif ?');">
                                            <i class="fa-solid fa-trash"></i>
                                            Supprimer
                                        </a>
                                    <?php else: ?>
                                        <span class="admin-badge admin-badge-muted">Suppression bloquee</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="admin-empty-cell">Aucun objectif enregistre pour le moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </section>
</div>
