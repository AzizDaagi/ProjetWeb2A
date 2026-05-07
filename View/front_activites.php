<?php ob_start(); ?>

<div class="animate-fade-in" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <div class="glass-card" style="padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <h2 style="color: var(--primary); margin: 0;"><i class="fa-solid fa-person-running"></i> Catalogue des Activités</h2>
            
            <form action="index.php" method="GET" style="display: flex; gap: 0.5rem; flex-grow: 1; max-width: 600px;">
                <input type="hidden" name="action" value="activites">
                <input type="text" name="search" placeholder="Rechercher par nom..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                       style="flex-grow: 1; padding: 0.5rem 1rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: #fff;">
                
                <select name="sort" onchange="this.form.submit()" 
                        style="padding: 0.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: #fff;">
                    <option value="name_asc" <?= ($_GET['sort'] ?? '') == 'name_asc' ? 'selected' : '' ?>>Nom (A-Z)</option>
                    <option value="name_desc" <?= ($_GET['sort'] ?? '') == 'name_desc' ? 'selected' : '' ?>>Nom (Z-A)</option>
                    <option value="calories_desc" <?= ($_GET['sort'] ?? '') == 'calories_desc' ? 'selected' : '' ?>>Plus de calories</option>
                    <option value="duration_desc" <?= ($_GET['sort'] ?? '') == 'duration_desc' ? 'selected' : '' ?>>Plus longue durée</option>
                </select>
                
                <button type="submit" class="btn" style="padding: 0.5rem 1.2rem; border-radius: 20px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>

            <a href="index.php?action=export_activite_pdf&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? 'name_asc') ?>" 
               class="btn" style="background: var(--secondary); padding: 0.5rem 1.2rem; border-radius: 20px;">
                <i class="fa-solid fa-file-pdf"></i> Exporter PDF
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">
        <?php if(empty($activites)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                <p style="color: var(--text-muted);">Aucune activité trouvée pour votre recherche.</p>
            </div>
        <?php else: ?>
            <?php foreach($activites as $act): ?>
                <div class="glass-card" style="padding: 1.5rem; display: flex; flex-direction: column;">
                    <h3 style="color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($act['nom_activite']) ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; flex-grow: 1;">
                        <?= htmlspecialchars($act['description']) ?>
                    </p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
                        <span style="background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px;"><i class="fa-regular fa-clock"></i> <?= $act['duree_minutes'] ?> min</span>
                        <span style="background: rgba(244, 63, 94, 0.2); color: var(--accent); padding: 4px 8px; border-radius: 4px;"><i class="fa-solid fa-fire"></i> <?= $act['calories_brulees'] ?> kcal</span>
                    </div>
                    <a href="index.php?action=showExercices&id=<?= $act['id_activite'] ?>" class="btn btn-outline" style="width: 100%; text-align: center;">Consulter les Exercices</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/layout.php'; 
?>
