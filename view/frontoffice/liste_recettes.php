<?php
$pageTitle = 'Smart Nutrition | Produits';
require_once __DIR__ . '/../../controler/RecetteController.php';
require_once __DIR__ . '/../../controler/AlimentController.php';

$controller        = new RecetteController();
$alimentController = new AlimentController();
try {
    $recettes = $controller->listRecettes();
    $aliments  = $alimentController->listAliments();
} catch (Exception $e) {
    $recettes = [];
    $aliments  = [];
}

require_once __DIR__ . '/../template_only/layouts/header.php';
?>

<!-- ===== HERO : orbit diagram from template_only/front/home.php ===== -->
<div class="hero-wrapper">
    <!-- Animated orbit ring -->
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

    <!-- Hero text -->
    <div class="hero-content">
        <h1>Smart Nutrition</h1>
        <p class="subtitle-text">Sustainable &amp; Intelligent Food System</p>
        <p class="description-text">
            Welcome to your product catalog area.<br>
            Browse approved items or submit one for review.
        </p>
    </div>
</div>

<div class="catalog-divider"></div>

<!-- ===== RECETTES CATALOG ===== -->
<div class="catalog-section">
    <div class="catalog-header">
        <div class="catalog-header-left">
            <p class="catalog-eyebrow">Catalog approuvé</p>
            <h2><i class="fa-solid fa-book-open"></i> Nos Recettes</h2>
        </div>
        <div class="catalog-actions" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <input type="text" id="searchFrontRecettes" placeholder="Rechercher une recette..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: white; width:200px; font-family:inherit; font-size:14px;">
            <a href="/projetwebmalek/view/backoffice/manage_recettes.php"
               class="catalog-btn catalog-btn-primary">
                <i class="fa-solid fa-plus"></i> Créer recette
            </a>
            <a href="/projetwebmalek/view/backoffice/manage_aliments.php"
               class="catalog-btn catalog-btn-blue">
                <i class="fa-solid fa-apple-whole"></i> Gérer aliments
            </a>
            <a href="/projetwebmalek/view/backoffice/index.php"
               class="catalog-btn catalog-btn-green">
                <i class="fa-solid fa-user-shield"></i> Admin
            </a>
        </div>
    </div>

    <div class="product-grid">
        <?php if (!empty($recettes)): ?>
            <?php foreach ($recettes as $r): ?>
                <a href="details_recette.php?id=<?= $r['id'] ?>" class="product-card recette-card" data-nom="<?= htmlspecialchars(strtolower((string)$r['nom'])) ?>">
                    <div class="product-card-img">
                        <?php if (!empty($r['image_url'])): ?>
                            <img src="<?= htmlspecialchars((string)$r['image_url']) ?>"
                                 alt="<?= htmlspecialchars((string)$r['nom']) ?>">
                        <?php else: ?>
                            <div class="product-card-img-placeholder">
                                <i class="fa-solid fa-utensils"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-title-row">
                            <h3 class="product-card-title"><?= htmlspecialchars((string)$r['nom']) ?></h3>
                            <span class="product-card-badge badge-orange">
                                <i class="fa-solid fa-clock"></i>
                                <?= htmlspecialchars((string)$r['temps_preparation']) ?>
                            </span>
                        </div>
                        <p class="product-card-desc">
                            <?= htmlspecialchars((string)($r['description'] ?? $r['niveau_difficulte'] ?? '')) ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column:1/-1;text-align:center;color:rgba(236,240,241,0.5);padding:40px 0;">
                <i class="fa-solid fa-utensils" style="font-size:32px;display:block;margin-bottom:12px;opacity:.4;"></i>
                Aucune recette pour le moment. Soyez le premier !
            </p>
        <?php endif; ?>
    </div>
</div>

<div class="catalog-divider" style="max-width:1200px;margin:0 auto;"></div>

<!-- ===== ALIMENTS CATALOG ===== -->
<div class="catalog-section" style="padding-top:36px;">
    <div class="catalog-header">
        <div class="catalog-header-left">
            <p class="catalog-eyebrow">Qualité certifiée</p>
            <h2><i class="fa-solid fa-apple-whole"></i> Nos Aliments</h2>
        </div>
        <div class="catalog-actions" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <input type="text" id="searchFrontAliments" placeholder="Rechercher un aliment..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: white; width:200px; font-family:inherit; font-size:14px;">
            <a href="/projetwebmalek/view/backoffice/manage_aliments.php"
               class="catalog-btn catalog-btn-primary">
                <i class="fa-solid fa-plus"></i> Ajouter aliment
            </a>
        </div>
    </div>

    <div class="product-grid">
        <?php if (!empty($aliments)): ?>
            <?php foreach ($aliments as $a): ?>
                <a href="details_aliment.php?id=<?= $a['id'] ?>" class="product-card aliment-card" data-nom="<?= htmlspecialchars(strtolower((string)$a['nom'])) ?>">
                    <div class="product-card-img">
                        <?php if (!empty($a['image_url'])): ?>
                            <img src="<?= htmlspecialchars((string)$a['image_url']) ?>"
                                 alt="<?= htmlspecialchars((string)$a['nom']) ?>">
                        <?php else: ?>
                            <div class="product-card-img-placeholder">
                                <i class="fa-solid fa-leaf"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-title-row">
                            <h3 class="product-card-title"><?= htmlspecialchars((string)$a['nom']) ?></h3>
                            <span class="product-card-badge badge-green">
                                <i class="fa-solid fa-fire"></i>
                                <?= htmlspecialchars((string)$a['calories']) ?> kcal
                            </span>
                        </div>
                        <p class="product-card-desc">
                            <?= htmlspecialchars((string)($a['description'] ?? $a['type'] ?? '')) ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column:1/-1;text-align:center;color:rgba(236,240,241,0.5);padding:40px 0;">
                <i class="fa-solid fa-apple-whole" style="font-size:32px;display:block;margin-bottom:12px;opacity:.4;"></i>
                Aucun aliment pour le moment. Soyez le premier !
            </p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- Recherche Recettes ---
    const searchFrontRecettes = document.getElementById("searchFrontRecettes");
    const recettesCards = document.querySelectorAll(".recette-card");
    
    if (searchFrontRecettes) {
        searchFrontRecettes.addEventListener("input", function(e) {
            const term = e.target.value.toLowerCase();
            recettesCards.forEach(card => {
                const nom = card.getAttribute("data-nom");
                if (nom && nom.includes(term)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        });
    }

    // --- Recherche Aliments ---
    const searchFrontAliments = document.getElementById("searchFrontAliments");
    const alimentsCards = document.querySelectorAll(".aliment-card");
    
    if (searchFrontAliments) {
        searchFrontAliments.addEventListener("input", function(e) {
            const term = e.target.value.toLowerCase();
            alimentsCards.forEach(card => {
                const nom = card.getAttribute("data-nom");
                if (nom && nom.includes(term)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
