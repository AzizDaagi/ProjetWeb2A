<?php ob_start(); ?>

<div class="animate-fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="color: var(--primary);">Gestion des Requêtes Nutrition & Programmes</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Examinez les demandes de vos utilisateurs et assignez-leur le programme final.</p>
        </div>
        <a href="index.php?action=admin_dashboard" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Retour Dashboard Principal</a>
    </div>

    <!-- Table des Requêtes -->
    <div class="glass-card" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--card-border); background: rgba(0,0,0,0.2);">
                        <th style="padding: 1rem; color: var(--text-muted);">ID</th>
                        <th style="padding: 1rem; color: var(--text-muted);">Utilisateur</th>
                        <th style="padding: 1rem; color: var(--text-muted);">Email</th>
                        <th style="padding: 1rem; color: var(--text-muted);">Poids / Objectif</th>
                        <th style="padding: 1rem; color: var(--text-muted);">Exercices Choisis</th>
                        <th style="padding: 1rem; color: var(--text-muted);">Statut</th>
                        <th style="padding: 1rem; color: var(--text-muted); text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($requests)): ?>
                        <tr><td colspan="7" style="padding: 2rem; text-align: center; color: var(--text-muted);">Aucune requête trouvée.</td></tr>
                    <?php else: ?>
                        <?php foreach($requests as $req): ?>
                            <tr style="border-bottom: 1px solid var(--card-border); transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.02)';" onmouseout="this.style.background='transparent';">
                                <td style="padding: 1rem; color: #fff;">#<?= $req['id'] ?></td>
                                <td style="padding: 1rem; font-weight: bold; color: var(--primary);"><?= htmlspecialchars($req['user_name']) ?></td>
                                <td style="padding: 1rem; color: var(--text-main);"><?= htmlspecialchars($req['email']) ?></td>
                                <td style="padding: 1rem; color: var(--text-main);">
                                    <span style="color: #38bdf8; font-weight: bold;"><?= $req['current_weight'] ?> kg</span><br>
                                    <span style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($req['current_goal']) ?></span>
                                </td>
                                <td style="padding: 1rem; color: var(--text-main); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($req['selected_exercises']) ?>">
                                    <?= htmlspecialchars($req['selected_exercises']) ?: '<span style="color:var(--text-muted);font-style:italic;">Aucun</span>' ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php 
                                        $bg = ''; $color = '';
                                        switch($req['status']) {
                                            case 'pending': $bg = 'rgba(251, 191, 36, 0.2)'; $color = '#fbbf24'; break;
                                            case 'approved': $bg = 'rgba(52, 211, 153, 0.2)'; $color = '#34d399'; break;
                                            case 'rejected': $bg = 'rgba(244, 63, 94, 0.2)'; $color = '#f43f5e'; break;
                                        }
                                    ?>
                                    <span style="background: <?= $bg ?>; color: <?= $color ?>; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                        <?= ucfirst(htmlspecialchars($req['status'])) ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <a href="index.php?action=admin_edit_request&id=<?= $req['id'] ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.9rem; border-color: var(--accent); color: var(--accent);">Gérer</a>
                                    <a href="index.php?action=admin_delete_request&id=<?= $req['id'] ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.9rem; border-color: var(--error); color: var(--error);" onclick="return confirm('Supprimer définitivement cette requête ?');"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../../../View/layout.php'; 
?>
