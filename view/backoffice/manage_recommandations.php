<?php
$pageTitle = 'Options: Règles & Recommandations';
require_once __DIR__ . '/../../controler/RecommandationController.php';

$controller = new RecommandationController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $controller->addRecommandation($_POST['titre'], $_POST['type_objectif'], $_POST['contenu_regle']);
        } elseif ($_POST['action'] === 'delete') {
            $controller->deleteRecommandation($_POST['id']);
        }
        header('Location: manage_recommandations.php');
        exit;
    }
}

$recommandations = $controller->listRecommandations();
require_once __DIR__ . '/../template_only/layouts/admin_header.php'; 
?>

<div class="admin-page">
    <div class="admin-page-head">
        <h1><i class="fa-solid fa-heart-pulse icon"></i> Recommandations</h1>
        <p class="subtitle">Créer et gérer les recommandations nutritionnelles.</p>
    </div>
    
    <div class="submit-page-wrapper">
    
    <h1>Créer une Recommandation Nutritionnelle</h1>
    <form method="POST" action="" id="reco-form" novalidate>
        <input type="hidden" name="action" value="add">
        
        <div class="form-group">
            <label>Titre de la Règle</label>
            <input type="text" name="titre" id="titre-input">
        </div>
        <div class="form-group">
            <label>Type d'Objectif</label>
            <select name="type_objectif" id="type-input" style="width: 100%; padding: 10px;">
                <option value="Perte de poids">Perte de poids</option>
                <option value="Prise de masse">Prise de masse</option>
                <option value="Maintien">Maintien</option>
                <option value="Santé Globale">Santé Globale</option>
            </select>
        </div>
        <div class="form-group">
            <label>Contenu / Explication</label>
            <textarea name="contenu_regle" id="contenu-input" style="min-height: 100px;"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Ajouter Règle</button>
    </form>

    <h2 style="margin-top: 40px;">Liste des Recommandations</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Objectif</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recommandations as $r): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $r['titre']) ?></td>
                    <td><span style="background: #17a2b8; color: white; padding: 5px; border-radius: 4px;"><?= htmlspecialchars((string) $r['type_objectif']) ?></span></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('reco-form').addEventListener('submit', function(e) {
    let titre = document.getElementById('titre-input').value.trim();
    let type = document.getElementById('type-input').value;
    let contenu = document.getElementById('contenu-input').value.trim();
    
    let hasErrors = false;

    // Reset previous errors dynamically
    document.querySelectorAll('.error-msg').forEach(el => el.remove());
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
        el.style.border = '1px solid #ddd'; // Default border
    });

    function showError(inputId, message) {
        hasErrors = true;
        let inputEl = document.getElementById(inputId);
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

    if (titre === "") {
        showError('titre-input', "Veuillez entrer un titre.");
    }
    if (type === "") {
        showError('type-input', "Veuillez sélectionner un type d'objectif.");
    }
    if (contenu === "") {
        showError('contenu-input', "Veuillez fournir un contenu/explication.");
    }

    if (hasErrors) {
        e.preventDefault();
    }
});
</script>

    </div>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/admin_footer.php'; ?>
