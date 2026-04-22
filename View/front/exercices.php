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
            <div style="margin-bottom: 1.5rem;">
                <label for="muscleFilter" style="color: var(--text-muted); margin-right: 1rem;">Filtrer par muscle :</label>
                <select id="muscleFilter" style="padding: 0.5rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border); max-width: 200px;">
                    <option value="">Tous les muscles</option>
                    <?php
                        $muscles = [];
                        foreach($exercices as $ex) {
                            if(!in_array($ex['muscle_principal'], $muscles)) {
                                $muscles[] = $ex['muscle_principal'];
                            }
                        }
                        foreach($muscles as $m) echo "<option value=\"".htmlspecialchars($m)."\">".htmlspecialchars($m)."</option>";
                    ?>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;" id="exercicesList">
                <?php foreach($exercices as $ex): ?>
                    <div class="glass-card exercice-item" data-muscle="<?= htmlspecialchars($ex['muscle_principal']) ?>" style="display: flex; justify-content: space-between; align-items: stretch; padding: 1rem 1.5rem;">
                        <div>
                            <h4 style="font-size: 1.2rem; margin-bottom: 0.5rem; color: #fff;">
                                <?= htmlspecialchars($ex['nom_exercice']) ?> 
                                <span style="font-size: 0.8rem; background: <?= $ex['niveau_difficulte'] == 'Avancé' ? 'var(--error)' : ($ex['niveau_difficulte'] == 'Intermédiaire' ? '#fbbf24' : '#34d399') ?>; color: #000; padding: 2px 6px; border-radius: 4px; margin-left: 8px;">
                                    <?= htmlspecialchars($ex['niveau_difficulte']) ?>
                                </span>
                            </h4>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">
                                <span style="background: rgba(56, 189, 248, 0.2); border: 1px solid var(--primary); padding: 2px 8px; border-radius: 12px; color: var(--primary);">
                                    💪 <?= htmlspecialchars($ex['muscle_principal']) ?>
                                </span>
                                <?php if(!empty($ex['muscle_secondaire'])): ?>
                                    <span style="background: rgba(148, 163, 184, 0.2); border: 1px solid var(--text-muted); padding: 2px 8px; border-radius: 12px; margin-left: 5px;">
                                        <?= htmlspecialchars($ex['muscle_secondaire']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div style="color: var(--primary); font-weight: 600;">
                                <?= $ex['series'] ?> séries × <?= $ex['repetitions'] ?> rép. | <span style="color: #fbbf24;">🔥 <?= $ex['calories_estimees'] ?> kcal</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <script>
                document.getElementById('muscleFilter').addEventListener('change', function() {
                    const selectedMuscle = this.value;
                    const items = document.querySelectorAll('.exercice-item');
                    items.forEach(item => {
                        if(selectedMuscle === "" || item.dataset.muscle === selectedMuscle) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            </script>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
