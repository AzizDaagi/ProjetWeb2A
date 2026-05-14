<?php $baseUrl = $baseUrl ?? '/projet-web-25-26'; ?>

<main class="recipes-page">
    <section class="recipes-hero">
        <span class="recipes-hero__badge">
            <i class="fa-solid fa-seedling"></i>
            Catalogue approuve
        </span>
        <h1>Nos Recettes</h1>
        <p>Retrouve des recettes reliees au suivi nutritionnel, avec leurs ingredients communs et un acces rapide aux outils Smart Nutrition.</p>
    </section>

    <section class="recipes-toolbar">
        <div class="recipes-search">
            <input type="search" id="searchFrontRecettes" placeholder="Rechercher une recette...">
        </div>

        <div class="recipes-actions">
            <a href="<?= $baseUrl ?>/index.php?action=recipe-generate" class="recipes-btn">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>Generateur</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=recipe-stats" class="recipes-btn secondary">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Statistiques</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=recipe-export&type=liste" target="_blank" class="recipes-btn danger">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Export PDF</span>
            </a>
        </div>
    </section>

    <?php if (!empty($moduleUnavailableMessage)): ?>
        <section class="recipes-empty" style="margin-bottom:24px;">
            <?= htmlspecialchars($moduleUnavailableMessage) ?>
        </section>
    <?php endif; ?>

    <section class="recipes-grid">
        <?php if (!empty($recettes)): ?>
            <?php foreach ($recettes as $r): ?>
                <?php
                $description = trim((string) ($r['description'] ?? ''));
                if ($description === '') {
                    $description = 'Aucune description disponible.';
                }
                if (mb_strlen($description) > 120) {
                    $description = mb_substr($description, 0, 120) . '...';
                }
                ?>
                <article class="recipe-card" data-nom="<?= htmlspecialchars(mb_strtolower((string) $r['nom'])) ?>">
                    <div class="recipe-card__image">
                        <?php if (!empty($r['image_url'])): ?>
                            <img src="<?= htmlspecialchars((string) $r['image_url']) ?>" alt="<?= htmlspecialchars((string) $r['nom']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="recipe-card__image-fallback" style="display:none;">&#127869;&#65039;</div>
                        <?php else: ?>
                            <div class="recipe-card__image-fallback">&#127869;&#65039;</div>
                        <?php endif; ?>
                    </div>
                    <div class="recipe-card__body">
                        <h3><?= htmlspecialchars((string) $r['nom']) ?></h3>
                        <div class="recipe-card__meta">
                            <span><i class="fa-solid fa-clock"></i> <?= htmlspecialchars((string) ($r['temps_preparation'] ?? '-')) ?></span>
                            <span><i class="fa-solid fa-chart-simple"></i> <?= htmlspecialchars((string) ($r['difficulte'] ?? 'Moyen')) ?></span>
                        </div>
                        <p class="recipe-card__description"><?= htmlspecialchars($description) ?></p>
                        <div class="recipe-card__footer">
                            <a class="recipe-card__link" href="<?= $baseUrl ?>/index.php?action=recipe-details&id=<?= urlencode((string) $r['id']) ?>">Voir details &rarr;</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="recipes-empty">
                <div style="font-size:32px; margin-bottom:10px;">&#127869;&#65039;</div>
                Aucune recette disponible pour le moment.
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchFrontRecettes = document.getElementById('searchFrontRecettes');
    const recettesCards = document.querySelectorAll('.recipe-card');
    if (searchFrontRecettes) {
        searchFrontRecettes.addEventListener('input', function (e) {
            const term = e.target.value.toLowerCase();
            recettesCards.forEach(card => {
                card.style.display = (card.getAttribute('data-nom') || '').includes(term) ? '' : 'none';
            });
        });
    }
});
</script>
