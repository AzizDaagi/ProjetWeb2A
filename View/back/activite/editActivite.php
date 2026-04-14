<?php ob_start(); ?>

<div class="animate-fade-in">
    <div style="margin-bottom: 2rem;">
        <a href="index.php?action=admin_index" style="color: var(--text-muted); text-decoration: none;">&larr; Retour au TdB Admin</a>
    </div>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'nom_vide'): ?>
        <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--error); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: #fca5a5;">
            PHP: Le nom de l'activité ne peut pas être vide !
        </div>
    <?php endif; ?>

    <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem; color: #f59e0b;">Modifier l'Activité</h3>
        <!-- FORMULAIRE SANS HTML5 VAL -->
        <form action="index.php?action=updateActivite" method="POST" class="js-validate-activite" novalidate>
            <input type="hidden" name="id_activite" value="<?= $activite['id_activite'] ?>">
            <div>
                <label>Nom de l'activité</label>
                <input type="text" name="nom_activite" value="<?= htmlspecialchars($activite['nom_activite']) ?>">
                <div class="error-message nom-error">Le nom est requis.</div>
            </div>
            <div>
                <label>Description</label>
                <textarea name="description" rows="5"><?= htmlspecialchars($activite['description']) ?></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label>Durée (minutes)</label>
                    <input type="text" name="duree_minutes" value="<?= $activite['duree_minutes'] ?>">
                    <div class="error-message duree-error">Doit être un nombre entier positif.</div>
                </div>
                <div>
                    <label>Calories estimées brûlées</label>
                    <input type="text" name="calories_brulees" value="<?= $activite['calories_brulees'] ?>">
                    <div class="error-message cal-error">Doit être un nombre entier positif.</div>
                </div>
            </div>
            <button type="submit" class="btn" style="width: 100%; margin-top: 1rem; background: linear-gradient(135deg, #f59e0b, #d97706);">Mettre à jour</button>
        </form>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../../layout.php'; 
?>
