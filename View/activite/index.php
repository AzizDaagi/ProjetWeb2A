<?php ob_start(); ?>

<div class="animate-fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Mes Activités</h2>
        <a href="#add-form" class="btn">Nouvelle Activité +</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <?php if(empty($activites)): ?>
            <p style="color: var(--text-muted);">Aucune activité enregistrée. Commencez par en ajouter une !</p>
        <?php else: ?>
            <?php foreach($activites as $act): ?>
                <div class="glass-card">
                    <h3 style="color: var(--primary); margin-bottom: 0.5rem;"><?= htmlspecialchars($act['nom_activite']) ?></h3>
                    <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.95rem;"><?= htmlspecialchars($act['description']) ?></p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
                        <span style="background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px;">⏱ <?= $act['duree_minutes'] ?> min</span>
                        <span style="background: rgba(244, 63, 94, 0.2); color: var(--accent); padding: 4px 8px; border-radius: 4px;">🔥 <?= $act['calories_brulees'] ?> kcal</span>
                    </div>
                    <a href="index.php?action=show&id=<?= $act['id_activite'] ?>" class="btn btn-outline" style="width: 100%; text-align: center; margin-bottom: 0.5rem;">Voir Détails & Exercices</a>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="index.php?action=editActivite&id=<?= $act['id_activite'] ?>" class="btn btn-outline" style="flex: 1; text-align: center; padding: 0.5rem; font-size: 0.85rem; border-color: #f59e0b; color: #f59e0b;">✎ Modifier</a>
                        <a href="index.php?action=deleteActivite&id=<?= $act['id_activite'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette activité et tous ses exercices ?');" class="btn btn-outline" style="flex: 1; text-align: center; padding: 0.5rem; font-size: 0.85rem; border-color: var(--accent); color: var(--accent);">🗑 Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="glass-card" id="add-form" style="max-width: 600px; margin: 0 auto;">
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem;">Ajouter une Activité</h3>
        <form action="index.php?action=createActivite" method="POST">
            <div>
                <label>Nom de l'activité</label>
                <input type="text" name="nom_activite" required placeholder="ex: Séance Musculation Haut du Corps">
            </div>
            <div>
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Détails de la séance..."></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label>Durée (minutes)</label>
                    <input type="number" name="duree_minutes" required min="1">
                </div>
                <div>
                    <label>Calories estimées brûlées</label>
                    <input type="number" name="calories_brulees" required min="0">
                </div>
            </div>
            <button type="submit" class="btn" style="width: 100%; margin-top: 1rem;">Enregistrer l'activité</button>
        </form>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
