<?php
$baseUrl = $baseUrl ?? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$pageTitle = 'Smart Nutrition | Gestion des Aliments';
require_once __DIR__ . '/../../controller/AlimentController.php';

$controller = new AlimentController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $image_url = $_POST['existing_image_url'] ?? null;

        // Handle File Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/aliments/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image_url = $baseUrl . '/view/uploads/aliments/' . $fileName;
            }
        }

        if ($_POST['action'] === 'add') {
            $controller->addAliment(
                $_POST['nom'], $_POST['calories'], $_POST['proteines'],
                $_POST['glucides'], $_POST['lipides'], $_POST['fibres'], $_POST['type'],
                $image_url
            );
        } elseif ($_POST['action'] === 'update') {
            $controller->updateAliment(
                $_POST['id'], $_POST['nom'], $_POST['calories'], $_POST['proteines'],
                $_POST['glucides'], $_POST['lipides'], $_POST['fibres'], $_POST['type'],
                $image_url
            );
        } elseif ($_POST['action'] === 'delete') {
            $controller->deleteAliment($_POST['id']);
        }
        header('Location: ' . $baseUrl . '/index.php?action=admin-aliments');
        exit;
    }
}

$aliments = $controller->listAliments();

// Edit Mode Logic
$alimentToEdit = null;
if (isset($_GET['edit_id'])) {
    foreach ($aliments as $al) {
        if ($al['id'] == $_GET['edit_id']) {
            $alimentToEdit = $al;
            break;
        }
    }
}

require_once __DIR__ . '/../template_only/layouts/admin_header.php';
?>

<div class="admin-page">


<!-- ================= SUBMIT FORM SECTION ================= -->
<div class="submit-page-wrapper">

    <p class="submit-page-intro">
        <i class="fa-solid fa-circle-info"></i>
        Gérez vos aliments ici. Les modifications sont appliquées immédiatement.
    </p>

    <!-- Animated gradient back button -->
    <a href="<?= $baseUrl ?>/index.php?action=admin-dashboard" class="submit-back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour au Dashboard
    </a>

    <!-- Form Card -->
    <div class="submit-form-card">
        <h1 id="form-title" style="margin:0 0 24px;font-size:22px;font-weight:800;">
            <?php if ($alimentToEdit): ?>
                <i class="fa-solid fa-pen-to-square" style="color:#f39c12;margin-right:8px;"></i> Modifier l'aliment
            <?php else: ?>
                <i class="fa-solid fa-plus-circle" style="color:#2ecc71;margin-right:8px;"></i> Créer un Aliment
            <?php endif; ?>
        </h1>

        <form method="POST" action="<?= $baseUrl ?>/index.php?action=admin-aliments" id="aliment-form" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="action" id="action-input" value="<?= $alimentToEdit ? 'update' : 'add' ?>">
            <input type="hidden" name="id"     id="form-id" value="<?= $alimentToEdit ? htmlspecialchars($alimentToEdit['id']) : '' ?>">
            <input type="hidden" name="existing_image_url" value="<?= $alimentToEdit ? htmlspecialchars($alimentToEdit['image_url'] ?? '') : '' ?>">

            <!-- Nom -->
            <div class="form-group">
                <label for="nom-input">Nom de l'aliment</label>
                <input type="text" name="nom" id="nom-input" placeholder="ex: Poulet grillé" value="<?= $alimentToEdit ? htmlspecialchars($alimentToEdit['nom']) : '' ?>">
            </div>

            <!-- Type -->
            <div class="form-group">
                <label for="type-input">Type d'aliment (macro dominante)</label>
                <select name="type" id="type-input">
                    <option value="">Sélectionnez un type</option>
                    <?php 
                    $types = [
                        'proteine' => '🥩 Protéine',
                        'glucide'  => '🌾 Glucide',
                        'lipide'   => '🥑 Lipide',
                    ];
                    foreach($types as $val => $label) {
                        $selected = ($alimentToEdit && $alimentToEdit['type'] === $val) ? 'selected' : '';
                        echo "<option value=\"$val\" $selected>$label</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Calories -->
            <div class="form-group">
                <label for="cal-input">Calories (kcal / 100g)</label>
                <input type="number" name="calories" id="cal-input" placeholder="ex: 165" value="<?= $alimentToEdit ? htmlspecialchars($alimentToEdit['calories']) : '' ?>">
            </div>

            <!-- Macros row -->
            <div class="submit-form-row">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="prot-input">Protéines (g)</label>
                    <input type="number" step="0.1" name="proteines" id="prot-input" placeholder="31.0" value="<?= $alimentToEdit ? htmlspecialchars($alimentToEdit['proteines']) : '' ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="glu-input">Glucides (g)</label>
                    <input type="number" step="0.1" name="glucides" id="glu-input" placeholder="0.0" value="<?= $alimentToEdit ? htmlspecialchars($alimentToEdit['glucides']) : '' ?>">
                </div>
            </div>
            <div class="submit-form-row" style="margin-top:16px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="lip-input">Lipides (g)</label>
                    <input type="number" step="0.1" name="lipides" id="lip-input" placeholder="3.6" value="<?= $alimentToEdit ? htmlspecialchars($alimentToEdit['lipides']) : '' ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="fib-input">Fibres (g)</label>
                    <input type="number" step="0.1" name="fibres" id="fib-input" placeholder="2.0" value="<?= $alimentToEdit ? htmlspecialchars($alimentToEdit['fibres'] ?? '0') : '' ?>">
                </div>
            </div>

            <!-- Image File Upload -->
            <div class="form-group" style="margin-top:20px;">
                <label for="image-input">Photo de l'aliment (PC)</label>
                <?php if ($alimentToEdit && !empty($alimentToEdit['image_url'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= htmlspecialchars($alimentToEdit['image_url']) ?>" alt="Actuelle" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2);">
                        <span style="font-size: 12px; color: rgba(236,240,241,0.6); display: block;">Image actuelle</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="image" id="image-input" accept="image/*" style="padding: 10px; background: rgba(0,0,0,0.2); border: 1px dashed rgba(255,255,255,0.3); border-radius: 8px; width: 100%; color: white;">
            </div>

            <!-- Submit -->
            <button type="submit" id="submit-btn" class="submit-btn">
                <?php if ($alimentToEdit): ?>
                    <i class="fa-solid fa-floppy-disk"></i> Sauvegarder
                <?php else: ?>
                    <i class="fa-solid fa-paper-plane"></i> Ajouter l'aliment
                <?php endif; ?>
            </button>
            <?php if ($alimentToEdit): ?>
                <a href="<?= $baseUrl ?>/index.php?action=admin-aliments" class="submit-btn-cancel" style="display:inline-flex; text-decoration:none; justify-content:center; align-items:center;">
                    <i class="fa-solid fa-xmark"></i> Annuler
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>
</div>

<script>
document.getElementById('aliment-form').addEventListener('submit', function(e) {
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
        showError('nom-input', "Veuillez entrer le nom de l'aliment.");
    } else if (nom.length < 3) {
        showError('nom-input', "Le nom doit contenir au moins 3 caractères.");
    }

    let type = document.getElementById('type-input').value;
    if (type === "") {
        showError('type-input', "Veuillez sélectionner un type d'aliment.");
    }

    let calories = document.getElementById('cal-input').value.trim();
    if (calories === "") {
        showError('cal-input', "Veuillez entrer les calories.");
    } else if (isNaN(calories) || parseFloat(calories) < 0) {
        showError('cal-input', "Les calories doivent être un nombre positif.");
    }

    let proteines = document.getElementById('prot-input').value.trim();
    if (proteines === "") {
        showError('prot-input', "Veuillez entrer les protéines.");
    } else if (isNaN(proteines) || parseFloat(proteines) < 0) {
        showError('prot-input', "Les protéines doivent être un nombre positif.");
    }

    let glucides = document.getElementById('glu-input').value.trim();
    if (glucides === "") {
        showError('glu-input', "Veuillez entrer les glucides.");
    } else if (isNaN(glucides) || parseFloat(glucides) < 0) {
        showError('glu-input', "Les glucides doivent être un nombre positif.");
    }

    let lipides = document.getElementById('lip-input').value.trim();
    if (lipides === "") {
        showError('lip-input', "Veuillez entrer les lipides.");
    } else if (isNaN(lipides) || parseFloat(lipides) < 0) {
        showError('lip-input', "Les lipides doivent être un nombre positif.");
    }

    let fibres = document.getElementById('fib-input').value.trim();
    if (fibres === "") {
        showError('fib-input', "Veuillez entrer les fibres.");
    } else if (isNaN(fibres) || parseFloat(fibres) < 0) {
        showError('fib-input', "Les fibres doivent être un nombre positif.");
    }

    let imageInput = document.getElementById('image-input');
    if (action === 'add' && imageInput.files.length === 0) {
        showError('image-input', "Veuillez sélectionner une image pour le nouvel aliment.");
    }

    if (hasErrors) {
        e.preventDefault();
    }
});
</script>

<?php require_once __DIR__ . '/../template_only/layouts/admin_footer.php'; ?>
