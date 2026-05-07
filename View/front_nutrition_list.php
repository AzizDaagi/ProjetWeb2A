<?php ob_start(); ?>

<div class="animate-fade-in" style="max-width: 900px; margin: 0 auto; padding: 2rem;">
    <div class="glass-card" style="padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <h2 style="color: var(--primary); margin: 0;"><i class="fa-solid fa-list-check"></i> Mes Demandes Nutritionnelles</h2>
            
            <form action="index.php" method="GET" style="display: flex; gap: 0.5rem; flex-grow: 1; max-width: 600px;">
                <input type="hidden" name="action" value="my_nutrition_requests">
                <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                       style="flex-grow: 1; padding: 0.5rem 1rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: #fff;">
                
                <select name="sort" onchange="this.form.submit()" 
                        style="padding: 0.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: #fff;">
                    <option value="date_desc" <?= ($_GET['sort'] ?? '') == 'date_desc' ? 'selected' : '' ?>>Plus récents</option>
                    <option value="date_asc" <?= ($_GET['sort'] ?? '') == 'date_asc' ? 'selected' : '' ?>>Plus anciens</option>
                    <option value="status" <?= ($_GET['sort'] ?? '') == 'status' ? 'selected' : '' ?>>Par statut</option>
                    <option value="user_asc" <?= ($_GET['sort'] ?? '') == 'user_asc' ? 'selected' : '' ?>>Nom (A-Z)</option>
                </select>
                
                <button type="submit" class="btn" style="padding: 0.5rem 1.2rem; border-radius: 20px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>

            <a href="index.php?action=export_nutrition_pdf&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? 'date_desc') ?>" 
               class="btn" style="background: var(--secondary); padding: 0.5rem 1.2rem; border-radius: 20px;">
                <i class="fa-solid fa-file-pdf"></i> Exporter PDF
            </a>
        </div>

    <?php if(!empty($requests)): ?>
        <div style="display: grid; gap: 1.5rem;">
            <?php foreach($requests as $req): ?>
                <div class="glass-card" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem;">
                    <div>
                        <h4 style="color: var(--primary); margin-bottom: 0.5rem;"><?= htmlspecialchars($req['current_goal']) ?> (<?= $req['current_weight'] ?> kg)</h4>
                        <p style="color: var(--text-muted); font-size: 0.9rem;"><?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></p>
                        <p style="color: var(--text-main); margin-top: 5px;"><?= htmlspecialchars($req['message'] ?: 'Pas de message') ?></p>
                        <div style="margin-top: 10px;">
                            <span style="font-size: 0.85rem; padding: 2px 8px; border-radius: 12px; background: <?= $req['status'] == 'completed' ? '#10b981' : '#f59e0b' ?>; color: #000;">
                                <?= ucfirst($req['status']) ?>
                            </span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <a href="index.php?action=edit_nutrition_request&id=<?= $req['id'] ?>" class="btn btn-outline" style="border-color: #f59e0b; color: #f59e0b;">Modifier</a>
                        <a href="index.php?action=delete_nutrition_request&id=<?= $req['id'] ?>" class="btn" style="background: var(--error);" onclick="return true;">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif(isset($_GET['email'])): ?>
        <p style="text-align: center; color: var(--text-muted);">Aucune demande trouvée pour cet email.</p>
    <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 3rem;">
        <a href="index.php?action=nutrition_request" class="btn btn-outline">&larr; Nouvelle Demande</a>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/layout.php'; 
?>
