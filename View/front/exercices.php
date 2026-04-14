<?php ob_start(); ?>

<div class="animate-fade-in">
    <div style="margin-bottom: 2rem;">
        <a href="index.php?action=activites" style="color: var(--text-muted); text-decoration: none;">&larr; Retour au catalogue public</a>
    </div>

    <div class="glass-card" style="margin-bottom: 3rem; background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));">
        <h2 style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($activite['nom_activite']) ?></h2>
        <p style="color: var(--text-main); margin-bottom: 1.5rem; line-height: 1.6;"><?= nl2br(htmlspecialchars($activite['description'])) ?></p>
        
        <div style="display: flex; gap: 1.5rem;">
            <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px; flex: 1; text-align: center;">
                <h4 style="color: var(--text-muted); margin-bottom: 0.5rem;">Durée Totale</h4>
                <div style="font-size: 1.5rem; font-weight: bold;">⏱ <?= $activite['duree_minutes'] ?> min</div>
            </div>
            <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px; flex: 1; text-align: center;">
                <h4 style="color: var(--text-muted); margin-bottom: 0.5rem;">Calories Brûlées</h4>
                <div style="font-size: 1.5rem; font-weight: bold; color: var(--accent);">🔥 <?= $activite['calories_brulees'] ?> kcal</div>
            </div>
        </div>
    </div>

    <div>
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem;">Détail des Exercices</h3>
        
        <?php if(empty($exercices)): ?>
            <p style="color: var(--text-muted); font-style: italic;">Aucun exercice renseigné par l'administrateur.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach($exercices as $ex): ?>
                    <div class="glass-card" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem;">
                        <div>
                            <h4 style="font-size: 1.1rem; margin-bottom: 0.25rem;"><?= htmlspecialchars($ex['nom_exercice']) ?></h4>
                            <div style="color: var(--primary); font-weight: 600;">
                                <?= $ex['series'] ?> séries × <?= $ex['repetitions'] ?> répétitions
                            </div>
                        </div>
                        <div style="background: rgba(56, 189, 248, 0.1); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                            💪
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
