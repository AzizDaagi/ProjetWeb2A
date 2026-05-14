<?php $baseUrl = $baseUrl ?? '/projet-web-25-26'; ?>
<?php
function macroBadgeRecipe($pct, $min, $max)
{
    return ($pct >= $min && $pct <= $max)
        ? '<span style="color:#16a34a;font-size:13px;">OK ' . $pct . '%</span>'
        : '<span style="color:#be123c;font-size:13px;">Hors cible ' . $pct . '%</span>';
}
?>

<main class="recipes-page">
    <section class="recipes-hero center">
        <span class="recipes-hero__badge"><i class="fa-solid fa-arrow-up-right-dots"></i> Optimisation</span>
        <h1>Optimiseur nutritionnel</h1>
        <p>Le systeme ajuste automatiquement les quantites de chaque ingredient pour mieux coller a ton objectif.</p>
    </section>

    <a href="<?= $baseUrl ?>/index.php?action=recipe-details&id=<?= urlencode((string) $id_recette) ?>" class="recipes-btn secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Retour a la recette
    </a>

    <?php if (!$recette): ?>
        <section class="recipes-card">
            <p class="recipe-description">Recette introuvable. <a href="<?= $baseUrl ?>/index.php?action=recipes-management">Retour au catalogue.</a></p>
        </section>
    <?php elseif (!$result): ?>
        <section class="recipes-card">
            <p class="recipe-description">Module recettes temporairement indisponible. Lancez la migration fix_recettes_integration.php.</p>
        </section>
    <?php else: ?>
        <section class="recipes-card">
            <h2 class="recipe-section-title"><?= htmlspecialchars((string) $recette['nom']) ?></h2>
            <div class="recipes-optimizer-options">
                <?php foreach ($objectifLabels as $key => $info): ?>
                    <a href="<?= $baseUrl ?>/index.php?action=recipe-optimize&id=<?= urlencode((string) $id_recette) ?>&objectif=<?= urlencode($key) ?>" class="recipes-optimizer-option<?= $objectif === $key ? ' is-active' : '' ?>">
                        <span><i class="fa-solid <?= htmlspecialchars((string) $info['icon']) ?>"></i></span>
                        <strong><?= htmlspecialchars((string) $info['label']) ?></strong>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($result['ecarts'])): ?>
                <div class="recipes-card">
                    <h3 class="recipe-section-title">Problemes detectes</h3>
                    <ul>
                        <?php foreach ($result['ecarts'] as $e): ?>
                            <li><?= htmlspecialchars((string) $e['label']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="recipes-optimizer-grid">
                <div class="recipes-optimizer-panel">
                    <h3 class="recipe-section-title">Avant</h3>
                    <?php $ba = $result['avant']; $pa = $result['pct_avant']; ?>
                    <div class="recipes-optimizer-list">
                        <div class="recipes-optimizer-item"><strong>Calories</strong><span><?= htmlspecialchars((string) $ba['calories']) ?> kcal</span></div>
                        <div class="recipes-optimizer-item"><strong>Proteines</strong><span><?= htmlspecialchars((string) $ba['proteines']) ?> g <?= macroBadgeRecipe($pa['prot'], 15, 35) ?></span></div>
                        <div class="recipes-optimizer-item"><strong>Glucides</strong><span><?= htmlspecialchars((string) $ba['glucides']) ?> g <?= macroBadgeRecipe($pa['gluc'], 40, 60) ?></span></div>
                        <div class="recipes-optimizer-item"><strong>Lipides</strong><span><?= htmlspecialchars((string) $ba['lipides']) ?> g <?= macroBadgeRecipe($pa['lip'], 20, 35) ?></span></div>
                    </div>
                </div>

                <div class="recipes-optimizer-panel">
                    <h3 class="recipe-section-title">Apres</h3>
                    <?php $bp = $result['apres']; $pp = $result['pct_apres']; ?>
                    <div class="recipes-optimizer-list">
                        <div class="recipes-optimizer-item"><strong>Calories</strong><span><?= htmlspecialchars((string) $bp['calories']) ?> kcal</span></div>
                        <div class="recipes-optimizer-item"><strong>Proteines</strong><span><?= htmlspecialchars((string) $bp['proteines']) ?> g <?= macroBadgeRecipe($pp['prot'], 15, 35) ?></span></div>
                        <div class="recipes-optimizer-item"><strong>Glucides</strong><span><?= htmlspecialchars((string) $bp['glucides']) ?> g <?= macroBadgeRecipe($pp['gluc'], 40, 60) ?></span></div>
                        <div class="recipes-optimizer-item"><strong>Lipides</strong><span><?= htmlspecialchars((string) $bp['lipides']) ?> g <?= macroBadgeRecipe($pp['lip'], 20, 35) ?></span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="recipes-card">
            <h3 class="recipe-section-title">Nouvelles quantites proposees</h3>
            <div class="recipes-optimizer-list">
                <?php foreach ($result['aliments'] as $a): ?>
                    <?php $oldQ = (float) ($a['quantite'] ?? 0); $newQ = $result['nouvelles_quantites'][$a['id']] ?? $oldQ; ?>
                    <div class="recipes-optimizer-item">
                        <strong><?= htmlspecialchars((string) $a['nom']) ?></strong>
                        <span><?= htmlspecialchars((string) $oldQ) ?> g → <?= htmlspecialchars((string) $newQ) ?> g</span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="recipes-actions-bar">
                <form method="POST" action="<?= $baseUrl ?>/index.php?action=recipe-save-optimization">
                    <input type="hidden" name="id_recette" value="<?= htmlspecialchars((string) $id_recette) ?>">
                    <input type="hidden" name="objectif" value="<?= htmlspecialchars((string) $objectif) ?>">
                    <?php foreach ($result['nouvelles_quantites'] as $al_id => $qte): ?>
                        <input type="hidden" name="nouvelles_quantites[<?= htmlspecialchars((string) $al_id) ?>]" value="<?= htmlspecialchars((string) $qte) ?>">
                    <?php endforeach; ?>
                    <button type="submit" name="appliquer" value="1" class="recipes-btn">
                        <i class="fa-solid fa-check"></i>
                        Accepter et enregistrer
                    </button>
                </form>
                <a href="<?= $baseUrl ?>/index.php?action=recipe-details&id=<?= urlencode((string) $id_recette) ?>" class="recipes-btn secondary">
                    <i class="fa-solid fa-xmark"></i>
                    Garder l'originale
                </a>
            </div>
        </section>
    <?php endif; ?>
</main>
