<?php ob_start(); ?>

<div class="animate-fade-in" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <h2 style="color: var(--primary); margin-bottom: 0.5rem; text-align: center;">Demande de Programme Nutritionnel</h2>
    <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Remplissez ce formulaire pour recevoir un programme personnalisé basé sur vos objectifs.</p>

    <?php if(isset($_GET['error'])): ?>
        <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--error); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: #fca5a5; text-align: center;">
            <?php 
                switch($_GET['error']) {
                    case 'empty_fields': echo "Tous les champs obligatoires doivent être remplis."; break;
                    case 'invalid_weight': echo "Le poids doit être un nombre compris entre 1 et 300 kg."; break;
                    case 'invalid_goal': echo "Veuillez sélectionner un objectif valide."; break;
                    case 'invalid_height': echo "La taille doit être comprise entre 50 et 250 cm."; break;
                    case 'db_error': echo "Une erreur est survenue lors de l'enregistrement. Réessayez plus tard."; break;
                    default: echo "Erreur de validation.";
                }
            ?>
        </div>
    <?php endif; ?>

    <div class="glass-card">
        <form action="index.php?action=process_nutrition_request" method="POST" id="nutritionForm" novalidate>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Nom Complet *</label>
                    <input type="text" name="user_name" id="user_name" placeholder="John Doe" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border);">
                    <div id="error_name" style="color: var(--error); font-size: 0.85rem; margin-top: 5px; display: none;">Ce champ est requis.</div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Email *</label>
                    <input type="email" name="email" id="email" placeholder="john@example.com" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border);">
                    <div id="error_email" style="color: var(--error); font-size: 0.85rem; margin-top: 5px; display: none;">Email invalide.</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Poids Actuel (kg) *</label>
                    <input type="number" name="current_weight" id="current_weight" step="0.1" placeholder="ex: 75.5" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border);">
                    <div id="error_weight" style="color: var(--error); font-size: 0.85rem; margin-top: 5px; display: none;">Doit être entre 1 et 300 kg.</div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Taille (cm) <span style="color: var(--text-muted); font-size: 0.8rem;">Optionnel</span></label>
                    <input type="number" name="height" id="height" placeholder="ex: 180" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border);">
                    <div id="error_height" style="color: var(--error); font-size: 0.85rem; margin-top: 5px; display: none;">Taille absurde.</div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Objectif Actuel *</label>
                <select name="current_goal" id="current_goal" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border);">
                    <option value="">Sélectionnez un objectif...</option>
                    <option value="lose weight">Perdre du poids</option>
                    <option value="gain muscle">Prendre de la masse musculaire</option>
                    <option value="maintain weight">Maintenir son poids</option>
                </select>
                <div id="error_goal" style="color: var(--error); font-size: 0.85rem; margin-top: 5px; display: none;">Veuillez choisir un objectif.</div>
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-main);">Message/Notes <span style="color: var(--text-muted); font-size: 0.8rem;">Optionnel</span></label>
                <textarea name="message" rows="4" placeholder="Avez-vous des allergies ou des contraintes spécifiques ?" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid var(--card-border); resize: vertical;"></textarea>
            </div>

            <button type="submit" class="btn" style="width: 100%; font-size: 1.1rem; padding: 1rem;">Recevoir mes Recommandations</button>
        </form>
    </div>
</div>

<script>
document.getElementById('nutritionForm').addEventListener('submit', function(e) {
    let isValid = true;

    const name = document.getElementById('user_name');
    const email = document.getElementById('email');
    const weight = document.getElementById('current_weight');
    const goal = document.getElementById('current_goal');
    const height = document.getElementById('height');

    // Name Validate
    if(!name.value.trim()){
        document.getElementById('error_name').style.display = 'block';
        isValid = false;
    } else { document.getElementById('error_name').style.display = 'none'; }

    // Email Validate
    if(!email.value.trim() || !email.value.includes('@')){
        document.getElementById('error_email').style.display = 'block';
        isValid = false;
    } else { document.getElementById('error_email').style.display = 'none'; }

    // Weight Validate
    let wVal = parseFloat(weight.value);
    if(!weight.value || isNaN(wVal) || wVal < 1 || wVal > 300) {
        document.getElementById('error_weight').style.display = 'block';
        isValid = false;
    } else { document.getElementById('error_weight').style.display = 'none'; }

    // Goal Validate
    if(!goal.value){
        document.getElementById('error_goal').style.display = 'block';
        isValid = false;
    } else { document.getElementById('error_goal').style.display = 'none'; }

    if(!isValid) {
        e.preventDefault();
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../../../View/layout.php'; 
?>
