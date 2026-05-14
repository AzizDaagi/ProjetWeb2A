<?php $baseUrl = $baseUrl ?? '/projet-web-25-26'; ?>

<main class="recipes-page">
    <section class="recipes-hero center">
        <span class="recipes-hero__badge"><i class="fa-solid fa-wand-magic-sparkles"></i> IA culinaire</span>
        <h1>Generateur de recettes IA</h1>
        <p>Decris tes besoins nutritionnels et Smart Nutrition propose une combinaison d'aliments compatible avec ton objectif.</p>
    </section>

    <div class="recipes-preset-list">
        <button type="button" class="recipes-preset-chip" onclick="applyPreset(500,30,20,'standard')">Musculation</button>
        <button type="button" class="recipes-preset-chip" onclick="applyPreset(400,20,15,'vegetarien')">Vegetarien leger</button>
        <button type="button" class="recipes-preset-chip" onclick="applyPreset(600,25,25,'sans_gluten')">Sans gluten</button>
        <button type="button" class="recipes-preset-chip" onclick="applyPreset(350,35,10,'standard')">Haute proteine</button>
    </div>

    <section class="recipes-card">
        <?php if (!empty($errorMsg)): ?>
            <p style="color:#be123c; font-weight:700;"><?= htmlspecialchars($errorMsg) ?></p>
        <?php endif; ?>

        <form method="POST" action="<?= $baseUrl ?>/index.php?action=recipe-generate" id="gen-form">
            <input type="hidden" name="generate" value="1">

            <div class="recipes-form-grid">
                <div class="recipes-form-group">
                    <label for="max_kcal">Calories max</label>
                    <input type="number" name="max_kcal" id="max_kcal" min="100" max="3000" required value="<?= isset($_POST['max_kcal']) ? htmlspecialchars((string) $_POST['max_kcal']) : '500' ?>">
                </div>
                <div class="recipes-form-group">
                    <label for="min_prot">Proteines min</label>
                    <input type="number" name="min_prot" id="min_prot" min="0" max="200" required value="<?= isset($_POST['min_prot']) ? htmlspecialchars((string) $_POST['min_prot']) : '30' ?>">
                </div>
                <div class="recipes-form-group">
                    <label for="max_lipides">Lipides max</label>
                    <input type="number" name="max_lipides" id="max_lipides" min="0" max="200" required value="<?= isset($_POST['max_lipides']) ? htmlspecialchars((string) $_POST['max_lipides']) : '20' ?>">
                </div>
                <div class="recipes-form-group">
                    <label for="diet_type">Regime alimentaire</label>
                    <select name="diet_type" id="diet_type">
                        <option value="standard" <?= (!isset($_POST['diet_type']) || $_POST['diet_type'] === 'standard') ? 'selected' : '' ?>>Standard</option>
                        <option value="vegetarien" <?= (isset($_POST['diet_type']) && $_POST['diet_type'] === 'vegetarien') ? 'selected' : '' ?>>Vegetarien</option>
                        <option value="sans_gluten" <?= (isset($_POST['diet_type']) && $_POST['diet_type'] === 'sans_gluten') ? 'selected' : '' ?>>Sans gluten</option>
                    </select>
                </div>
            </div>

            <div class="recipes-actions-bar">
                <button type="submit" class="recipes-btn">
                    <i class="fa-solid fa-cogs"></i>
                    Generer ma recette
                </button>
            </div>
        </form>
    </section>

    <?php if (!empty($generated)): ?>
        <?php $t = $generated['totaux']; ?>
        <section class="recipes-card">
            <h2 class="recipe-section-title">Combinaison proposee</h2>

            <div class="recipes-combo-list">
                <?php foreach ($generated['aliments'] as $item): ?>
                    <div class="recipes-combo-item">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span class="recipes-combo-qty"><?= htmlspecialchars((string) $item['quantite']) ?> g</span>
                            <strong><?= htmlspecialchars((string) $item['aliment']['nom']) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="recipes-macro-summary">
                <div class="recipe-macro"><strong><?= htmlspecialchars((string) $t['calories']) ?></strong><span>Calories</span></div>
                <div class="recipe-macro"><strong><?= htmlspecialchars((string) $t['proteines']) ?></strong><span>Proteines</span></div>
                <div class="recipe-macro"><strong><?= htmlspecialchars((string) $t['glucides']) ?></strong><span>Glucides</span></div>
                <div class="recipe-macro"><strong><?= htmlspecialchars((string) $t['lipides']) ?></strong><span>Lipides</span></div>
            </div>
        </section>
    <?php endif; ?>
</main>

<script>
function applyPreset(kcal, prot, lip, diet) {
    document.getElementById('max_kcal').value = kcal;
    document.getElementById('min_prot').value = prot;
    document.getElementById('max_lipides').value = lip;
    document.getElementById('diet_type').value = diet;
}
</script>
