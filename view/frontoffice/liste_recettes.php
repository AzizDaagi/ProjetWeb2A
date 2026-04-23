<?php
$pageTitle = 'Nos Recettes';
require_once __DIR__ . '/../../controler/RecetteController.php';
require_once __DIR__ . '/../../controler/AlimentController.php';

$controller = new RecetteController();
$alimentController = new AlimentController();
try {
    $recettes = $controller->listRecettes();
    $aliments = $alimentController->listAliments();
} catch (Exception $e) {
    $recettes = [];
    $aliments = [];
}

require_once __DIR__ . '/../template_only/layouts/header.php';
?>

<div class="hero-wrapper">
    <div class="cycle-diagram">
        <svg class="orbit-ring" viewBox="0 0 400 400">
            <circle cx="200" cy="200" r="140" class="ring-track" />
            <circle cx="200" cy="200" r="140" class="ring-glow" />
        </svg>
        
        <div class="node node-1" title="Sustainability">
            <div class="node-icon"><i class="fa-solid fa-leaf"></i></div>
            <span class="node-label">Sustainability</span>
        </div>
        <div class="node node-2" title="Healthy Food">
            <div class="node-icon"><i class="fa-solid fa-apple-whole"></i></div>
            <span class="node-label">Healthy Food</span>
        </div>
        <div class="node node-3" title="Lifestyle">
            <div class="node-icon"><i class="fa-solid fa-person-running"></i></div>
            <span class="node-label">Lifestyle</span>
        </div>
        <div class="node node-4" title="Smart Nutrition">
            <div class="node-icon"><i class="fa-solid fa-utensils"></i></div>
            <span class="node-label">Nutrition</span>
        </div>

        <div class="center-piece">
            <div class="pulse-core"></div>
            <h3>Smart<br>System</h3>
        </div>
    </div>

    <div class="hero-content">
        <h1>Smart Nutrition</h1>
        <p class="subtitle-text">Sustainable & Intelligent Food System</p>
        <p class="description-text">
            Bienvenue sur votre assistant nutritionnel personnel.<br>
            Analysez, suivez et optimisez votre alimentation en temps réel.
        </p>
    </div>
</div>

<div class="container user-dashboard" style="margin-top: -40px; position: relative; z-index: 10;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h1 style="margin: 0;"><i class="fa-solid fa-utensils"></i> Liste des Recettes</h1>
        <div>
            <a href="/projetwebmalek/view/backoffice/manage_recettes.php" class="btn" style="background: #007bff; color: white; padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-right: 10px;">
                <i class="fa-solid fa-plus"></i> Créer recettes
            </a>
            <a href="/projetwebmalek/view/backoffice/manage_aliments.php" class="btn" style="background: #28a745; color: white; padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: bold;">
                <i class="fa-solid fa-plus"></i> Nouveau aliments
            </a>
        </div>
    </div>

    <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php if (!empty($recettes)): ?>
            <?php foreach ($recettes as $r): ?>
                <a href="details_recette.php?id=<?= $r['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                <div class="card" style="background: var(--card-bg, white); border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s;">
                    <?php if (!empty($r['image_url'])): ?>
                    <div style="width: 100%; height: 200px; border-radius: 8px; margin-bottom: 15px; overflow: hidden; background: #e9ecef;">
                        <img src="<?= htmlspecialchars((string) $r['image_url']) ?>" alt="Image de <?= htmlspecialchars((string) $r['nom']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <?php endif; ?>
                    <h3 style="margin-top: 0; color: var(--text-color, #333);"><?= htmlspecialchars((string) $r['nom']) ?></h3>
                    
                    <div style="margin: 15px 0;">
                        <span style="display: inline-block; background: #e9ecef; color: #495057; padding: 4px 10px; border-radius: 15px; font-size: 14px; font-weight: bold; margin-right: 10px;">
                            <i class="fa-solid fa-clock"></i> <?= htmlspecialchars((string) $r['temps_preparation']) ?>
                        </span>
                        <span style="display: inline-block; background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 15px; font-size: 14px; font-weight: bold;">
                            <i class="fa-solid fa-chart-bar"></i> <?= htmlspecialchars((string) $r['niveau_difficulte']) ?>
                        </span>
                    </div>

                    <span style="display: inline-block; margin-top: 10px; background: #28a745; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold;">
                        Voir les détails <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center; font-size: 18px; color: #777;">Aucune recette n'a été ajoutée pour le moment. Soyez le premier !</p>
        <?php endif; ?>
    </div>

    <!-- Section Aliments -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 60px; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h1 style="margin: 0;"><i class="fa-solid fa-apple-whole"></i> Nos Aliments de Qualité</h1>
    </div>

    <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php if (!empty($aliments)): ?>
            <?php foreach ($aliments as $a): ?>
                <a href="details_aliment.php?id=<?= $a['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                <div class="card" style="background: var(--card-bg, white); border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s;">
                    <?php if (!empty($a['image_url'])): ?>
                    <div style="width: 100%; height: 200px; border-radius: 8px; margin-bottom: 15px; overflow: hidden; background: #e9ecef;">
                        <img src="<?= htmlspecialchars((string) $a['image_url']) ?>" alt="Image de <?= htmlspecialchars((string) $a['nom']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <?php endif; ?>
                    <h3 style="margin-top: 0; color: var(--text-color, #333);"><?= htmlspecialchars((string) $a['nom']) ?></h3>
                    
                    <div style="margin: 15px 0;">
                        <span style="display: inline-block; background: #e9ecef; color: #495057; padding: 4px 10px; border-radius: 15px; font-size: 14px; font-weight: bold; margin-right: 10px;">
                            <i class="fa-solid fa-fire"></i> <?= htmlspecialchars((string) $a['calories']) ?> kcal
                        </span>
                        <span style="display: inline-block; background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 15px; font-size: 14px; font-weight: bold;">
                            <i class="fa-solid fa-tag"></i> <?= htmlspecialchars((string) $a['type']) ?>
                        </span>
                    </div>

                    <span style="display: inline-block; margin-top: 10px; background: #007bff; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold;">
                        Voir les détails <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center; font-size: 18px; color: #777;">Aucun aliment n'a été ajouté pour le moment. Soyez le premier !</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
