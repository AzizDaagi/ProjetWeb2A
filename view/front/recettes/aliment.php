<?php $baseUrl = $baseUrl ?? '/projet-web-25-26'; ?>

<main class="recipes-page recipe-detail">
    <a href="<?= $baseUrl ?>/index.php?action=recipes-management" class="recipes-btn secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Retour au catalogue
    </a>

    <?php if ($aliment): ?>
        <section class="recipes-card">
            <div class="recipe-detail__image">
                <?php if (!empty($aliment['image_url'])): ?>
                    <img src="<?= htmlspecialchars((string) $aliment['image_url']) ?>" alt="<?= htmlspecialchars((string) $aliment['nom']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="recipe-card__image-fallback" style="display:none;">🍎</div>
                <?php else: ?>
                    <div class="recipe-card__image-fallback">🍎</div>
                <?php endif; ?>
            </div>

            <h1 class="recipe-detail__title"><?= htmlspecialchars((string) $aliment['nom']) ?></h1>

            <div class="recipe-detail__meta">
                <span class="recipe-detail__chip"><i class="fa-solid fa-fire"></i> <?= htmlspecialchars((string) $aliment['calories']) ?> kcal</span>
                <span class="recipe-detail__chip"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars((string) ($aliment['type'] ?? 'Aliment')) ?></span>
            </div>

            <div class="recipe-macro-grid">
                <div class="recipe-macro"><strong><?= htmlspecialchars((string) $aliment['proteines']) ?></strong><span>Proteines (g)</span></div>
                <div class="recipe-macro"><strong><?= htmlspecialchars((string) $aliment['glucides']) ?></strong><span>Glucides (g)</span></div>
                <div class="recipe-macro"><strong><?= htmlspecialchars((string) $aliment['lipides']) ?></strong><span>Lipides (g)</span></div>
                <div class="recipe-macro"><strong><?= htmlspecialchars((string) ($aliment['fibres'] ?? '0')) ?></strong><span>Fibres (g)</span></div>
                <div class="recipe-macro"><strong><?= htmlspecialchars((string) ($aliment['sucre_g'] ?? '0')) ?></strong><span>Sucre (g)</span></div>
            </div>

            <p class="recipe-description">Valeurs nutritionnelles pour 100 g de produit.</p>
        </section>
    <?php else: ?>
        <section class="recipes-card">
            <h2 class="recipe-section-title">Aliment introuvable</h2>
            <p class="recipe-description">Cet aliment n'existe pas ou a ete supprime.</p>
        </section>
    <?php endif; ?>
</main>
