<?php ob_start(); ?>

<div class="animate-fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <a href="index.php?action=admin_index" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Retour au TdB Admin</a>
        <a href="index.php?action=admin_dashboard" class="btn btn-outline" style="padding: 0.5rem 1rem;">Dashboard</a>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--error); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: #fca5a5;">
            <?= $_GET['error'] == 'fields' ? "Tous les champs d'exercice sont obligatoires !" : "Erreur." ?>
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
                <h4 style="color: var(--text-muted); margin-bottom: 0.5rem;">Calories Brûlées</h4>
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
                <div style="margin-bottom: 1rem;">
                    <select id="adminMuscleFilter" style="padding: 0.5rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border); max-width: 200px;">
                        <option value="">Filtrer par muscle...</option>
                        <?php
                            $adminMuscles = [];
                            foreach($exercices as $ex) {
                                if(!in_array($ex['muscle_principal'], $adminMuscles)) {
                                    $adminMuscles[] = $ex['muscle_principal'];
                                }
                            }
                            foreach($adminMuscles as $m) echo "<option value=\"".htmlspecialchars($m)."\">".htmlspecialchars($m)."</option>";
                        ?>
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;" id="adminExercicesList">
                    <?php foreach($exercices as $ex): ?>
                        <div class="glass-card admin-exercice-item" data-muscle="<?= htmlspecialchars($ex['muscle_principal']) ?>" style="display: flex; justify-content: space-between; align-items: stretch; padding: 1rem 1.5rem;">
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
                                <div style="color: var(--text-main); font-weight: 600;">
                                    <?= $ex['series'] ?> séries × <?= $ex['repetitions'] ?> rép. | <span style="color: #fbbf24;">🔥 <?= $ex['calories_estimees'] ?> kcal</span>
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; justify-content: center;">
                                <a href="index.php?action=editExercice&id=<?= $ex['id_exercice'] ?>" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.9rem;"><i class="fa-solid fa-pen"></i></a>
                                <a href="index.php?action=deleteExercice&id=<?= $ex['id_exercice'] ?>" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.9rem; background: var(--error);" onclick="return confirm('Supprimer cet exercice ?');"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <script>
                    document.getElementById('adminMuscleFilter').addEventListener('change', function() {
                        const selectedMuscle = this.value;
                        const items = document.querySelectorAll('.admin-exercice-item');
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

        <div style="flex: 0 1 400px; min-width: 300px;">
            <div class="glass-card" style="position: sticky; top: 2rem;">
                <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem;">Ajouter un Exercice</h3>
                
                <form action="index.php?action=addExercice" method="POST" id="addExerciceForm">
                    <input type="hidden" name="id_activite" value="<?= $activite['id_activite'] ?>">
                    
                    <div>
                        <label>Nom de l'exercice</label>
                        <input type="text" name="nom_exercice" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>Séries</label>
                            <input type="number" name="series" min="1" value="3" required>
                        </div>
                        <div>
                            <label>Répétitions</label>
                            <input type="number" name="repetitions" min="1" value="10" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>Muscle Principal</label>
                            <input type="text" name="muscle_principal" required>
                        </div>
                        <div>
                            <label>Muscle Secondaire</label>
                            <input type="text" name="muscle_secondaire">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>Difficulté</label>
                            <select name="niveau_difficulte" style="width: 100%; padding: 0.75rem; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--card-border); border-radius: 8px; color: var(--text-main);" required>
                                <option value="Débutant">Débutant</option>
                                <option value="Intermédiaire">Intermédiaire</option>
                                <option value="Avancé">Avancé</option>
                            </select>
                        </div>
                        <div>
                            <label>Calories estimées</label>
                            <input type="number" name="calories_estimees" min="1" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn" style="width: 100%; margin-top: 1rem;">Ajouter l'exercice</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Simple Validation
document.getElementById('addExerciceForm').addEventListener('submit', function(e) {
    let requiredFields = this.querySelectorAll('input[required], select[required]');
    let valid = true;
    requiredFields.forEach(function(el) {
        if (!el.value.trim()) {
            el.style.borderColor = 'var(--error)';
            valid = false;
        } else {
            el.style.borderColor = 'var(--card-border)';
        }
    });
    if (!valid) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires.');
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../../layout.php'; 
?>
