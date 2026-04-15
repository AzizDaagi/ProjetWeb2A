<?php
$pageTitle = 'Détails de l\'Aliment';
require_once __DIR__ . '/../../controler/AlimentController.php';

$controller = new AlimentController();
$aliment = null;

if (isset($_GET['id'])) {
    $aliment = $controller->getAliment($_GET['id']);
}

require_once __DIR__ . '/../template_only/layouts/header.php';
?>

<div class="container user-dashboard" style="padding-top: 40px;">
    <?php if ($aliment): ?>
        <div style="background: var(--card-bg, white); border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 800px; margin: 0 auto;">
            
            <a href="liste_recettes.php" style="margin-bottom: 25px; display: inline-block; background: #6c757d; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                <i class="fa-solid fa-arrow-left"></i> Retour à la liste
            </a>
            
            <h1 style="font-size: 42px; margin-bottom: 10px; color: var(--text-color, #333);"><?= htmlspecialchars((string) $aliment['nom']) ?></h1>
            
            <?php if (!empty($aliment['image_url'])): ?>
            <div style="width: 100%; max-height: 400px; border-radius: 12px; overflow: hidden; margin: 25px 0; background: #e9ecef;">
                <img src="<?= htmlspecialchars((string) $aliment['image_url']) ?>" alt="Image de <?= htmlspecialchars((string) $aliment['nom']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: var(--text-color, #333);"><i class="fa-solid fa-fire" style="color: #dc3545;"></i> Apport Énergétique</h3>
                <p style="font-size: 18px; color: var(--text-color, #555);">Calories : <strong><?= htmlspecialchars((string) $aliment['calories']) ?> kcal</strong> pour 100g.</p>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: var(--text-color, #333);"><i class="fa-solid fa-tag" style="color: #28a745;"></i> Catégorie</h3>
                <p style="font-size: 18px; color: var(--text-color, #555); font-weight: bold; background: #d4edda; color: #155724; display: inline-block; padding: 10px 20px; border-radius: 8px;">
                    <?= htmlspecialchars((string) $aliment['type']) ?>
                </p>
            </div>
            
            <div>
                <h3 style="color: var(--text-color, #333);"><i class="fa-solid fa-flask" style="color: #17a2b8;"></i> Macronutriments (pour 100g)</h3>
                <div style="font-size: 16px; line-height: 1.6; color: var(--text-color, #555); background: var(--bg-color, #f8f9fa); padding: 25px; border-radius: 8px; border-left: 5px solid #17a2b8;">
                    <ul style="list-style-type: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: 10px;"><strong>Protéines :</strong> <?= htmlspecialchars((string) $aliment['proteines']) ?> g</li>
                        <li style="margin-bottom: 10px;"><strong>Glucides :</strong> <?= htmlspecialchars((string) $aliment['glucides']) ?> g</li>
                        <li style="margin-bottom: 0;"><strong>Lipides :</strong> <?= htmlspecialchars((string) $aliment['lipides']) ?> g</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <h2 style="color: #dc3545;">Aliment introuvable.</h2>
            <p>Cet aliment n'existe pas ou a été supprimé.</p>
            <a href="liste_recettes.php" class="btn" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px;">Retourner à la liste</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
