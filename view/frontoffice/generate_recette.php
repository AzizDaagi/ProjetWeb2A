<?php
$pageTitle = 'Smart Nutrition | Générateur de Recettes IA';
require_once __DIR__ . '/../../controler/RecetteController.php';

$controller = new RecetteController();

$generated = null;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $maxKcal   = (float)$_POST['max_kcal'];
    $minProt   = (float)$_POST['min_prot'];
    $maxLipides = (float)$_POST['max_lipides'];
    $dietType  = $_POST['diet_type'];

    $result = $controller->generateRecipeFromConstraints($maxKcal, $minProt, $maxLipides, $dietType);
    $generated = $result ?: null;
    if (!$result) {
        $errorMsg = "Aucune combinaison trouvée pour ces contraintes. Essayez d'être moins restrictif.";
    }
}

require_once __DIR__ . '/../template_only/layouts/header.php';
?>

<!-- Hero Banner -->
<section style="background: linear-gradient(135deg, rgba(155,89,182,0.2), rgba(52,152,219,0.15));
                border-bottom: 1px solid rgba(255,255,255,0.07);
                padding: 60px 32px 50px; text-align:center;">
    <span style="display:inline-flex;align-items:center;justify-content:center;
                 width:72px;height:72px;background:rgba(155,89,182,0.2);
                 border:1px solid rgba(155,89,182,0.5);border-radius:50%;
                 font-size:28px;color:#9b59b6;margin-bottom:20px;">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
    </span>
    <h1 style="margin:0 0 12px;font-size:36px;font-weight:900;
               background:linear-gradient(135deg,#9b59b6,#3498db);
               -webkit-background-clip:text;-webkit-text-fill-color:transparent;">
        Générateur de Recettes IA
    </h1>
    <p style="color:rgba(236,240,241,0.6);font-size:16px;max-width:560px;margin:0 auto;">
        Décrivez vos besoins nutritionnels. Le moteur choisit automatiquement les meilleurs ingrédients et leurs quantités idéales.
    </p>
</section>

<div class="submit-page-wrapper" style="max-width:860px;">

    <!-- Chips d'exemples rapides -->
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:28px;" id="presets">
        <button type="button" class="preset-chip" onclick="applyPreset(500,30,20,'standard')">
            🏋️ Musculation (500 kcal, 30g prot)
        </button>
        <button type="button" class="preset-chip" onclick="applyPreset(400,20,15,'vegetarien')">
            🥦 Végétarien léger (400 kcal)
        </button>
        <button type="button" class="preset-chip" onclick="applyPreset(600,25,25,'sans_gluten')">
            🌾 Sans Gluten (600 kcal)
        </button>
        <button type="button" class="preset-chip" onclick="applyPreset(350,35,10,'standard')">
            💪 Haute Protéine (350 kcal)
        </button>
    </div>

    <!-- Formulaire de contraintes -->
    <div class="submit-form-card" style="margin-bottom:30px;">
        <h2 style="margin:0 0 22px;font-size:19px;font-weight:800;display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-sliders" style="color:#9b59b6;"></i> Vos Contraintes Nutritionnelles
        </h2>

        <?php if ($errorMsg): ?>
            <div style="background:rgba(231,76,60,0.15);border:1px solid #e74c3c;border-left:4px solid #e74c3c;
                        padding:14px 16px;border-radius:8px;margin-bottom:22px;color:#e74c3c;font-size:14px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="generate_recette.php" id="gen-form">
            <input type="hidden" name="generate" value="1">

            <div class="submit-form-row">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="max_kcal">
                        <i class="fa-solid fa-fire" style="color:#e74c3c;"></i> Calories Max (kcal)
                    </label>
                    <input type="number" name="max_kcal" id="max_kcal" min="100" max="3000" required
                           value="<?= isset($_POST['max_kcal']) ? htmlspecialchars($_POST['max_kcal']) : '500' ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="min_prot">
                        <i class="fa-solid fa-dumbbell" style="color:#3498db;"></i> Protéines Min (g)
                    </label>
                    <input type="number" name="min_prot" id="min_prot" min="0" max="200" required
                           value="<?= isset($_POST['min_prot']) ? htmlspecialchars($_POST['min_prot']) : '30' ?>">
                </div>
            </div>

            <div class="submit-form-row" style="margin-top:16px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="max_lipides">
                        <i class="fa-solid fa-droplet" style="color:#f39c12;"></i> Lipides Max (g)
                    </label>
                    <input type="number" name="max_lipides" id="max_lipides" min="0" max="200" required
                           value="<?= isset($_POST['max_lipides']) ? htmlspecialchars($_POST['max_lipides']) : '20' ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="diet_type">
                        <i class="fa-solid fa-leaf" style="color:#2ecc71;"></i> Régime Alimentaire
                    </label>
                    <select name="diet_type" id="diet_type">
                        <option value="standard" <?= (!isset($_POST['diet_type']) || $_POST['diet_type'] == 'standard') ? 'selected' : '' ?>>Standard (Tout)</option>
                        <option value="vegetarien" <?= (isset($_POST['diet_type']) && $_POST['diet_type'] == 'vegetarien') ? 'selected' : '' ?>>🥦 Végétarien</option>
                        <option value="sans_gluten" <?= (isset($_POST['diet_type']) && $_POST['diet_type'] == 'sans_gluten') ? 'selected' : '' ?>>🌾 Sans Gluten</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="submit-btn"
                    style="background:linear-gradient(135deg,#9b59b6,#3498db);
                           box-shadow:0 6px 20px rgba(155,89,182,0.4);margin-top:10px;">
                <i class="fa-solid fa-cogs"></i> Générer ma Recette
            </button>
        </form>
    </div>

    <!-- Résultat -->
    <?php if ($generated): 
        $t = $generated['totaux'];
        $isPerfect = $generated['score'] === 0;
    ?>
    <div class="submit-form-card" style="border-top:4px solid <?= $isPerfect ? '#2ecc71' : '#f39c12' ?>;">
        <h2 style="margin:0 0 6px;font-size:20px;font-weight:700;color:<?= $isPerfect ? '#2ecc71' : '#f39c12' ?>;">
            <i class="fa-solid fa-<?= $isPerfect ? 'check-circle' : 'triangle-exclamation' ?>"></i>
            <?= $isPerfect ? 'Recette Parfaite trouvée !' : 'Meilleure combinaison disponible' ?>
        </h2>
        <?php if (!$isPerfect): ?>
        <p style="color:rgba(236,240,241,0.5);font-size:13px;margin:0 0 20px;">
            Certaines contraintes sont difficiles à satisfaire exactement avec les aliments disponibles. Voici la combinaison la plus proche.
        </p>
        <?php else: ?>
        <p style="color:rgba(236,240,241,0.5);font-size:13px;margin:0 0 20px;">
            Toutes vos contraintes sont respectées !
        </p>
        <?php endif; ?>

        <!-- Ingrédients -->
        <div style="margin-bottom:22px;">
            <h3 style="margin:0 0 12px;font-size:15px;font-weight:700;color:rgba(236,240,241,0.7);
                       border-left:3px solid #3498db;padding-left:10px;">Ingrédients à préparer</h3>
            <div style="display:grid;gap:10px;">
                <?php foreach ($generated['aliments'] as $item): 
                    $cal_item = round($item['aliment']['calories'] * $item['quantite'] / 100);
                ?>
                <div style="display:flex;align-items:center;justify-content:space-between;
                            background:rgba(52,152,219,0.07);border:1px solid rgba(52,152,219,0.15);
                            border-radius:8px;padding:12px 16px;gap:12px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span style="background:rgba(52,152,219,0.2);color:#3498db;font-weight:800;
                                     padding:4px 10px;border-radius:4px;font-size:15px;min-width:64px;text-align:center;">
                            <?= $item['quantite'] ?>g
                        </span>
                        <span style="font-weight:600;font-size:15px;">
                            <?= htmlspecialchars($item['aliment']['nom']) ?>
                        </span>
                    </div>
                    <span style="font-size:12px;color:rgba(236,240,241,0.45);white-space:nowrap;">
                        = <?= $cal_item ?> kcal
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tableau nutritionnel -->
        <div style="margin-bottom:10px;">
            <h3 style="margin:0 0 12px;font-size:15px;font-weight:700;color:rgba(236,240,241,0.7);
                       border-left:3px solid #f39c12;padding-left:10px;">Bilan Nutritionnel Total</h3>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;text-align:center;">
                <div style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.2);padding:14px;border-radius:10px;">
                    <p style="margin:0 0 4px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Calories</p>
                    <strong style="font-size:22px;color:#e74c3c;"><?= $t['calories'] ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.4);"> kcal</span>
                </div>
                <div style="background:rgba(52,152,219,0.1);border:1px solid rgba(52,152,219,0.2);padding:14px;border-radius:10px;">
                    <p style="margin:0 0 4px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Protéines</p>
                    <strong style="font-size:22px;color:#3498db;"><?= $t['proteines'] ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.4);"> g</span>
                </div>
                <div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.2);padding:14px;border-radius:10px;">
                    <p style="margin:0 0 4px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Glucides</p>
                    <strong style="font-size:22px;color:#2ecc71;"><?= $t['glucides'] ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.4);"> g</span>
                </div>
                <div style="background:rgba(243,156,18,0.1);border:1px solid rgba(243,156,18,0.2);padding:14px;border-radius:10px;">
                    <p style="margin:0 0 4px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Lipides</p>
                    <strong style="font-size:22px;color:#f39c12;"><?= $t['lipides'] ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.4);"> g</span>
                </div>
            </div>
        </div>

        <!-- Barre visuelle de répartition -->
        <?php
            $calProt = $t['proteines'] * 4;
            $calGluc = $t['glucides'] * 4;
            $calLip  = $t['lipides'] * 9;
            $calTotal = $calProt + $calGluc + $calLip;
            $pProt = $calTotal > 0 ? round($calProt / $calTotal * 100) : 0;
            $pGluc = $calTotal > 0 ? round($calGluc / $calTotal * 100) : 0;
            $pLip  = $calTotal > 0 ? round($calLip  / $calTotal * 100) : 0;
        ?>
        <div style="margin-top:18px;">
            <p style="font-size:12px;color:rgba(236,240,241,0.5);margin:0 0 8px;">Répartition calorique :</p>
            <div style="display:flex;height:12px;border-radius:6px;overflow:hidden;gap:2px;">
                <div style="width:<?= $pProt ?>%;background:#3498db;" title="Protéines <?= $pProt ?>%"></div>
                <div style="width:<?= $pGluc ?>%;background:#2ecc71;" title="Glucides <?= $pGluc ?>%"></div>
                <div style="width:<?= $pLip ?>%;background:#f39c12;" title="Lipides <?= $pLip ?>%"></div>
            </div>
            <div style="display:flex;gap:16px;margin-top:6px;font-size:11px;color:rgba(236,240,241,0.5);">
                <span><span style="color:#3498db;">■</span> Prot. <?= $pProt ?>%</span>
                <span><span style="color:#2ecc71;">■</span> Gluc. <?= $pGluc ?>%</span>
                <span><span style="color:#f39c12;">■</span> Lip. <?= $pLip ?>%</span>
            </div>
        </div>

        <div style="margin-top:22px;padding-top:18px;border-top:1px solid rgba(255,255,255,0.07);">
            <a href="generate_recette.php" class="submit-btn"
               style="background:rgba(155,89,182,0.2);border:1px solid rgba(155,89,182,0.4);
                      color:#9b59b6;box-shadow:none;display:inline-flex;text-decoration:none;width:auto;">
                <i class="fa-solid fa-rotate"></i> Générer une autre combinaison
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>

<style>
.preset-chip {
    background: rgba(155, 89, 182, 0.1);
    border: 1px solid rgba(155, 89, 182, 0.3);
    color: rgba(236,240,241,0.8);
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.preset-chip:hover {
    background: rgba(155, 89, 182, 0.25);
    border-color: rgba(155, 89, 182, 0.7);
    color: #9b59b6;
    transform: translateY(-2px);
}
</style>

<script>
function applyPreset(kcal, prot, lip, diet) {
    document.getElementById('max_kcal').value = kcal;
    document.getElementById('min_prot').value = prot;
    document.getElementById('max_lipides').value = lip;
    document.getElementById('diet_type').value = diet;
    document.getElementById('gen-form').submit();
}
</script>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
