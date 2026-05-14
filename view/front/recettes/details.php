<?php $baseUrl = $baseUrl ?? '/projet-web-25-26'; ?>

<main class="recipes-page recipe-detail">
    <a href="<?= $baseUrl ?>/index.php?action=recipes-management" class="recipes-btn secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Retour au catalogue
    </a>

    <?php if (!empty($optimised_flash)): ?>
        <section class="recipes-card">
            <strong style="color:#16a34a;">Recette optimisee et enregistree avec succes.</strong>
        </section>
    <?php endif; ?>

    <?php if ($recette): ?>
        <section class="recipes-card">
            <div class="recipe-detail__image">
                <?php if (!empty($recette['image_url'])): ?>
                    <img src="<?= htmlspecialchars((string) $recette['image_url']) ?>" alt="<?= htmlspecialchars((string) $recette['nom']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="recipe-card__image-fallback" style="display:none;">🍽️</div>
                <?php else: ?>
                    <div class="recipe-card__image-fallback">🍽️</div>
                <?php endif; ?>
            </div>

            <h1 class="recipe-detail__title"><?= htmlspecialchars((string) $recette['nom']) ?></h1>

            <div class="recipe-detail__meta">
                <span class="recipe-detail__chip"><i class="fa-solid fa-clock"></i> <?= htmlspecialchars((string) ($recette['temps_preparation'] ?? '-')) ?></span>
                <span class="recipe-detail__chip"><i class="fa-solid fa-chart-simple"></i> <?= htmlspecialchars((string) ($recette['difficulte'] ?? 'Moyen')) ?></span>
            </div>

            <?php if (!empty($aliments_associes)): ?>
                <h2 class="recipe-section-title">Ingredients</h2>
                <div class="recipe-ingredients">
                    <?php foreach ($aliments_associes as $a): ?>
                        <div class="recipe-ingredient">
                            <strong><?= htmlspecialchars((string) $a['nom']) ?></strong>
                            <span><?= htmlspecialchars((string) ($a['quantite'] ?? 0)) ?> g</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h2 class="recipe-section-title">Macros nutritionnelles</h2>
                <div class="recipe-macro-grid">
                    <div class="recipe-macro"><strong><?= round((float) ($nutrition_totale['calories'] ?? 0)) ?></strong><span>kcal</span></div>
                    <div class="recipe-macro"><strong><?= round((float) ($nutrition_totale['proteines'] ?? 0), 1) ?></strong><span>Proteines (g)</span></div>
                    <div class="recipe-macro"><strong><?= round((float) ($nutrition_totale['glucides'] ?? 0), 1) ?></strong><span>Glucides (g)</span></div>
                    <div class="recipe-macro"><strong><?= round((float) ($nutrition_totale['lipides'] ?? 0), 1) ?></strong><span>Lipides (g)</span></div>
                    <div class="recipe-macro"><strong><?= round((float) ($nutrition_totale['fibres'] ?? 0), 1) ?></strong><span>Fibres (g)</span></div>
                </div>
            <?php endif; ?>

            <h2 class="recipe-section-title">Description / etapes</h2>
            <div class="recipe-description"><?= nl2br(htmlspecialchars((string) ($recette['description'] ?? ''))) ?></div>

            <div class="recipes-actions-bar">
                <a href="<?= $baseUrl ?>/index.php?action=recipe-optimize&id=<?= urlencode((string) $recette['id']) ?>" class="recipes-btn">
                    <i class="fa-solid fa-arrow-up-right-dots"></i>
                    Optimiser cette recette
                </a>
                <a href="<?= $baseUrl ?>/index.php?action=recipe-export&type=recette&id=<?= urlencode((string) $recette['id']) ?>" target="_blank" class="recipes-btn danger">
                    <i class="fa-solid fa-file-pdf"></i>
                    Exporter PDF
                </a>
                <?php if (($_SESSION['user_role'] ?? 'user') === 'admin'): ?>
                    <a href="<?= $baseUrl ?>/index.php?action=admin-recipes&edit_id=<?= urlencode((string) $recette['id']) ?>" class="recipes-btn secondary">
                        <i class="fa-solid fa-pen"></i>
                        Modifier
                    </a>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>
        <section class="recipes-card">
            <h2 class="recipe-section-title">Recette introuvable</h2>
            <p class="recipe-description">Cette recette n'existe pas ou a ete supprimee.</p>
        </section>
    <?php endif; ?>
</main>
