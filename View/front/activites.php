<?php ob_start(); ?>

<div class="animate-fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Catalogue des Activités Sportives</h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <?php if(empty($activites)): ?>
            <p style="color: var(--text-muted);">Aucune activité disponible pour le moment.</p>
        <?php else: ?>
            <?php foreach($activites as $act): ?>
                <div class="glass-card">
                    <h3 style="color: var(--primary); margin-bottom: 0.5rem;"><?= htmlspecialchars($act['nom_activite']) ?></h3>
                    <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.95rem;"><?= htmlspecialchars($act['description']) ?></p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
                        <span style="background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px;">⏱ <?= $act['duree_minutes'] ?> min</span>
                        <span style="background: rgba(244, 63, 94, 0.2); color: var(--accent); padding: 4px 8px; border-radius: 4px;">🔥 <?= $act['calories_brulees'] ?> kcal</span>
                    </div>
                    <!-- READ ONLY MODE -->
                    <a href="index.php?action=showExercices&id=<?= $act['id_activite'] ?>" class="btn btn-outline" style="width: 100%; text-align: center;">Consulter les Exercices</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
