<?php $baseUrl = $baseUrl ?? '/projet-web-25-26'; ?>

<main class="recipes-page">
    <section class="recipes-hero center">
        <span class="recipes-hero__badge"><i class="fa-solid fa-chart-pie"></i> Analyse nutritionnelle</span>
        <h1>Statistiques nutritionnelles</h1>
        <p>Vue globale des valeurs nutritionnelles du catalogue de recettes Smart Nutrition.</p>
    </section>

    <section class="recipes-card">
        <div class="recipes-actions-bar" style="justify-content:space-between;">
            <a href="<?= $baseUrl ?>/index.php?action=recipes-management" class="recipes-btn secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Retour au catalogue
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=recipe-export&type=statistiques" target="_blank" class="recipes-btn danger">
                <i class="fa-solid fa-file-pdf"></i>
                Exporter en PDF
            </a>
        </div>
    </section>

    <?php if (!$stats): ?>
        <section class="recipes-card">
            <div class="recipes-empty">Aucune donnee disponible. Cree des recettes avec des ingredients pour voir les statistiques.</div>
        </section>
    <?php else: ?>
        <?php $m = $stats['moyennes']; ?>

        <section class="recipes-card">
            <div class="recipes-stat-grid">
                <div class="recipes-stat-box"><p>Recettes totales</p><strong><?= htmlspecialchars((string) $stats['nb_recettes']) ?></strong></div>
                <div class="recipes-stat-box"><p>Recettes analysees</p><strong><?= htmlspecialchars((string) $stats['nb_valides']) ?></strong></div>
                <div class="recipes-stat-box"><p>Moy. calories</p><strong><?= htmlspecialchars((string) $m['calories']) ?></strong></div>
            </div>
        </section>

        <section class="recipes-card">
            <h2 class="recipe-section-title">Moyennes par recette</h2>
            <?php
            $macros = [
                ['label' => 'Proteines', 'unit' => 'g', 'color' => '#2563eb', 'val' => $m['proteines']],
                ['label' => 'Glucides', 'unit' => 'g', 'color' => '#16a34a', 'val' => $m['glucides']],
                ['label' => 'Lipides', 'unit' => 'g', 'color' => '#f59e0b', 'val' => $m['lipides']],
                ['label' => 'Fibres', 'unit' => 'g', 'color' => '#7c3aed', 'val' => $m['fibres']],
            ];
            $maxVal = max(array_column($macros, 'val')) ?: 1;
            ?>
            <div class="recipes-bar-list">
                <?php foreach ($macros as $macro): ?>
                    <?php $pct = round($macro['val'] / $maxVal * 100); ?>
                    <div class="recipes-bar-row">
                        <span class="recipes-bar-label"><?= htmlspecialchars($macro['label']) ?></span>
                        <div class="recipes-bar-track"><div class="recipes-bar-fill" style="width:<?= $pct ?>%; background:<?= $macro['color'] ?>;"></div></div>
                        <span class="recipes-bar-value"><?= htmlspecialchars((string) $macro['val']) ?> <?= htmlspecialchars($macro['unit']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>
