<?php
$pageTitle = 'Smart Nutrition | Gestion des Recettes';
require_once __DIR__ . '/../../controler/RecetteController.php';
require_once __DIR__ . '/../../controler/AlimentController.php';

$controller      = new RecetteController();
$alimentController = new AlimentController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $aliments_ids = isset($_POST['aliments']) ? $_POST['aliments'] : [];
        $image_url = $_POST['existing_image_url'] ?? null;

        // Handle File Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/recettes/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image_url = '/projetwebmalek/view/uploads/recettes/' . $fileName;
            }
        }

        if ($_POST['action'] === 'add') {
            $controller->addRecette(
                $_POST['nom'], $_POST['description'], $_POST['temps_preparation'],
                $_POST['niveau_difficulte'], $image_url, $aliments_ids
            );
        } elseif ($_POST['action'] === 'update') {
            $controller->updateRecette(
                $_POST['id'], $_POST['nom'], $_POST['description'], $_POST['temps_preparation'],
                $_POST['niveau_difficulte'], $image_url, $aliments_ids
            );
        } elseif ($_POST['action'] === 'delete') {
            $controller->deleteRecette($_POST['id']);
        }
        header('Location: manage_recettes.php');
        exit;
    }
}

$recettes = $controller->listRecettes();
$tous_aliments = $alimentController->listAliments();

$recettes_aliments_map = [];
foreach ($recettes as $r) {
    $assoc = $controller->getAlimentsByRecette($r['id']);
    $recettes_aliments_map[$r['id']] = array_map(fn($a) => $a['id'], $assoc);
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

    <a href="index.php" class="submit-back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour au Dashboard
    </a>

    <div class="submit-form-card">
        <h1 id="form-title" style="margin:0 0 24px;font-size:22px;font-weight:800;">
            <?php if ($recetteToEdit): ?>
                <i class="fa-solid fa-pen-to-square" style="color:#f39c12;margin-right:8px;"></i> Modifier la recette
            <?php else: ?>
                <i class="fa-solid fa-plus-circle" style="color:#2ecc71;margin-right:8px;"></i> Créer une Recette
            <?php endif; ?>
        </h1>

        <form method="POST" action="manage_recettes.php" id="recette-form" enctype="multipart/form-data" novalidate>
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
                                if ($recetteToEdit && isset($recettes_aliments_map[$recetteToEdit['id']])) {
                                    if (in_array($al['id'], $recettes_aliments_map[$recetteToEdit['id']])) {
                                        $isChecked = true;
                                    }
                                }
                            ?>
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:10px;font-weight:normal;color:rgba(236,240,241,0.8);font-size:14px;">
                                <input type="checkbox" name="aliments[]" class="aliment-checkbox"
                                       value="<?= $al['id'] ?>"
                                       <?= $isChecked ? 'checked' : '' ?>
                                       style="width:16px;height:16px;flex-shrink:0;cursor:pointer;">
                                <?= htmlspecialchars((string)$al['nom']) ?>
                                <span class="product-card-badge badge-green" style="margin-left:auto;">
                                    <?= htmlspecialchars((string)$al['calories']) ?> kcal
                                </span>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:rgba(236,240,241,0.4);margin:0;font-size:13px;">
                            Aucun aliment disponible. <a href="manage_aliments.php" style="color:#2ecc71;">Créez-en un d'abord.</a>
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
                    <label for="diff-input">Niveau de difficulté</label>
                    <select name="niveau_difficulte" id="diff-input">
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
                <a href="manage_recettes.php" class="submit-btn-cancel" style="display:inline-flex; text-decoration:none; justify-content:center; align-items:center;">
                    <i class="fa-solid fa-xmark"></i> Annuler
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

</div>
<?php require_once __DIR__ . '/../template_only/layouts/admin_footer.php'; ?>
