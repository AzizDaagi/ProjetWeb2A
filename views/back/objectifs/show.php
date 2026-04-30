<?php
$objectif = $objectif ?? [];
$objectifSummary = $objectifSummary ?? [];
$repasCount = (int) ($repasCount ?? 0);
$sexeLabel = $sexeLabel ?? '-';
$activiteLabel = $activiteLabel ?? '-';
$objectifTypeLabel = $objectifTypeLabel ?? '-';
$hasComputedSummary = !empty($objectifSummary);
?>
<div class="admin-page">
    <div class="admin-page-head admin-page-head-inline">
        <div>
            <h1><i class="fa-solid fa-crosshairs icon"></i> Detail objectif</h1>
            <p class="subtitle">Informations completes sur un objectif calorique et son profil physique associe.</p>
        </div>

        <div class="admin-action-group">
            <a href="index.php?controller=backoffice&action=objectifs" class="admin-btn admin-btn-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Retour
            </a>

            <?php if ($repasCount === 0): ?>
                <a href="index.php?controller=backoffice&action=objectifDelete&id=<?= urlencode((string) ($objectif['id'] ?? '')) ?>" class="admin-btn admin-btn-danger" onclick="return confirm('Supprimer cet objectif ?');">
                    <i class="fa-solid fa-trash"></i>
                    Supprimer
                </a>
            <?php endif; ?>
        </div>
    </div>

    <section class="admin-widget">
        <div class="admin-kpi-grid">
            <article class="kpi-card">
                <p>Calories cible</p>
                <strong><?= number_format((float) ($objectif['calories_cible'] ?? 0), 0, '.', ' ') ?></strong>
                <i class="fa-solid fa-fire"></i>
            </article>

            <article class="kpi-card">
                <p>BMR</p>
                <strong><?= $hasComputedSummary ? number_format((float) ($objectifSummary['bmr'] ?? 0), 0, '.', ' ') : '-' ?></strong>
                <i class="fa-solid fa-heart-pulse"></i>
            </article>

            <article class="kpi-card">
                <p>TDEE</p>
                <strong><?= $hasComputedSummary ? number_format((float) ($objectifSummary['tdee'] ?? 0), 0, '.', ' ') : '-' ?></strong>
                <i class="fa-solid fa-bolt"></i>
            </article>

            <article class="kpi-card">
                <p>Repas lies</p>
                <strong><?= number_format($repasCount, 0, '.', ' ') ?></strong>
                <i class="fa-solid fa-utensils"></i>
            </article>
        </div>
    </section>

    <section class="admin-widget">
        <table class="admin-table">
            <tbody>
                <tr>
                    <th>ID</th>
                    <td><?= htmlspecialchars((string) ($objectif['id'] ?? '-')) ?></td>
                </tr>
                <tr>
                    <th>Date creation</th>
                    <td><?= htmlspecialchars((string) ($objectif['date_creation'] ?? '-')) ?></td>
                </tr>
                <tr>
                    <th>Poids</th>
                    <td><?= number_format((float) ($objectif['poids'] ?? 0), 1, '.', ' ') ?> kg</td>
                </tr>
                <tr>
                    <th>Taille</th>
                    <td><?= number_format((float) ($objectif['taille'] ?? 0), 0, '.', ' ') ?> cm</td>
                </tr>
                <tr>
                    <th>Age</th>
                    <td><?= htmlspecialchars((string) ($objectif['age'] ?? '-')) ?> ans</td>
                </tr>
                <tr>
                    <th>Sexe</th>
                    <td><?= htmlspecialchars((string) $sexeLabel) ?></td>
                </tr>
                <tr>
                    <th>Activite</th>
                    <td><?= htmlspecialchars((string) $activiteLabel) ?><?php if (isset($objectifSummary['activity_factor'])): ?> (x<?= htmlspecialchars((string) $objectifSummary['activity_factor']) ?>)<?php endif; ?></td>
                </tr>
                <tr>
                    <th>Type d'objectif</th>
                    <td><?= htmlspecialchars((string) $objectifTypeLabel) ?></td>
                </tr>
                <tr>
                    <th>Proteines cible</th>
                    <td><?= number_format((float) ($objectif['proteines'] ?? 0), 0, '.', ' ') ?> g</td>
                </tr>
                <tr>
                    <th>Glucides cible</th>
                    <td><?= number_format((float) ($objectif['glucides'] ?? 0), 0, '.', ' ') ?> g</td>
                </tr>
                <tr>
                    <th>Lipides cible</th>
                    <td><?= number_format((float) ($objectif['lipides'] ?? 0), 0, '.', ' ') ?> g</td>
                </tr>
            </tbody>
        </table>
    </section>

    <?php if ($repasCount > 0): ?>
        <div class="admin-alert admin-alert-error">
            Cet objectif est actuellement lie a <?= number_format($repasCount, 0, '.', ' ') ?> repas. Sa suppression est bloque pour preserver l'integrite des relations avec <code>repas_consomme</code>.
        </div>
    <?php endif; ?>

    <?php if (!$hasComputedSummary): ?>
        <div class="admin-alert admin-alert-error">
            Cet objectif a ete cree avant l'ajout du profil physique complet. Mets-le a jour depuis le front pour recalculer automatiquement BMR, TDEE et calories cible.
        </div>
    <?php endif; ?>
</div>
