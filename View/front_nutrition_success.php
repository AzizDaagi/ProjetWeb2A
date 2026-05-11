<?php ob_start(); ?>

<div class="animate-fade-in" style="max-width: 800px; margin: 0 auto; padding: 5rem 1rem; text-align: center;">
    <div class="glass-card" style="padding: 3rem;">
        <i class="fa-solid fa-circle-check" style="font-size: 4rem; color: #10b981; margin-bottom: 1.5rem;"></i>
        <h2 style="color: var(--primary); margin-bottom: 1rem;">Demande soumise avec succès !</h2>
        
        <?php 
        if (isset($_SESSION['last_suggestions'])): 
            $sug = $_SESSION['last_suggestions'];
            unset($_SESSION['last_suggestions']); // Clear after use
        ?>
            <div style="margin: 2rem 0; padding: 1.5rem; background: rgba(255,255,255,0.05); border-radius: 12px; text-align: left;">
                <h3 style="color: var(--secondary); margin-bottom: 1rem;"><i class="fa-solid fa-bolt"></i> Programme Généré Instantanément pour : <?= htmlspecialchars($sug['goal']) ?></h3>
                <p style="margin-bottom: 1rem; color: var(--text-muted);">Voici vos recommandations personnalisées calculées immédiatement :</p>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach($sug['activities'] as $act): ?>
                        <div style="padding: 1rem; background: rgba(0,0,0,0.2); border-left: 4px solid var(--primary); border-radius: 4px;">
                            <strong style="color: #fff;"><?= htmlspecialchars($act['nom_activite']) ?></strong>
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;"><?= htmlspecialchars($act['description']) ?></p>
                            <span style="font-size: 0.8rem; color: var(--primary);"><?= $act['calories_brulees'] ?> kcal / <?= $act['duree_minutes'] ?> min</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if(!empty($sug['api_exercises'])): ?>
                    <h3 style="color: var(--accent); margin: 2rem 0 1rem;"><i class="fa-solid fa-dumbbell"></i> Exercices Recommandés (IA)</h3>
                    <div style="display: grid; gap: 1.5rem;">
                        <?php foreach($sug['api_exercises'] as $ex): ?>
                            <div style="padding: 1.2rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <strong style="color: #fff; font-size: 1.1rem;"><?= htmlspecialchars($ex['name']) ?></strong>
                                    <span style="font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; background: rgba(243, 156, 18, 0.2); color: var(--accent); text-transform: uppercase;">
                                        <?= htmlspecialchars($ex['difficulty']) ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 0.8rem;">
                                    <span style="font-size: 0.85rem; color: var(--secondary);"><i class="fa-solid fa-mound"></i> Muscle: <?= htmlspecialchars($ex['muscle']) ?></span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; white-space: pre-line;">
                                    <?= htmlspecialchars($ex['instructions']) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p style="color: var(--text-main); font-size: 1.1rem; margin-bottom: 2rem;">
                Votre programme a été généré avec succès ! Vous pouvez maintenant explorer vos recommandations personnalisées.
            </p>
        <?php endif; ?>

        <a href="index.php?action=home" class="btn" style="display: inline-block; padding: 0.75rem 2rem; font-size: 1.1rem;">Retour à l'accueil</a>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/layout.php'; 
?>
