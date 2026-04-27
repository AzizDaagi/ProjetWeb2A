<div class="admin-page">
    <div class="admin-page-head">
        <h1><i class="fa-solid fa-user-shield icon"></i> Backoffice</h1>
        <p class="subtitle">Copie de la template backoffice pour partage.</p>
    </div>

    <div class="admin-dashboard-layout">
        <section class="admin-widget admin-widget-wide">
            <div class="admin-widget-head">
                <h2>Evolution des utilisateurs</h2>
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="admin-line-chart">
                <svg viewBox="0 0 860 280" aria-hidden="true">
                    <polyline points="0,220 110,180 220,165 330,140 440,120 550,110 660,98 770,82 860,70" fill="none" stroke="#00a2ff" stroke-width="4" stroke-linecap="round"/>
                    <polyline points="0,225 110,205 220,198 330,184 440,170 550,154 660,148 770,132 860,126" fill="none" stroke="#ff5f45" stroke-width="4" stroke-linecap="round"/>
                </svg>
                <div class="chart-legend">
                    <span><i class="legend-dot legend-blue"></i> Inscriptions</span>
                    <span><i class="legend-dot legend-orange"></i> Profils completes</span>
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
                        <span><?= htmlspecialchars((string) $totalUsers) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-widget admin-kpi-widget">
            <div class="admin-kpi-grid">
                <article class="kpi-card">
                    <p>Total Users</p>
                    <strong><?= htmlspecialchars((string) $totalUsers) ?></strong>
                    <i class="fa-solid fa-users"></i>
                </article>
                <article class="kpi-card">
                    <p>Etat Systeme</p>
                    <strong>Online</strong>
                    <i class="fa-solid fa-signal"></i>
                </article>
                <article class="kpi-card">
                    <p>Modules</p>
                    <strong>4</strong>
                    <i class="fa-solid fa-cubes"></i>
                </article>
                <article class="kpi-card">
                    <p>Compte</p>
                    <strong>AD</strong>
                    <i class="fa-solid fa-circle-user"></i>
                </article>
            </div>
        </section>
    </div>

    <section class="admin-widget admin-recent-widget">
        <div class="admin-widget-head">
            <h2>Utilisateurs recents</h2>
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
                <?php foreach ($recentUsers as $userRow): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $userRow['prenom']) ?></td>
                        <td><?= htmlspecialchars((string) $userRow['nom']) ?></td>
                        <td><?= htmlspecialchars((string) $userRow['email']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
