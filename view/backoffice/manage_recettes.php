<?php
$baseUrl = $baseUrl ?? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$pageTitle = 'Smart Nutrition | Gestion des Recettes';
require_once __DIR__ . '/../../controller/RecetteController.php';
require_once __DIR__ . '/../../controller/AlimentController.php';

$controller      = new RecetteController();
$alimentController = new AlimentController();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $aliments_ids = isset($_POST['aliments']) ? $_POST['aliments'] : [];
        $quantites_raw = isset($_POST['quantites']) ? $_POST['quantites'] : [];
        
        $aliments_quantites = [];
        foreach ($aliments_ids as $id) {
            $aliments_quantites[$id] = isset($quantites_raw[$id]) ? $quantites_raw[$id] : 0;
        }

        $image_url = $_POST['existing_image_url'] ?? null;

        // Handle File Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/recettes/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image_url = $baseUrl . '/view/uploads/recettes/' . $fileName;
            }
        }

        if ($_POST['action'] === 'add') {
            $controller->addRecette(
                $_POST['nom'], $_POST['description'], $_POST['temps_preparation'],
                $_POST['difficulte'], $image_url, $aliments_quantites
            );
            $warnings = $controller->checkEquilibreNutritionnel($aliments_quantites);
            if (!empty($warnings)) {
                $_SESSION['flash_warnings'] = $warnings;
            }
        } elseif ($_POST['action'] === 'update') {
            $controller->updateRecette(
                $_POST['id'], $_POST['nom'], $_POST['description'], $_POST['temps_preparation'],
                $_POST['difficulte'], $image_url, $aliments_quantites
            );
            $warnings = $controller->checkEquilibreNutritionnel($aliments_quantites);
            if (!empty($warnings)) {
                $_SESSION['flash_warnings'] = $warnings;
            }
        } elseif ($_POST['action'] === 'delete') {
            $controller->deleteRecette($_POST['id']);
        }
        header('Location: ' . $baseUrl . '/index.php?action=admin-recipes');
        exit;
    }
}

$flashWarnings = [];
if (isset($_SESSION['flash_warnings'])) {
    $flashWarnings = $_SESSION['flash_warnings'];
    unset($_SESSION['flash_warnings']);
}

$recettes = $controller->listRecettes();
$tous_aliments = $alimentController->listAliments();

$recettes_aliments_map = [];
$recettes_aliments_quantites_map = [];
foreach ($recettes as $r) {
    $assoc = $controller->getAlimentsByRecette($r['id']);
    $recettes_aliments_map[$r['id']] = array_map(fn($a) => $a['id'], $assoc);
    
    $quantites_map = [];
    foreach ($assoc as $a) {
        $quantites_map[$a['id']] = $a['quantite'];
    }
    $recettes_aliments_quantites_map[$r['id']] = $quantites_map;
}

// Edit Mode Logic
$recetteToEdit = null;
if (isset($_GET['edit_id'])) {
    foreach ($recettes as $r) {
        if ($r['id'] == $_GET['edit_id']) {
            $recetteToEdit = $r;
            break;
        }
    }
}

require_once __DIR__ . '/../template_only/layouts/admin_header.php';
?>

<div class="admin-page">

<!-- ================= FORM SECTION ================= -->
<div class="submit-page-wrapper">

    <p class="submit-page-intro">
        <i class="fa-solid fa-circle-info"></i>
        Créez et gérez vos recettes. Associez-leur des aliments pour calculer leurs apports.
    </p>

    <a href="<?= $baseUrl ?>/index.php?action=admin-dashboard" class="submit-back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour au Dashboard
    </a>

    <?php if (!empty($flashWarnings)): ?>
        <div style="background: rgba(243, 156, 18, 0.15); border: 1px solid #f39c12; border-left: 4px solid #f39c12; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <h4 style="color: #f39c12; margin: 0 0 10px 0;"><i class="fa-solid fa-triangle-exclamation"></i> Attention : Équilibre Nutritionnel</h4>
            <ul style="margin: 0; padding-left: 20px; color: rgba(255,255,255,0.9);">
                <?php foreach ($flashWarnings as $w): ?>
                    <li style="margin-bottom: 6px;"><?= htmlspecialchars($w) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="submit-form-card">
        <h1 id="form-title" style="margin:0 0 24px;font-size:22px;font-weight:800;">
            <?php if ($recetteToEdit): ?>
                <i class="fa-solid fa-pen-to-square" style="color:#f39c12;margin-right:8px;"></i> Modifier la recette
            <?php else: ?>
                <i class="fa-solid fa-plus-circle" style="color:#2ecc71;margin-right:8px;"></i> Créer une Recette
            <?php endif; ?>
        </h1>

        <form method="POST" action="<?= $baseUrl ?>/index.php?action=admin-recipes" id="recette-form" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="action" id="action-input" value="<?= $recetteToEdit ? 'update' : 'add' ?>">
            <input type="hidden" name="id"     id="form-id" value="<?= $recetteToEdit ? htmlspecialchars($recetteToEdit['id']) : '' ?>">
            <input type="hidden" name="existing_image_url" value="<?= $recetteToEdit ? htmlspecialchars($recetteToEdit['image_url'] ?? '') : '' ?>">

            <!-- Nom -->
            <div class="form-group">
                <label for="nom-input">Nom de la recette</label>
                <input type="text" name="nom" id="nom-input" placeholder="ex: Tarte aux pommes" value="<?= $recetteToEdit ? htmlspecialchars($recetteToEdit['nom']) : '' ?>">
            </div>

            <!-- Aliments checkboxes -->
            <div class="form-group">
                <label>Ingrédients / Aliments associés</label>
                <div style="background:rgba(10,16,28,0.6);border:1px solid rgba(87,101,116,0.55);border-radius:8px;padding:14px;max-height:200px;overflow-y:auto;">
                    <?php if (!empty($tous_aliments)): ?>
                        <?php foreach ($tous_aliments as $al): ?>
                            <?php 
                                $isChecked = false;
                                $qte = '';
                                if ($recetteToEdit && isset($recettes_aliments_map[$recetteToEdit['id']])) {
                                    if (in_array($al['id'], $recettes_aliments_map[$recetteToEdit['id']])) {
                                        $isChecked = true;
                                        $qte = $recettes_aliments_quantites_map[$recetteToEdit['id']][$al['id']] ?? '';
                                    }
                                }
                            ?>
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;font-weight:normal;color:rgba(236,240,241,0.8);font-size:14px;">
                                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;flex-grow:1;">
                                    <input type="checkbox" name="aliments[]" class="aliment-checkbox"
                                           value="<?= $al['id'] ?>"
                                           <?= $isChecked ? 'checked' : '' ?>
                                           style="width:16px;height:16px;flex-shrink:0;cursor:pointer;"
                                           onchange="document.getElementById('qte_<?= $al['id'] ?>').style.display = this.checked ? 'block' : 'none';">
                                    <?= htmlspecialchars((string)$al['nom']) ?>
                                </label>
                                <input type="number" step="1" min="1" name="quantites[<?= $al['id'] ?>]" id="qte_<?= $al['id'] ?>" class="qte-input"
                                       placeholder="Qté (g)" value="<?= htmlspecialchars((string)$qte) ?>" 
                                       style="width:90px; padding:4px 8px; font-size:13px; display:<?= $isChecked ? 'block' : 'none' ?>; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.2); color:white; border-radius:4px;">
                                <span class="product-card-badge badge-green" style="flex-shrink:0;">
                                    <?= htmlspecialchars((string)$al['calories']) ?> kcal
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:rgba(236,240,241,0.4);margin:0;font-size:13px;">
                            Aucun aliment disponible. <a href="<?= $baseUrl ?>/index.php?action=admin-aliments" style="color:#2ecc71;">Créez-en un d'abord.</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="desc-input">Description / Étapes de préparation</label>
                <textarea name="description" id="desc-input"
                          placeholder="Décrivez les étapes de la recette..." style="min-height:130px;"><?= $recetteToEdit ? htmlspecialchars($recetteToEdit['description']) : '' ?></textarea>
            </div>

            <!-- Temps + Difficulté -->
            <div class="submit-form-row">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="temps-input">Temps de préparation</label>
                    <input type="text" name="temps_preparation" id="temps-input"
                           placeholder="ex: 30 minutes" value="<?= $recetteToEdit ? htmlspecialchars($recetteToEdit['temps_preparation']) : '' ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="diff-input">Difficulté</label>
                    <select name="difficulte" id="diff-input">
                        <option value="">Sélectionnez un niveau</option>
                        <?php 
                        $diffs = ['Très Facile', 'Facile', 'Moyen', 'Difficile', 'Expert'];
                        foreach($diffs as $d) {
                            $selected = ($recetteToEdit && $recetteToEdit['niveau_difficulte'] === $d) ? 'selected' : '';
                            echo "<option value=\"$d\" $selected>$d</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Image File Upload -->
            <div class="form-group" style="margin-top:20px;">
                <label for="image-input">Photo de la recette (PC)</label>
                <?php if ($recetteToEdit && !empty($recetteToEdit['image_url'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= htmlspecialchars($recetteToEdit['image_url']) ?>" alt="Actuelle" style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2);">
                        <span style="font-size: 12px; color: rgba(236,240,241,0.6); display: block;">Image actuelle</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="image" id="image-input" accept="image/*" style="padding: 10px; background: rgba(0,0,0,0.2); border: 1px dashed rgba(255,255,255,0.3); border-radius: 8px; width: 100%; color: white;">
            </div>

            <button type="submit" id="submit-btn" class="submit-btn">
                <?php if ($recetteToEdit): ?>
                    <i class="fa-solid fa-floppy-disk"></i> Sauvegarder
                <?php else: ?>
                    <i class="fa-solid fa-paper-plane"></i> Créer la recette
                <?php endif; ?>
            </button>
            <?php if ($recetteToEdit): ?>
                <a href="<?= $baseUrl ?>/index.php?action=admin-recipes" class="submit-btn-cancel" style="display:inline-flex; text-decoration:none; justify-content:center; align-items:center;">
                    <i class="fa-solid fa-xmark"></i> Annuler
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ================= LIST SECTION ================= -->
<div class="submit-page-wrapper" style="margin-top: 40px;">
    <h2 style="margin: 0 0 24px; font-size: 20px; font-weight: 800;">
        <i class="fa-solid fa-list" style="color:#2ecc71; margin-right:8px;"></i>
        Recettes existantes
        <span style="font-size:14px; font-weight:400; color:rgba(255,255,255,0.5); margin-left:10px;">(<?= count($recettes) ?> recette<?= count($recettes) > 1 ? 's' : '' ?>)</span>
    </h2>

    <?php if (empty($recettes)): ?>
        <div style="text-align:center; padding:40px; color:rgba(255,255,255,0.4);">
            <i class="fa-solid fa-plate-wheat" style="font-size:40px; margin-bottom:12px; display:block;"></i>
            Aucune recette pour le moment. Créez-en une ci-dessus.
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 16px;">
        <?php foreach ($recettes as $r): ?>
            <?php
                $recetteAliments  = $recettes_aliments_map[$r['id']] ?? [];
                $recetteQuantites = $recettes_aliments_quantites_map[$r['id']] ?? [];
                // build ingredient list from tous_aliments
                $ingredientNames = [];
                foreach ($tous_aliments as $al) {
                    if (in_array($al['id'], $recetteAliments)) {
                        $qte = $recetteQuantites[$al['id']] ?? 0;
                        $ingredientNames[] = htmlspecialchars($al['nom']) . ' (' . (int)$qte . 'g)';
                    }
                }
                $nutrition = $controller->calculerNutritionTotale($r['id']);
            ?>
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 20px; display: flex; gap: 20px; align-items: flex-start;">

                <!-- Image -->
                <?php if (!empty($r['image_url'])): ?>
                    <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="" style="width:90px; height:70px; object-fit:cover; border-radius:8px; flex-shrink:0; border:1px solid rgba(255,255,255,0.1);">
                <?php else: ?>
                    <div style="width:90px; height:70px; border-radius:8px; background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fa-solid fa-utensils" style="color:rgba(255,255,255,0.2); font-size:24px;"></i>
                    </div>
                <?php endif; ?>

                <!-- Info -->
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px; flex-wrap:wrap;">
                        <span style="font-weight:700; font-size:16px;"><?= htmlspecialchars($r['nom']) ?></span>
                        <span style="background:rgba(46,204,113,0.15); color:#2ecc71; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;"><?= htmlspecialchars($r['niveau_difficulte'] ?? '-') ?></span>
                        <span style="color:rgba(255,255,255,0.4); font-size:12px;"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($r['temps_preparation'] ?? '-') ?></span>
                    </div>

                    <!-- Ingredients -->
                    <?php if (!empty($ingredientNames)): ?>
                        <div style="font-size:12px; color:rgba(255,255,255,0.5); margin-bottom:8px;">
                            <i class="fa-solid fa-leaf" style="color:#2ecc71;"></i>
                            <?= implode(' &bull; ', $ingredientNames) ?>
                        </div>
                    <?php else: ?>
                        <div style="font-size:12px; color:rgba(255,255,255,0.3); margin-bottom:8px;">
                            <i class="fa-solid fa-triangle-exclamation" style="color:#f39c12;"></i> Aucun ingrédient associé
                        </div>
                    <?php endif; ?>

                    <!-- Nutrition summary -->
                    <?php if ($nutrition['calories'] > 0): ?>
                        <div style="display:flex; gap:14px; flex-wrap:wrap; font-size:12px;">
                            <span style="color:#e67e22;"><b><?= round($nutrition['calories']) ?></b> kcal</span>
                            <span style="color:#3498db;"><b><?= round($nutrition['proteines'], 1) ?>g</b> prot</span>
                            <span style="color:#f1c40f;"><b><?= round($nutrition['glucides'], 1) ?>g</b> gluc</span>
                            <span style="color:#9b59b6;"><b><?= round($nutrition['lipides'], 1) ?>g</b> lip</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div style="display:flex; flex-direction:column; gap:8px; flex-shrink:0;">
                    <a href="<?= $baseUrl ?>/index.php?action=admin-recipes&edit_id=<?= $r['id'] ?>" 
                       style="display:inline-flex; align-items:center; gap:6px; padding:7px 14px; background:rgba(243,156,18,0.15); color:#f39c12; border:1px solid rgba(243,156,18,0.3); border-radius:6px; text-decoration:none; font-size:13px; font-weight:600;">
                        <i class="fa-solid fa-pen-to-square"></i> Modifier
                    </a>
                    <form method="POST" action="<?= $baseUrl ?>/index.php?action=admin-recipes" onsubmit="return confirm('Supprimer cette recette ?')" style="margin:0;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button type="submit" style="display:inline-flex; align-items:center; gap:6px; padding:7px 14px; background:rgba(231,76,60,0.15); color:#e74c3c; border:1px solid rgba(231,76,60,0.3); border-radius:6px; cursor:pointer; font-size:13px; font-weight:600; width:100%; justify-content:center;">
                            <i class="fa-solid fa-trash"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('recette-form').addEventListener('submit', function(e) {
    let hasErrors = false;

    // Reset previous errors dynamically
    document.querySelectorAll('.error-msg').forEach(el => el.remove());
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
        el.style.border = ''; 
    });

    function showError(inputId, message) {
        hasErrors = true;
        let inputEl = document.getElementById(inputId);
        if(!inputEl) return;
        inputEl.classList.add('is-invalid');
        inputEl.style.border = '1px solid #dc3545';
        
        let errorSpan = document.createElement('span');
        errorSpan.className = 'error-msg';
        errorSpan.style.color = '#dc3545';
        errorSpan.style.fontSize = '0.85em';
        errorSpan.style.display = 'block';
        errorSpan.style.marginTop = '5px';
        errorSpan.innerText = message;
        
        inputEl.parentNode.appendChild(errorSpan);
    }

    let action = document.getElementById('action-input').value;

    let nom = document.getElementById('nom-input').value.trim();
    if (nom === "") {
        showError('nom-input', "Veuillez entrer le nom de la recette.");
    } else if (nom.length < 3) {
        showError('nom-input', "Le nom doit contenir au moins 3 caractères.");
    }

    let description = document.getElementById('desc-input').value.trim();
    if (description === "") {
        showError('desc-input', "Veuillez entrer une description.");
    } else if (description.length < 10) {
        showError('desc-input', "La description doit contenir au moins 10 caractères.");
    }

    let temps = document.getElementById('temps-input').value.trim();
    if (temps === "") {
        showError('temps-input', "Veuillez entrer le temps de préparation.");
    }

    let diff = document.getElementById('diff-input').value;
    if (diff === "") {
        showError('diff-input', "Veuillez sélectionner un niveau de difficulté.");
    }

    let imageInput = document.getElementById('image-input');
    if (action === 'add' && imageInput.files.length === 0) {
        showError('image-input', "Veuillez sélectionner une image pour la nouvelle recette.");
    }

    let alimentsCheckboxes = document.querySelectorAll('.aliment-checkbox');
    let hasCheckedAliment = false;
    let alimentsContainer = null;
    
    for (let i = 0; i < alimentsCheckboxes.length; i++) {
        if (!alimentsContainer) {
            alimentsContainer = alimentsCheckboxes[i].closest('div').parentNode;
        }
        
        if (alimentsCheckboxes[i].checked) {
            hasCheckedAliment = true;
            let id = alimentsCheckboxes[i].value;
            let qteInput = document.getElementById('qte_' + id);
            if (qteInput) {
                let qteValue = qteInput.value.trim();
                if (qteValue === "" || isNaN(qteValue) || parseFloat(qteValue) <= 0) {
                    hasErrors = true;
                    qteInput.classList.add('is-invalid');
                    qteInput.style.border = '1px solid #dc3545';
                    // We attach the error to the row container
                    let row = qteInput.parentNode;
                    let existingErr = row.querySelector('.error-msg');
                    if (!existingErr) {
                        let errorSpan = document.createElement('span');
                        errorSpan.className = 'error-msg';
                        errorSpan.style.color = '#dc3545';
                        errorSpan.style.fontSize = '0.85em';
                        errorSpan.style.display = 'block';
                        errorSpan.style.width = '100%';
                        errorSpan.style.marginTop = '5px';
                        errorSpan.innerText = "Quantité requise (>0).";
                        row.appendChild(errorSpan);
                        row.style.flexWrap = 'wrap';
                    }
                }
            }
        }
    }

    if (!hasCheckedAliment && alimentsCheckboxes.length > 0 && alimentsContainer) {
         hasErrors = true;
         alimentsContainer.classList.add('is-invalid');
         alimentsContainer.style.border = '1px solid #dc3545';
         let errorSpan = document.createElement('span');
         errorSpan.className = 'error-msg';
         errorSpan.style.color = '#dc3545';
         errorSpan.style.fontSize = '0.85em';
         errorSpan.style.display = 'block';
         errorSpan.style.marginTop = '5px';
         errorSpan.innerText = "Veuillez sélectionner au moins un aliment.";
         alimentsContainer.appendChild(errorSpan);
    }

    if (hasErrors) {
        e.preventDefault();
    }
});
</script>

</div><!-- /admin-page -->

<?php require_once __DIR__ . '/../template_only/layouts/admin_footer.php'; ?>
