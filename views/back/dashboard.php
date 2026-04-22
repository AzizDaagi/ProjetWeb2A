<?php
$recentUsers = $recentUsers ?? [];
$evolutionLabels = $evolutionLabels ?? [];
$totalUsers = (int) ($totalUsers ?? 0);
$totalRepas = (int) ($totalRepas ?? 0);
$totalAliments = (int) ($totalAliments ?? 0);
$totalCalories = (float) ($totalCalories ?? 0);
$caloriesTrendPoints = $caloriesTrendPoints ?? '0,220 860,220';
$repasTrendPoints = $repasTrendPoints ?? '0,220 860,220';
?>
<div class="admin-page">
    <div class="admin-page-head">
        <h1><i class="fa-solid fa-user-shield icon"></i> Backoffice</h1>
        <p class="subtitle">Vue d'ensemble du projet Smart Nutrition et de ses donnees principales.</p>
    </div>

    <div class="admin-dashboard-layout">
        <section class="admin-widget admin-widget-wide">
            <div class="admin-widget-head">
                <h2>Evolution nutritionnelle</h2>
                <i class="fa-solid fa-chart-line"></i>
            </div>

            <div class="admin-line-chart">
                <svg viewBox="0 0 860 280" aria-hidden="true">
                    <polyline points="<?= htmlspecialchars($caloriesTrendPoints) ?>" fill="none" stroke="#00a2ff" stroke-width="4" stroke-linecap="round" />
                    <polyline points="<?= htmlspecialchars($repasTrendPoints) ?>" fill="none" stroke="#ff5f45" stroke-width="4" stroke-linecap="round" />
                </svg>

                <div class="chart-legend">
                    <span><i class="legend-dot legend-blue"></i> Calories</span>
                    <span><i class="legend-dot legend-orange"></i> Repas</span>
                </div>

                <div class="chart-axis-labels">
                    <?php foreach ($evolutionLabels as $label): ?>
                        <span><?= htmlspecialchars((string) $label) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="admin-widget">
            <div class="admin-widget-head">
                <h2>Utilisateurs</h2>
                <i class="fa-solid fa-chart-pie"></i>
            </div>

            <div class="admin-donut-wrap">
                <div class="admin-donut">
                    <div class="admin-donut-core">
                        <strong>Total</strong>
                        <span><?= number_format($totalUsers, 0, '.', ' ') ?></span>
                    </div>
                </div>
            </div>

            <div class="admin-donut-meta">
                <span><i class="legend-dot legend-green"></i> Comptes suivis</span>
                <span><i class="legend-dot legend-blue"></i> Donnees dynamiques</span>
            </div>
        </section>

        <section class="admin-widget admin-kpi-widget">
            <div class="admin-kpi-grid">
                <article class="kpi-card">
                    <p>Total Users</p>
                    <strong><?= number_format($totalUsers, 0, '.', ' ') ?></strong>
                    <i class="fa-solid fa-users"></i>
                </article>

                <article class="kpi-card">
                    <p>Total Repas</p>
                    <strong><?= number_format($totalRepas, 0, '.', ' ') ?></strong>
                    <i class="fa-solid fa-utensils"></i>
                </article>

                <article class="kpi-card">
                    <p>Total Calories</p>
                    <strong><?= number_format((int) round($totalCalories), 0, '.', ' ') ?></strong>
                    <i class="fa-solid fa-fire"></i>
                </article>

                <article class="kpi-card">
                    <p>Total Aliments</p>
                    <strong><?= number_format($totalAliments, 0, '.', ' ') ?></strong>
                    <i class="fa-solid fa-seedling"></i>
                </article>
            </div>
        </section>
    </div>

    <section class="admin-widget admin-recent-widget">
        <div class="admin-widget-head">
            <h2>Utilisateurs recents</h2>
            <a href="index.php?controller=backoffice&action=users" class="admin-link-inline">Voir tout</a>
        </div>

        <table class="users-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Age</th>
                    <th>Poids</th>
                    <th>Objectif</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentUsers)): ?>
                    <?php foreach ($recentUsers as $userRow): ?>
                        <tr>
                            <td><?= htmlspecialchars($userRow['nom'] !== '' ? $userRow['nom'] : 'Utilisateur #' . $userRow['id']) ?></td>
                            <td><?= $userRow['age'] !== null ? htmlspecialchars((string) $userRow['age']) . ' ans' : '-' ?></td>
                            <td><?= $userRow['poids'] !== null ? htmlspecialchars(number_format((float) $userRow['poids'], 1, '.', ' ')) . ' kg' : '-' ?></td>
                            <td><?= $userRow['objectif_calories'] !== null ? htmlspecialchars(number_format((float) $userRow['objectif_calories'], 0, '.', ' ')) . ' kcal' : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="admin-empty-cell">Aucun utilisateur disponible pour le moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>
