<?php
$pageTitle = 'Détails de la Recette';
require_once __DIR__ . '/../../controler/RecetteController.php';

$controller = new RecetteController();
$recette = null;
$aliments_associes = [];

if (isset($_GET['id'])) {
    $recette = $controller->getRecette($_GET['id']);
    if ($recette) {
        $aliments_associes = $controller->getAlimentsByRecette($recette['id']);
    }
}

require_once __DIR__ . '/../template_only/layouts/header.php';
?>

<div class="container user-dashboard" style="padding-top: 40px;">
    <?php if ($recette): ?>
        <div style="background: var(--card-bg, white); border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 800px; margin: 0 auto;">
            <a href="liste_recettes.php" style="margin-bottom: 20px; display: inline-block; background: #6c757d; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                <i class="fa-solid fa-arrow-left"></i> Retour à la liste
            </a>
            <h1 style="font-size: 42px; margin-bottom: 10px; color: var(--text-color, #333);"><?= htmlspecialchars((string) $recette['nom']) ?></h1>
            
            <?php if (!empty($recette['image_url'])): ?>
            <div style="width: 100%; max-height: 400px; border-radius: 12px; overflow: hidden; margin: 25px 0; background: #e9ecef;">
                <img src="<?= htmlspecialchars((string) $recette['image_url']) ?>" alt="Image de <?= htmlspecialchars((string) $recette['nom']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <?php endif; ?>
            
            <div style="display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap;">
                <div style="background: #e9ecef; color: #495057; padding: 15px 25px; border-radius: 12px; font-weight: bold; font-size: 18px; flex: 1; text-align: center;">
                    <i class="fa-solid fa-clock" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                    <?= htmlspecialchars((string) $recette['temps_preparation']) ?>
                </div>
                <div style="background: #fff3cd; color: #856404; padding: 15px 25px; border-radius: 12px; font-weight: bold; font-size: 18px; flex: 1; text-align: center;">
                    <i class="fa-solid fa-chart-bar" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                    <?= htmlspecialchars((string) $recette['niveau_difficulte']) ?>
                </div>
            </div>

            <!-- Nouvelle section des Aliments Associés (Table Pivot) -->
            <div style="margin-bottom: 40px; padding: 25px; background: #f8f9fa; border-radius: 12px; border-left: 5px solid #28a745;">
                <h3 style="margin-top: 0; color: #28a745;"><i class="fa-solid fa-basket-shopping"></i> Ingrédients / Aliments requis</h3>
                <?php if (!empty($aliments_associes)): ?>
                    <ul style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                        <?php foreach ($aliments_associes as $a): ?>
                            <li style="background: white; padding: 10px 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-weight: bold; color: #333;"><?= htmlspecialchars((string) $a['nom']) ?></span>
                                <span style="font-size: 12px; background: #e9ecef; padding: 3px 8px; border-radius: 12px;"><?= htmlspecialchars((string) $a['calories']) ?> kcal</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #666; margin: 0;">Aucun aliment spécifique référencé pour cette recette.</p>
                <?php endif; ?>
            </div>
            
            <div>
                <h3 style="color: var(--text-color, #333);"><i class="fa-solid fa-list-check" style="color: #007bff;"></i> Préparation (Étapes)</h3>
                <div style="font-size: 18px; line-height: 1.8; color: var(--text-color, #555);">
                    <?= nl2br(htmlspecialchars((string) $recette['description'])) ?>
                </div>
            </div>

        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <h2 style="color: #dc3545;">Recette introuvable.</h2>
            <p>La recette que vous cherchez n'existe pas ou a été supprimée.</p>
            <a href="liste_recettes.php" class="btn" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px;">Retourner à la liste</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
