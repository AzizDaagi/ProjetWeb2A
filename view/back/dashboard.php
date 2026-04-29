<div class="admin-page">
    <?php
    $pieSegments = (isset($pieSegments) && is_array($pieSegments)) ? $pieSegments : [
        ['label' => 'Hommes', 'count' => 0, 'dotClass' => 'legend-blue'],
        ['label' => 'Femmes', 'count' => 0, 'dotClass' => 'legend-orange'],
    ];
    $pieGradient = (isset($pieGradient) && is_string($pieGradient) && $pieGradient !== '') ? $pieGradient : 'conic-gradient(#95a5a6 0 100%)';
    $kpiCards = (isset($kpiCards) && is_array($kpiCards)) ? $kpiCards : [
        ['label' => 'Total utilisateurs', 'value' => (string) ((int) ($totalUsers ?? 0)), 'icon' => 'fa-solid fa-users'],
        ['label' => 'Taux profils completes', 'value' => '0%', 'icon' => 'fa-solid fa-circle-check'],
        ['label' => 'Profils a completer', 'value' => '0', 'icon' => 'fa-solid fa-user-pen'],
    ];
    ?>

    <div class="admin-page-head">
        <h1><i class="fa-solid fa-user-shield icon"></i> Dashboard</h1>
        <p class="subtitle">Vue d'ensemble de l'activite admin avec template style dashboard.</p>
    </div>

    <div class="admin-dashboard-layout">
        <section class="admin-widget admin-kpi-widget admin-kpi-primary">
            <div class="admin-kpi-grid">
                <?php foreach ($kpiCards as $card): ?>
                    <article class="kpi-card">
                        <p><?= htmlspecialchars((string) ($card['label'] ?? 'Indicateur')) ?></p>
                        <strong><?= htmlspecialchars((string) ($card['value'] ?? '0')) ?></strong>
                        <i class="<?= htmlspecialchars((string) ($card['icon'] ?? 'fa-solid fa-chart-line')) ?>"></i>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-widget admin-widget-wide admin-evolution-widget">
            <div class="admin-widget-head">
                <h2>Repartition par sexe</h2>
                <i class="fa-solid fa-ellipsis"></i>
            </div>
            <div class="admin-pie-widget">
                <div class="admin-pie-chart" style="--pie-gradient: <?= htmlspecialchars($pieGradient) ?>;">
                    <div class="admin-pie-core">
                        <strong>Total</strong>
                        <span><?= htmlspecialchars((string) ((int) ($totalUsers ?? 0))) ?></span>
                    </div>
                </div>
                <div class="chart-legend admin-pie-legend">
                    <?php foreach ($pieSegments as $segment): ?>
                        <span>
                            <i class="legend-dot <?= htmlspecialchars((string) ($segment['dotClass'] ?? 'legend-blue')) ?>"></i>
                            <?= htmlspecialchars((string) ($segment['label'] ?? 'Categorie')) ?>
                            (<?= htmlspecialchars((string) ((int) ($segment['count'] ?? 0))) ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    </div>

    <section class="admin-widget admin-recent-widget">
        <div class="admin-widget-head">
            <h2>Utilisateurs recents</h2>
            <a href="/smart_nutrition/index.php?action=users-list" class="btn-edit">Voir tout</a>
        </div>
        <table class="users-table">
            <thead>
                <tr>
                    <th>Prenom</th>
                    <th>Nom</th>
                    <th>E-mail</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentUsers)): ?>
                    <?php foreach ($recentUsers as $userRow): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $userRow['prenom']) ?></td>
                            <td><?= htmlspecialchars((string) $userRow['nom']) ?></td>
                            <td><?= htmlspecialchars((string) $userRow['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">Aucun utilisateur disponible</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>
