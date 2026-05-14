<?php ob_start(); ?>

<div class="animate-fade-in" style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="color: var(--primary);">Gérer la Requête #<?= $requestData['id'] ?></h2>
        <a href="index.php?action=admin_requests" class="btn btn-outline">Retour à la liste</a>
    </div>

    <!-- User Information Snapshot -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <div class="glass-card" style="padding: 1.5rem;">
            <h4 style="color: var(--text-muted); margin-bottom: 1rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem;">Informations Utilisateur</h4>
            <p style="margin-bottom: 0.5rem;"><strong>Nom :</strong> <span style="color: #fff;"><?= htmlspecialchars($requestData['user_name']) ?></span></p>
            <p style="margin-bottom: 0.5rem;"><strong>Email :</strong> <span style="color: #fff;"><?= htmlspecialchars($requestData['email']) ?></span></p>
            <p style="margin-bottom: 0.5rem;"><strong>Poids :</strong> <span style="color: #fff;"><?= $requestData['current_weight'] ?> kg</span></p>
            <p style="margin-bottom: 0.5rem;"><strong>Taille :</strong> <span style="color: #fff;"><?= $requestData['height'] ? $requestData['height'].' cm' : 'Non précisé' ?></span></p>
            <p><strong>Objectif :</strong> <span style="color: var(--accent); font-weight: bold;"><?= htmlspecialchars($requestData['current_goal']) ?></span></p>
        </div>
        <div class="glass-card" style="padding: 1.5rem;">
            <h4 style="color: var(--text-muted); margin-bottom: 1rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem;">Message & Contexte</h4>
            <div style="color: #fff; line-height: 1.5; font-style: italic; background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 8px; min-height: 100px;">
                <?= !empty($requestData['message']) ? nl2br(htmlspecialchars($requestData['message'])) : '<span style="color: var(--text-muted);">Aucun message supplémentaire.</span>' ?>
            </div>
        </div>
    </div>

    <!-- Admin Form -->
    <div class="glass-card" style="margin-bottom: 3rem;">
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem;">
            <i class="fa-solid fa-pen-to-square"></i> Assignation du Programme Nutritionnel
        </h3>
        
        <form action="index.php?action=admin_update_request" method="POST">
            <input type="hidden" name="id" value="<?= $requestData['id'] ?>">

            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main); font-weight: 600;">
                    <i class="fa-solid fa-circle-half-stroke" style="color: var(--accent);"></i> Statut de la Requête
                </label>
                <select name="status" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border);">
                    <option value="pending"  <?= $requestData['status'] == 'pending'  ? 'selected' : '' ?>>En attente (Pending)</option>
                    <option value="approved" <?= $requestData['status'] == 'approved' ? 'selected' : '' ?>>Approuve (Approved)</option>
                    <option value="rejected" <?= $requestData['status'] == 'rejected' ? 'selected' : '' ?>>Rejete (Rejected)</option>
                </select>
            </div>

            <button type="submit" class="btn" style="width: 100%; font-size: 1.1rem; padding: 1rem;">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer le Programme
            </button>
        </form>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/layout.php'; 
?>
