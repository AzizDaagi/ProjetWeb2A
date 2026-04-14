<?php ob_start(); ?>

<div class="animate-fade-in">
    <div style="margin-bottom: 2rem;">
        <a href="index.php?action=admin_index" style="color: var(--text-muted); text-decoration: none;">&larr; Retour au TdB Admin</a>
    </div>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'fields'): ?>
        <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--error); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: #fca5a5;">
            PHP: Tous les champs d'exercice sont obligatoires !
        </div>
    <?php endif; ?>

    <div class="glass-card" style="margin-bottom: 3rem; background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));">
        <h2 style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;">[Admin] <?= htmlspecialchars($activite['nom_activite']) ?></h2>
        <p style="color: var(--text-main); margin-bottom: 1.5rem; line-height: 1.6;"><?= nl2br(htmlspecialchars($activite['description'])) ?></p>
        
        <div style="display: flex; gap: 1.5rem;">
            <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px; flex: 1; text-align: center;">
                <h4 style="color: var(--text-muted); margin-bottom: 0.5rem;">Durée Totale</h4>
                <div style="font-size: 1.5rem; font-weight: bold;">⏱ <?= $activite['duree_minutes'] ?> min</div>
            </div>
            <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px; flex: 1; text-align: center;">
                <h4 style="color: var(--text-muted); margin-bottom: 0.5rem;">Calories</h4>
                <div style="font-size: 1.5rem; font-weight: bold; color: var(--accent);">🔥 <?= $activite['calories_brulees'] ?> kcal</div>
            </div>
        </div>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
        <div style="flex: 1; min-width: 300px;">
            <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem;">Exercices de la séance</h3>
            <?php if(empty($exercices)): ?>
                <p style="color: var(--text-muted); font-style: italic;">Aucun exercice ajouté pour le moment.</p>
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

        <div style="flex: 0 1 400px; min-width: 300px;">
            <div class="glass-card" style="position: sticky; top: 2rem;">
                <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem;">Ajouter un Exercice</h3>
                
                <!-- SANS HTML5 VAL -->
                <form action="index.php?action=addExercice" method="POST" class="js-validate-exercice" novalidate>
                    <input type="hidden" name="id_activite" value="<?= $activite['id_activite'] ?>">
                    
                    <div>
                        <label>Nom de l'exercice</label>
                        <input type="text" name="nom_exercice">
                        <div class="error-message nom-error">Le nom de l'exercice est requis.</div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>Nombre de Séries</label>
                            <input type="text" name="series" value="3">
                            <div class="error-message num-error">Invalide.</div>
                        </div>
                        <div>
                            <label>Répétitions par série</label>
                            <input type="text" name="repetitions" value="10">
                            <div class="error-message num-error2">Invalide.</div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn" style="width: 100%; margin-top: 1rem;">Ajouter l'exercice</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../../layout.php'; 
?>
