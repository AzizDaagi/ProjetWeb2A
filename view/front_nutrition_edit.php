<?php ob_start(); ?>

<div class="animate-fade-in" style="max-width: 700px; margin: 0 auto; padding: 2rem;">
    <h2 style="color: var(--primary); margin-bottom: 0.5rem; text-align: center;">Modifier ma Demande</h2>
    <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Mettez à jour vos informations ci-dessous.</p>

    <?php if(isset($_GET['error'])): ?>
        <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--error); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: #fca5a5; text-align: center;">
            <?php 
                if($_GET['error'] == 'empty_fields') echo "Veuillez remplir tous les champs obligatoires.";
                elseif($_GET['error'] == 'invalid_name') echo "Le nom est invalide.";
                elseif($_GET['error'] == 'invalid_weight') echo "Le poids doit être un nombre positif (1-300).";
                elseif($_GET['error'] == 'invalid_height') echo "La taille est invalide (50-250).";
                else echo "Une erreur est survenue.";
            ?>
        </div>
    <?php endif; ?>

    <div class="glass-card">
        <form action="index.php?action=update_nutrition_request" method="POST" id="editNutritionForm" novalidate>
            <input type="hidden" name="id" value="<?= $request['id'] ?>">
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Nom Complet *</label>
                <input type="text" name="user_name" id="user_name" value="<?= htmlspecialchars($request['user_name']) ?>" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border);">
                <div id="error_name" style="color: var(--error); font-size: 0.85rem; margin-top: 5px; display: none;">Le nom ne peut pas être vide ou purement numérique.</div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Poids Actuel (kg) *</label>
                    <input type="text" name="current_weight" id="current_weight" value="<?= $request['current_weight'] ?>" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border);">
                    <div id="error_weight" style="color: var(--error); font-size: 0.85rem; margin-top: 5px; display: none;">Le poids doit être un nombre positif (1-300).</div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Taille (cm)</label>
                    <input type="text" name="height" id="height" value="<?= $request['height'] ?>" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border);">
                    <div id="error_height" style="color: var(--error); font-size: 0.85rem; margin-top: 5px; display: none;">La taille doit être entre 50 et 250 cm.</div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Objectif Actuel *</label>
                <select name="current_goal" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border);">
                    <option value="lose weight" <?= $request['current_goal'] == 'lose weight' ? 'selected' : '' ?>>Perdre du poids</option>
                    <option value="gain muscle" <?= $request['current_goal'] == 'gain muscle' ? 'selected' : '' ?>>Prendre de la masse musculaire</option>
                    <option value="maintain weight" <?= $request['current_goal'] == 'maintain weight' ? 'selected' : '' ?>>Maintenir son poids</option>
                </select>
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Message/Notes</label>
                <textarea name="message" rows="4" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border); resize: vertical;"><?= htmlspecialchars($request['message']) ?></textarea>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn" style="flex: 1;">Enregistrer les modifications</button>
                <a href="index.php?action=my_nutrition_requests&email=<?= urlencode($request['email']) ?>" class="btn btn-outline" style="flex: 1; text-align: center;">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('editNutritionForm').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Name
    const name = document.getElementById('user_name').value.trim();
    if (name === '' || !isNaN(name)) {
        document.getElementById('error_name').style.display = 'block';
        document.getElementById('user_name').style.borderColor = 'var(--error)';
        isValid = false;
    } else {
        document.getElementById('error_name').style.display = 'none';
        document.getElementById('user_name').style.borderColor = 'var(--card-border)';
    }
    
    // Weight
    const weight = document.getElementById('current_weight').value.trim();
    if (weight === '' || isNaN(weight) || weight <= 0 || weight > 300) {
        document.getElementById('error_weight').style.display = 'block';
        document.getElementById('current_weight').style.borderColor = 'var(--error)';
        isValid = false;
    } else {
        document.getElementById('error_weight').style.display = 'none';
        document.getElementById('current_weight').style.borderColor = 'var(--card-border)';
    }
    
    // Height
    const height = document.getElementById('height').value.trim();
    if (height !== '' && (isNaN(height) || height < 50 || height > 250)) {
        document.getElementById('error_height').style.display = 'block';
        document.getElementById('height').style.borderColor = 'var(--error)';
        isValid = false;
    } else {
        document.getElementById('error_height').style.display = 'none';
        document.getElementById('height').style.borderColor = 'var(--card-border)';
    }
    
    if (!isValid) e.preventDefault();
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/layout.php'; 
?>
