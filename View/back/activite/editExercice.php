<?php ob_start(); ?>

<div class="animate-fade-in">
    <div style="margin-bottom: 2rem;">
        <a href="index.php?action=admin_show&id=<?= $exercice['id_activite'] ?>" style="color: var(--text-muted); text-decoration: none;">&larr; Retour à l'Activité</a>
    </div>

    <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
        <h2 style="color: var(--primary); margin-bottom: 1.5rem;">Modifier l'Exercice</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--error); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: #fca5a5;">
                Veuillez remplir tous les champs obligatoires.
            </div>
        <?php endif; ?>

        <form action="index.php?action=updateExercice" method="POST">
            <input type="hidden" name="id_exercice" value="<?= $exercice['id_exercice'] ?>">
            <input type="hidden" name="id_activite" value="<?= $exercice['id_activite'] ?>">
            
            <div>
                <label>Nom de l'exercice</label>
                <input type="text" name="nom_exercice" value="<?= htmlspecialchars($exercice['nom_exercice']) ?>" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label>Séries</label>
                    <input type="number" name="series" min="1" value="<?= $exercice['series'] ?>" required>
                </div>
                <div>
                    <label>Répétitions</label>
                    <input type="number" name="repetitions" min="1" value="<?= $exercice['repetitions'] ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label>Muscle Principal</label>
                    <input type="text" name="muscle_principal" value="<?= htmlspecialchars($exercice['muscle_principal']) ?>" required>
                </div>
                <div>
                    <label>Muscle Secondaire</label>
                    <input type="text" name="muscle_secondaire" value="<?= htmlspecialchars($exercice['muscle_secondaire']) ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label>Difficulté</label>
                    <select name="niveau_difficulte" style="width: 100%; padding: 0.75rem; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--card-border); border-radius: 8px; color: var(--text-main);" required>
                        <option value="Débutant" <?= $exercice['niveau_difficulte'] == 'Débutant' ? 'selected' : '' ?>>Débutant</option>
                        <option value="Intermédiaire" <?= $exercice['niveau_difficulte'] == 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                        <option value="Avancé" <?= $exercice['niveau_difficulte'] == 'Avancé' ? 'selected' : '' ?>>Avancé</option>
                    </select>
                </div>
                <div>
                    <label>Calories estimées</label>
                    <input type="number" name="calories_estimees" min="1" value="<?= $exercice['calories_estimees'] ?>" required>
                </div>
            </div>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 1.5rem;">Enregistrer les modifications</button>
        </form>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../../layout.php'; 
?>
