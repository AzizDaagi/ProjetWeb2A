<?php
$pageTitle = 'Gestion des Recettes';
require_once __DIR__ . '/../../controler/RecetteController.php';
require_once __DIR__ . '/../../controler/AlimentController.php';

$controller = new RecetteController();
$alimentController = new AlimentController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Collect checked aliments
        $aliments_ids = isset($_POST['aliments']) ? $_POST['aliments'] : [];

        if ($_POST['action'] === 'add') {
            $controller->addRecette($_POST['nom'], $_POST['description'], $_POST['temps_preparation'], $_POST['niveau_difficulte'], $_POST['image_url'] ?? null, $aliments_ids);
        } elseif ($_POST['action'] === 'update') {
            $controller->updateRecette($_POST['id'], $_POST['nom'], $_POST['description'], $_POST['temps_preparation'], $_POST['niveau_difficulte'], $_POST['image_url'] ?? null, $aliments_ids);
        } elseif ($_POST['action'] === 'delete') {
            $controller->deleteRecette($_POST['id']);
        }
        header('Location: manage_recettes.php');
        exit;
    }
}

$recettes = $controller->listRecettes();
$tous_aliments = $alimentController->listAliments();

// Create a mapping of recipes to their aliments for filling the edit form
$recettes_aliments_map = [];
foreach ($recettes as $r) {
    $assoc_aliments = $controller->getAlimentsByRecette($r['id']);
    $recettes_aliments_map[$r['id']] = array_map(function($a) { return $a['id']; }, $assoc_aliments);
}

require_once __DIR__ . '/../template_only/layouts/header.php'; 
?>

<div class="container admin-dashboard">
    <a href="index.php" class="btn" style="background: #6c757d; color: white; text-decoration: none; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> Retour Dashboard
    </a>
    <h1 id="form-title">Créer une Recette</h1>
    <form method="POST" action="" id="recette-form">
        <input type="hidden" name="action" id="action-input" value="add">
        <input type="hidden" name="id" id="form-id">
        
        <div class="form-group">
            <label>Nom de la recette</label>
            <input type="text" name="nom" id="nom-input" required minlength="3" maxlength="200" pattern="[a-zA-ZÀ-ÿ\s\-]+" title="Veuillez utiliser uniquement des lettres, espaces ou tirets." oninput="this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '')">
        </div>

        <div class="form-group">
            <label>Sélectionnez les Aliments de la Recette</label>
            <div style="background: var(--input-bg, white); border: 1px solid #ddd; border-radius: 6px; padding: 15px; max-height: 200px; overflow-y: auto;">
                <?php if (!empty($tous_aliments)): ?>
                    <?php foreach ($tous_aliments as $al): ?>
                        <label style="display: block; cursor: pointer; margin-bottom: 8px; font-weight: normal;">
                            <input type="checkbox" name="aliments[]" class="aliment-checkbox" value="<?= $al['id'] ?>" style="margin-right: 10px;">
                            <?= htmlspecialchars((string) $al['nom']) ?> (<?= htmlspecialchars((string) $al['calories']) ?> kcal)
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #777; margin: 0;">Aucun aliment disponible. Allez en créer un d'abord !</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label>Description (Étapes)</label>
            <textarea name="description" id="desc-input" required minlength="10" placeholder="Décrivez les étapes de la recette..." style="min-height: 120px;" title="Veuillez détailler avec au minimum 10 caractères."></textarea>
        </div>
        
        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Temps de préparation</label>
                <input type="text" name="temps_preparation" id="temps-input" required placeholder="Ex: 30 minutes, 1h..." pattern="[a-zA-Z0-9\s]+" title="Veuillez entrer une durée (ex: 30 minutes)">
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Niveau de difficulté</label>
                <select name="niveau_difficulte" id="diff-input" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ddd; background: var(--input-bg, white); color: var(--text-color, #333);">
                    <option value="">Sélectionnez un niveau</option>
                    <option value="Très Facile">Très Facile</option>
                    <option value="Facile">Facile</option>
                    <option value="Moyen">Moyen</option>
                    <option value="Difficile">Difficile</option>
                    <option value="Expert">Expert</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>URL de l'image (optionnel)</label>
            <input type="url" name="image_url" id="image-input" placeholder="https://exemple.com/image.jpg" pattern="https?://.+" title="Veuillez entrer une URL valide commençant par http:// ou https://">
        </div>
        
        <button type="submit" id="submit-btn" class="btn btn-primary">Créer la recette</button>
        <button type="button" class="btn btn-warning" id="cancel-btn" style="display:none;" onclick="cancelEdit()">Annuler</button>
    </form>

    <h2 style="margin-top: 50px;">Liste des Recettes</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Temps</th>
                <th>Difficulté</th>
                <th>Aliments Associés</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recettes as $recette): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $recette['nom']) ?></td>
                    <td><?= htmlspecialchars((string) $recette['temps_preparation']) ?></td>
                    <td><?= htmlspecialchars((string) $recette['niveau_difficulte']) ?></td>
                    <td><?= count($recettes_aliments_map[$recette['id']]) ?> aliment(s)</td>
                    <td>
                        <button type="button" class="btn btn-warning" onclick="editRecette(<?= htmlspecialchars((string) $recette['id']) ?>, '<?= addslashes(htmlspecialchars((string) $recette['nom'])) ?>', '<?= addslashes(htmlspecialchars((string) $recette['description'])) ?>', '<?= addslashes(htmlspecialchars((string) $recette['temps_preparation'])) ?>', '<?= addslashes(htmlspecialchars((string) $recette['niveau_difficulte'])) ?>', '<?= addslashes(htmlspecialchars((string) ($recette['image_url'] ?? ''))) ?>', <?= json_encode($recettes_aliments_map[$recette['id']]) ?>)">Modifier</button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $recette['id'] ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette recette ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function editRecette(id, nom, desc, temps, diff, image_url, alimentsArray) {
    document.getElementById('form-id').value = id;
    document.getElementById('action-input').value = 'update';
    document.getElementById('nom-input').value = nom;
    document.getElementById('desc-input').value = desc;
    document.getElementById('temps-input').value = temps;
    document.getElementById('diff-input').value = diff;
    document.getElementById('image-input').value = image_url;
    
    // Cocher les aliments associés
    const checkboxes = document.querySelectorAll('.aliment-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = alimentsArray.includes(parseInt(cb.value));
    });

    document.getElementById('submit-btn').innerText = 'Sauvegarder';
    document.getElementById('form-title').innerText = 'Modifier la recette';
    document.getElementById('cancel-btn').style.display = 'inline-block';
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelEdit() {
    document.getElementById('recette-form').reset();
    document.getElementById('form-id').value = '';
    document.getElementById('action-input').value = 'add';
    
    document.getElementById('submit-btn').innerText = 'Créer la recette';
    document.getElementById('form-title').innerText = 'Créer une Recette';
    document.getElementById('cancel-btn').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
