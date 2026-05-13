<?php
$baseUrl = $baseUrl ?? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$pageTitle = 'Smart Nutrition | Moteur de Génération';
require_once __DIR__ . '/../../controller/RecetteController.php';

$controller = new RecetteController();

$generated = null;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $maxKcal = (float)$_POST['max_kcal'];
    $minProt = (float)$_POST['min_prot'];
    $maxLipides = (float)$_POST['max_lipides'];
    $dietType = $_POST['diet_type'];

    $result = $controller->generateRecipeFromConstraints($maxKcal, $minProt, $maxLipides, $dietType);
    
    if ($result) {
        $generated = $result;
    } else {
        $errorMsg = "Impossible de générer une recette avec ces contraintes (aucun aliment compatible trouvé).";
    }
}

require_once __DIR__ . '/../template_only/layouts/admin_header.php';
?>

<div class="admin-page">

<div class="submit-page-wrapper">
    <p class="submit-page-intro">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
        Moteur de Génération de Recettes sous Contraintes
    </p>

    <a href="<?= $baseUrl ?>/index.php?action=admin-recipes" class="submit-back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour aux recettes
    </a>

    <!-- Formulaire de contraintes -->
    <div class="submit-form-card" style="margin-bottom: 30px;">
        <h1 style="margin:0 0 24px;font-size:22px;font-weight:800;color:#9b59b6;">
            <i class="fa-solid fa-robot"></i> Paramètres de l'IA
        </h1>

        <?php if ($errorMsg): ?>
            <div style="background: rgba(231,76,60,0.15); border: 1px solid #e74c3c; border-left: 4px solid #e74c3c; padding: 16px; border-radius: 8px; margin-bottom: 24px; color:#e74c3c;">
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $baseUrl ?>/index.php?action=recipe-generate">
            <input type="hidden" name="generate" value="1">
            
            <div class="submit-form-row">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="max_kcal">Calories Max (kcal)</label>
                    <input type="number" name="max_kcal" id="max_kcal" required value="<?= isset($_POST['max_kcal']) ? htmlspecialchars($_POST['max_kcal']) : '500' ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="min_prot">Protéines Min (g)</label>
                    <input type="number" name="min_prot" id="min_prot" required value="<?= isset($_POST['min_prot']) ? htmlspecialchars($_POST['min_prot']) : '30' ?>">
                </div>
            </div>

            <div class="submit-form-row" style="margin-top:16px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="max_lipides">Lipides Max (g)</label>
                    <input type="number" name="max_lipides" id="max_lipides" required value="<?= isset($_POST['max_lipides']) ? htmlspecialchars($_POST['max_lipides']) : '20' ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="diet_type">Régime Spécial</label>
                    <select name="diet_type" id="diet_type">
                        <option value="standard" <?= (isset($_POST['diet_type']) && $_POST['diet_type'] == 'standard') ? 'selected' : '' ?>>Standard (Aucun)</option>
                        <option value="vegetarien" <?= (isset($_POST['diet_type']) && $_POST['diet_type'] == 'vegetarien') ? 'selected' : '' ?>>Végétarien (Sans viande/poisson)</option>
                        <option value="sans_gluten" <?= (isset($_POST['diet_type']) && $_POST['diet_type'] == 'sans_gluten') ? 'selected' : '' ?>>Sans Gluten</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="submit-btn" style="background:#9b59b6; box-shadow:0 4px 15px rgba(155, 89, 182, 0.4);">
                <i class="fa-solid fa-cogs"></i> Générer la Combinaison
            </button>
        </form>
    </div>

    <!-- Résultat généré -->
    <?php if ($generated): ?>
    <div class="submit-form-card" style="border-top: 4px solid #2ecc71;">
        <h2 style="margin:0 0 20px;font-size:20px;font-weight:700;color:#2ecc71;">
            <i class="fa-solid fa-check-circle"></i> Combinaison Trouvée !
        </h2>
        
        <div style="background:rgba(10,16,28,0.5);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:20px;margin-bottom:24px;">
            <h3 style="margin:0 0 14px;font-size:16px;">Ingrédients choisis :</h3>
            <ul style="list-style:none;padding:0;margin:0 0 20px;">
                <?php foreach ($generated['aliments'] as $item): ?>
                    <li style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                        <span><strong style="color:#3498db;"><?= $item['quantite'] ?>g</strong> <?= htmlspecialchars($item['aliment']['nom']) ?></span>
                        <span style="font-size:12px;color:rgba(236,240,241,0.5);"><?= $item['aliment']['calories'] ?> kcal/100g</span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h3 style="margin:0 0 14px;font-size:16px;">Totaux calculés :</h3>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;text-align:center;">
                <div style="background:rgba(231,76,60,0.1);padding:10px;border-radius:6px;">
                    <div style="font-size:11px;color:rgba(236,240,241,0.5);">Calories</div>
                    <strong style="color:#e74c3c;"><?= $generated['totaux']['calories'] ?></strong>
                </div>
                <div style="background:rgba(52,152,219,0.1);padding:10px;border-radius:6px;">
                    <div style="font-size:11px;color:rgba(236,240,241,0.5);">Protéines</div>
                    <strong style="color:#3498db;"><?= $generated['totaux']['proteines'] ?>g</strong>
                </div>
                <div style="background:rgba(46,204,113,0.1);padding:10px;border-radius:6px;">
                    <div style="font-size:11px;color:rgba(236,240,241,0.5);">Glucides</div>
                    <strong style="color:#2ecc71;"><?= $generated['totaux']['glucides'] ?>g</strong>
                </div>
                <div style="background:rgba(243,156,18,0.1);padding:10px;border-radius:6px;">
                    <div style="font-size:11px;color:rgba(236,240,241,0.5);">Lipides</div>
                    <strong style="color:#f39c12;"><?= $generated['totaux']['lipides'] ?>g</strong>
                </div>
            </div>
            <p style="margin:10px 0 0;font-size:11px;color:rgba(236,240,241,0.4);text-align:right;">
                Score d'optimisation : <?= $generated['score'] ?> (0 = parfait)
            </p>
        </div>

        <form action="<?= $baseUrl ?>/index.php?action=admin-recipes" method="POST">
            <input type="hidden" name="action" value="add">
            
            <h3 style="margin:0 0 14px;font-size:16px;">Créer la recette finale</h3>
            
            <div class="form-group">
                <label>Nom de la recette</label>
                <input type="text" name="nom" required value="Recette Générée par IA" placeholder="Ex: Bol Protéiné au Poulet">
            </div>
            
            <div class="submit-form-row">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Temps de préparation</label>
                    <input type="text" name="temps_preparation" required value="15 minutes">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Difficulté</label>
                    <select name="difficulte" required>
                        <option value="Très Facile" selected>Très Facile</option>
                        <option value="Facile">Facile</option>
                        <option value="Moyen">Moyen</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top:16px;">
                <label>Description / Instructions</label>
                <textarea name="description" required style="min-height:80px;">Voici une recette générée automatiquement pour respecter vos macros.</textarea>
            </div>

            <!-- Ingrédients cachés pour le transfert vers manage_recettes -->
            <?php foreach ($generated['aliments'] as $item): ?>
                <input type="hidden" name="aliments[]" value="<?= $item['aliment']['id'] ?>">
                <input type="hidden" name="quantites[<?= $item['aliment']['id'] ?>]" value="<?= $item['quantite'] ?>">
            <?php endforeach; ?>

            <button type="submit" class="submit-btn">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer cette Recette
            </button>
        </form>
    </div>
    <?php endif; ?>

</div>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/admin_footer.php'; ?>
