<?php
$pageTitle = 'Gestion des Aliments';
require_once __DIR__ . '/../../controler/AlimentController.php';

$controller = new AlimentController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $controller->addAliment($_POST['nom'], $_POST['calories'], $_POST['proteines'], $_POST['glucides'], $_POST['lipides'], $_POST['type'], $_POST['image_url'] ?? null);
        } elseif ($_POST['action'] === 'update') {
            $controller->updateAliment($_POST['id'], $_POST['nom'], $_POST['calories'], $_POST['proteines'], $_POST['glucides'], $_POST['lipides'], $_POST['type'], $_POST['image_url'] ?? null);
        } elseif ($_POST['action'] === 'delete') {
            $controller->deleteAliment($_POST['id']);
        }
        // Redirect to avoid resubmission
        header('Location: manage_aliments.php');
        exit;
    }
}

$aliments = $controller->listAliments();
require_once __DIR__ . '/../template_only/layouts/header.php'; 
?>

<div class="container admin-dashboard">
    <a href="index.php" class="btn" style="background: #6c757d; color: white; text-decoration: none; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> Retour Dashboard
    </a>
    <h1 id="form-title">Créer un Aliment</h1>
    <form method="POST" action="">
        <input type="hidden" name="action" id="action-input" value="add">
        <input type="hidden" name="id" id="form-id">
        
        <div class="form-group">
            <label>Nom de l'aliment</label>
            <input type="text" name="nom" id="nom-input" required minlength="2" maxlength="100" pattern="[a-zA-ZÀ-ÿ\s\-]+" title="Uniquement des lettres, espaces et tirets." oninput="this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '')">
        </div>
        <div class="form-group">
            <label>Type d'aliment</label>
            <select name="type" id="type-input" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ddd; background: var(--input-bg, white); color: var(--text-color, #333);">
                <option value="">Sélectionnez un type</option>
                <option value="Fruit">Fruit</option>
                <option value="Légume">Légume</option>
                <option value="Viande">Viande</option>
                <option value="Poisson">Poisson</option>
                <option value="Produit Laitier">Produit Laitier</option>
                <option value="Céréale">Céréale</option>
                <option value="Autre">Autre</option>
            </select>
        </div>
        <div class="form-group">
            <label>Calories (kcal pour 100g)</label>
            <input type="number" name="calories" id="cal-input" required min="0" max="1000" title="Valeur calorique entre 0 et 1000">
        </div>
        
        <div style="display: flex; gap: 15px; margin-bottom: 20px;">
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Protéines (g)</label>
                <input type="number" step="0.1" name="proteines" id="prot-input" required min="0" max="100">
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Glucides (g)</label>
                <input type="number" step="0.1" name="glucides" id="glu-input" required min="0" max="100">
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Lipides (g)</label>
                <input type="number" step="0.1" name="lipides" id="lip-input" required min="0" max="100">
            </div>
        </div>

        <div class="form-group">
            <label>URL de l'image (optionnel)</label>
            <input type="url" name="image_url" id="image-input" placeholder="https://exemple.com/image.jpg" pattern="https?://.+" title="Veuillez entrer une URL valide commençant par http:// ou https://">
        </div>
        
        <button type="submit" id="submit-btn" class="btn btn-primary">Ajouter</button>
        <button type="button" class="btn btn-warning" id="cancel-btn" style="display:none;" onclick="cancelEdit()">Annuler</button>
    </form>

    <h2 style="margin-top: 50px;">Liste des Aliments</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Calories</th>
                <th>Macro (P/G/L)</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($aliments as $aliment): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $aliment['nom']) ?></td>
                    <td><?= htmlspecialchars((string) $aliment['calories']) ?> kcal</td>
                    <td><?= htmlspecialchars($aliment['proteines']) ?>g / <?= htmlspecialchars($aliment['glucides']) ?>g / <?= htmlspecialchars($aliment['lipides']) ?>g</td>
                    <td><?= htmlspecialchars((string) $aliment['type']) ?></td>
                    <td>
                        <button type="button" class="btn btn-warning" onclick="editAliment(<?= htmlspecialchars((string) $aliment['id']) ?>, '<?= addslashes(htmlspecialchars((string) $aliment['nom'])) ?>', '<?= addslashes(htmlspecialchars((string) $aliment['calories'])) ?>', '<?= addslashes(htmlspecialchars((string) $aliment['proteines'])) ?>', '<?= addslashes(htmlspecialchars((string) $aliment['glucides'])) ?>', '<?= addslashes(htmlspecialchars((string) $aliment['lipides'])) ?>', '<?= addslashes(htmlspecialchars((string) $aliment['type'])) ?>', '<?= addslashes(htmlspecialchars((string) ($aliment['image_url'] ?? ''))) ?>')">Modifier</button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $aliment['id'] ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet aliment ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function editAliment(id, nom, cal, prot, glu, lip, type, image_url) {
    document.getElementById('form-id').value = id;
    document.getElementById('action-input').value = 'update';
    document.getElementById('nom-input').value = nom;
    document.getElementById('cal-input').value = cal;
    document.getElementById('prot-input').value = prot;
    document.getElementById('glu-input').value = glu;
    document.getElementById('lip-input').value = lip;
    document.getElementById('type-input').value = type;
    document.getElementById('image-input').value = image_url;
    
    document.getElementById('submit-btn').innerText = 'Sauvegarder';
    document.getElementById('form-title').innerText = 'Modifier l\'aliment';
    document.getElementById('cancel-btn').style.display = 'inline-block';
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelEdit() {
    document.getElementById('form-id').value = '';
    document.getElementById('action-input').value = 'add';
    document.getElementById('nom-input').value = '';
    document.getElementById('cal-input').value = '';
    document.getElementById('prot-input').value = '';
    document.getElementById('glu-input').value = '';
    document.getElementById('lip-input').value = '';
    document.getElementById('type-input').value = '';
    document.getElementById('image-input').value = '';
    
    document.getElementById('submit-btn').innerText = 'Ajouter';
    document.getElementById('form-title').innerText = 'Créer un Aliment';
    document.getElementById('cancel-btn').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
