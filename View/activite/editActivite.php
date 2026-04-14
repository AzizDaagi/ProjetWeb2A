<?php ob_start(); ?>

<div class="animate-fade-in">
    <div style="margin-bottom: 2rem;">
        <a href="index.php" style="color: var(--text-muted); text-decoration: none;">&larr; Retour aux activités</a>
    </div>

    <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem; color: #f59e0b;">Modifier l'Activité</h3>
        <form action="index.php?action=updateActivite" method="POST">
            <input type="hidden" name="id_activite" value="<?= $activite['id_activite'] ?>">
            <div>
                <label>Nom de l'activité</label>
                <input type="text" name="nom_activite" required value="<?= htmlspecialchars($activite['nom_activite']) ?>">
            </div>
            <div>
                <label>Description</label>
                <textarea name="description" rows="5"><?= htmlspecialchars($activite['description']) ?></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label>Durée (minutes)</label>
                    <input type="number" name="duree_minutes" required min="1" value="<?= $activite['duree_minutes'] ?>">
                </div>
                <div>
                    <label>Calories estimées brûlées</label>
                    <input type="number" name="calories_brulees" required min="0" value="<?= $activite['calories_brulees'] ?>">
                </div>
            </div>
            <button type="submit" class="btn" style="width: 100%; margin-top: 1rem; background: linear-gradient(135deg, #f59e0b, #d97706);">Mettre à jour</button>
        </form>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
