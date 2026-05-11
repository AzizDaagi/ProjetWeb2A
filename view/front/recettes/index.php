<?php
$pageTitle = 'Smart Nutrition | Recettes';
$bodyClass = 'recipes-page';
$projectBaseUrl = $baseUrl ?? '/projet-web-25-26';
$routeBase = $projectBaseUrl . '/index.php';
$isRecipeAdmin = $isAdminSession ?? ((($_SESSION['user_role'] ?? 'user') === 'admin'));

require_once __DIR__ . '/../../../controller/RecetteController.php';

$controller = new RecetteController();

$truncateText = static function ($text, $limit = 120) {
    $text = preg_replace('/\s+/', ' ', trim((string) $text));

    if ($text === '') {
        return 'Description a venir.';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit - 1)) . '…';
    }

    if (strlen($text) <= $limit) {
        return $text;
    }

    return rtrim(substr($text, 0, $limit - 3)) . '...';
};

$resolveImageUrl = static function ($imageUrl, $folder = 'recettes') use ($projectBaseUrl) {
    $imageUrl = trim((string) $imageUrl);

    if ($imageUrl === '') {
        return null;
    }

    if (strpos($imageUrl, '/projetwebmalek/') === 0) {
        return $projectBaseUrl . '/' . ltrim(substr($imageUrl, strlen('/projetwebmalek/')), '/');
    }

    if (strpos($imageUrl, 'projetwebmalek/') === 0) {
        return $projectBaseUrl . '/' . ltrim(substr($imageUrl, strlen('projetwebmalek/')), '/');
    }

    if (strpos($imageUrl, '/projet-web-25-26/') === 0 || preg_match('#^https?://#i', $imageUrl)) {
        return $imageUrl;
    }

    if (strpos($imageUrl, '/view/uploads/') === 0) {
        return $projectBaseUrl . $imageUrl;
    }

    if (strpos($imageUrl, 'view/uploads/') === 0) {
        return $projectBaseUrl . '/' . ltrim($imageUrl, '/');
    }

    if (preg_match('/^[A-Za-z0-9_.-]+\.(jpg|jpeg|png|gif|webp|svg)$/i', $imageUrl)) {
        return $projectBaseUrl . '/view/uploads/' . $folder . '/' . basename($imageUrl);
    }

    return $imageUrl;
};

try {
    $recettes = $controller->listRecettes();
    $aliments = $controller->listAliments();
} catch (Exception $exception) {
    $recettes = [];
    $aliments = [];
}

require_once __DIR__ . '/../../layouts/header.php';
?>

<section class="recipes-module">
    <div class="recipes-shell">
        <section class="recipes-hero-panel">
            <div class="recipes-hero-copy">
                <p class="recipes-eyebrow">Catalogue approuve</p>
                <h1>Nos Recettes</h1>
                <p class="recipes-hero-text">
                    Une selection Smart Nutrition de recettes claires, lisibles et prêtes a consulter sans quitter le projet principal.
                </p>
            </div>

            <div class="recipes-hero-badge">
                <span class="recipes-badge-label">Catalogue approuve</span>
                <strong><?= number_format(count($recettes), 0, '.', ' ') ?></strong>
                <span class="recipes-badge-meta">recette(s) disponibles</span>
            </div>
        </section>

        <section class="recipes-section">
            <div class="recipes-toolbar">
                <div class="recipes-toolbar-main">
                    <div class="recipes-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchFrontRecettes" placeholder="Rechercher une recette">
                    </div>

                    <div class="recipes-action-group">
                        <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-generate" class="recipes-action-btn">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            Generateur
                        </a>
                        <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-stats" class="recipes-action-btn">
                            <i class="fa-solid fa-chart-pie"></i>
                            Statistiques
                        </a>
                        <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-export&amp;type=liste" target="_blank" class="recipes-action-btn recipes-action-btn-danger">
                            <i class="fa-solid fa-file-pdf"></i>
                            Export PDF
                        </a>
                    </div>
                </div>

                <?php if ($isRecipeAdmin): ?>
                    <div class="recipes-action-group recipes-action-group-admin">
                        <a href="<?= htmlspecialchars($routeBase) ?>?action=admin-recipes" class="recipes-action-btn recipes-action-btn-accent">
                            <i class="fa-solid fa-pen-to-square"></i>
                            Gerer recettes
                        </a>
                        <a href="<?= htmlspecialchars($routeBase) ?>?controller=backoffice&amp;action=suivi" class="recipes-action-btn">
                            <i class="fa-solid fa-apple-whole"></i>
                            Gerer aliments
                        </a>
                        <a href="<?= htmlspecialchars($routeBase) ?>?action=admin-recommendations" class="recipes-action-btn">
                            <i class="fa-solid fa-heart-pulse"></i>
                            Recommandations
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="recipes-card-grid">
                <?php if (!empty($recettes)): ?>
                    <?php foreach ($recettes as $recette): ?>
                        <?php
                        $recipeName = strtolower((string) ($recette['nom'] ?? ''));
                        $recipeImageUrl = $resolveImageUrl($recette['image_url'] ?? null, 'recettes');
                        ?>
                        <a
                            href="<?= htmlspecialchars($routeBase) ?>?action=recipe-details&amp;id=<?= (int) $recette['id'] ?>"
                            class="recipes-card"
                            data-nom="<?= htmlspecialchars($recipeName) ?>"
                        >
                            <div class="recipes-card-media<?= $recipeImageUrl ? '' : ' is-placeholder' ?>">
                                <?php if ($recipeImageUrl): ?>
                                    <img
                                        src="<?= htmlspecialchars($recipeImageUrl) ?>"
                                        alt="<?= htmlspecialchars((string) $recette['nom']) ?>"
                                        loading="lazy"
                                        onerror="this.closest('.recipes-card-media').classList.add('is-placeholder'); this.remove();"
                                    >
                                <?php endif; ?>
                                <div class="recipes-card-placeholder">
                                    <i class="fa-solid fa-utensils"></i>
                                    <span>Recette Smart Nutrition</span>
                                </div>
                            </div>

                            <div class="recipes-card-copy">
                                <div class="recipes-card-head">
                                    <h3><?= htmlspecialchars((string) $recette['nom']) ?></h3>
                                    <span class="recipes-chip">
                                        <i class="fa-solid fa-clock"></i>
                                        <?= htmlspecialchars((string) ($recette['temps_preparation'] ?? 'Temps a definir')) ?>
                                    </span>
                                </div>

                                <p class="recipes-card-description">
                                    <?= htmlspecialchars($truncateText($recette['description'] ?? ($recette['niveau_difficulte'] ?? ''))) ?>
                                </p>

                                <span class="recipes-card-link">
                                    Voir details
                                    <i class="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="recipes-empty-state">
                        <i class="fa-solid fa-utensils"></i>
                        <h3>Aucune recette disponible</h3>
                        <p>Le catalogue recettes sera visible ici des que les donnees seront alimentees.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="recipes-section recipes-section-secondary">
            <div class="recipes-section-head">
                <div>
                    <p class="recipes-eyebrow">Ingredients de reference</p>
                    <h2>Nos Aliments</h2>
                </div>

                <div class="recipes-search-wrap recipes-search-wrap-compact">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchFrontAliments" placeholder="Rechercher un aliment">
                </div>
            </div>

            <div class="recipes-card-grid recipes-card-grid-foods">
                <?php if (!empty($aliments)): ?>
                    <?php foreach ($aliments as $aliment): ?>
                        <?php
                        $foodName = strtolower((string) ($aliment['nom'] ?? ''));
                        $foodImageUrl = $resolveImageUrl($aliment['image_url'] ?? null, 'aliments');
                        ?>
                        <a
                            href="<?= htmlspecialchars($routeBase) ?>?action=recipe-aliment&amp;id=<?= (int) $aliment['id'] ?>"
                            class="recipes-card recipes-card-food"
                            data-nom="<?= htmlspecialchars($foodName) ?>"
                        >
                            <div class="recipes-card-media<?= $foodImageUrl ? '' : ' is-placeholder' ?>">
                                <?php if ($foodImageUrl): ?>
                                    <img
                                        src="<?= htmlspecialchars($foodImageUrl) ?>"
                                        alt="<?= htmlspecialchars((string) $aliment['nom']) ?>"
                                        loading="lazy"
                                        onerror="this.closest('.recipes-card-media').classList.add('is-placeholder'); this.remove();"
                                    >
                                <?php endif; ?>
                                <div class="recipes-card-placeholder">
                                    <i class="fa-solid fa-apple-whole"></i>
                                    <span>Aliment Smart Nutrition</span>
                                </div>
                            </div>

                            <div class="recipes-card-copy">
                                <div class="recipes-card-head">
                                    <h3><?= htmlspecialchars((string) $aliment['nom']) ?></h3>
                                    <span class="recipes-chip recipes-chip-green">
                                        <i class="fa-solid fa-fire"></i>
                                        <?= htmlspecialchars((string) ($aliment['calories'] ?? 0)) ?> kcal
                                    </span>
                                </div>

                                <p class="recipes-card-description">
                                    <?= htmlspecialchars($truncateText($aliment['description'] ?? ($aliment['type'] ?? 'Aliment du catalogue nutritionnel.'))) ?>
                                </p>

                                <span class="recipes-card-link">
                                    Voir aliment
                                    <i class="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="recipes-empty-state">
                        <i class="fa-solid fa-apple-whole"></i>
                        <h3>Aucun aliment disponible</h3>
                        <p>Le catalogue aliments sera visible ici des que les donnees seront disponibles.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterCards = function(inputId, selector) {
        const input = document.getElementById(inputId);
        const cards = document.querySelectorAll(selector);

        if (!input) {
            return;
        }

        input.addEventListener('input', function(event) {
            const term = event.target.value.toLowerCase().trim();

            cards.forEach(function(card) {
                const name = (card.getAttribute('data-nom') || '').toLowerCase();
                card.style.display = name.includes(term) ? '' : 'none';
            });
        });
    };

    filterCards('searchFrontRecettes', '.recipes-card-grid .recipes-card[data-nom]');
    filterCards('searchFrontAliments', '.recipes-card-grid-foods .recipes-card[data-nom]');
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
