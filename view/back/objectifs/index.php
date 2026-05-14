<?php
$objectifs = $objectifs ?? [];
$baseUrl = $baseUrl ?? '/projet-web-25-26';
$sexeOptions = $sexeOptions ?? [];
$objectifTypeOptions = $objectifTypeOptions ?? [];
$objectifsError = $objectifsError ?? null;
$successMessage = $_SESSION['admin_objectif_success'] ?? null;
$errorMessage = $_SESSION['admin_objectif_error'] ?? null;
unset($_SESSION['admin_objectif_success'], $_SESSION['admin_objectif_error']);

$objectifColumns = !empty($objectifs) ? array_keys((array) $objectifs[0]) : [];
$objectifLabels = [
    'id' => '#',
    'date_creation' => 'Date',
    'calories_cible' => 'Calories',
    'poids' => 'Poids',
    'taille' => 'Taille',
    'age' => 'Age',
    'sexe' => 'Sexe',
    'activite' => 'Activite',
    'activite_label' => 'Activite',
    'objectif_type' => 'Type',
    'repas_count' => 'Repas lies',
];
?>
<!-- BACKOFFICE_OBJECTIFS_OK -->
<div class="admin-page admin-module-page">
    <div class="admin-page-header">
        <span class="admin-page-kicker">Objectifs</span>
        <h1>Gestion des objectifs</h1>
        <p>Consultez les objectifs nutritionnels enregistres par les utilisateurs.</p>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="admin-alert admin-alert-success"><?= htmlspecialchars((string) $successMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="admin-alert admin-alert-error"><?= htmlspecialchars((string) $errorMessage) ?></div>
    <?php endif; ?>

    <section class="admin-card">
        <?php if (!empty($objectifsError)): ?>
            <div class="admin-empty-state">
                Service temporairement indisponible. Impossible de charger les objectifs pour le moment.
            </div>
        <?php elseif (!empty($objectifs)): ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <?php foreach ($objectifColumns as $column): ?>
                                <th><?= htmlspecialchars($objectifLabels[$column] ?? ucwords(str_replace('_', ' ', (string) $column))) ?></th>
                            <?php endforeach; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($objectifs as $objectif): ?>
                            <tr>
                                <?php foreach ($objectifColumns as $column): ?>
                                    <?php $value = $objectif[$column] ?? null; ?>
                                    <td>
                                        <?php if ($column === 'calories_cible'): ?>
                                            <?= number_format((float) $value, 0, '.', ' ') ?> kcal
                                        <?php elseif ($column === 'repas_count'): ?>
                                            <span class="admin-inline-badge admin-inline-badge-accent"><?= number_format((int) $value, 0, '.', ' ') ?></span>
                                        <?php elseif ($column === 'sexe'): ?>
                                            <span class="admin-inline-badge"><?= htmlspecialchars((string) ($sexeOptions[$value] ?? $value ?? '-')) ?></span>
                                        <?php elseif ($column === 'objectif_type'): ?>
                                            <span class="admin-inline-badge admin-inline-badge-accent"><?= htmlspecialchars((string) ($objectifTypeOptions[$value] ?? $value ?? '-')) ?></span>
                                        <?php elseif ($column === 'activite_label'): ?>
                                            <?= htmlspecialchars((string) ($value ?? '-')) ?>
                                        <?php elseif (is_scalar($value) || $value === null): ?>
                                            <?= htmlspecialchars((string) ($value ?? '-')) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '-') ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <div class="admin-actions admin-actions-inline">
                                        <a href="<?= $baseUrl ?>/index.php?action=admin-objectif-show&id=<?= urlencode((string) ($objectif['id'] ?? '')) ?>" class="admin-btn secondary admin-btn-sm">
                                            <i class="fa-solid fa-eye"></i>
                                            Details
                                        </a>
                                        <?php if ((int) ($objectif['repas_count'] ?? 0) === 0): ?>
                                            <a href="<?= $baseUrl ?>/index.php?action=admin-objectif-delete&id=<?= urlencode((string) ($objectif['id'] ?? '')) ?>" class="admin-btn danger admin-btn-sm" onclick="return confirm('Supprimer cet objectif ?');">
                                                <i class="fa-solid fa-trash"></i>
                                                Supprimer
                                            </a>
                                        <?php else: ?>
                                            <span class="admin-inline-badge admin-inline-badge-muted">Suppression bloquee</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-empty-state">Aucun objectif enregistre.</div>
        <?php endif; ?>
    </section>
</div>
