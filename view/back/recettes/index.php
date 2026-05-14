<?php $baseUrl = $baseUrl ?? '/projet-web-25-26'; ?>

<div class="admin-page admin-module-page">
    <div class="admin-page-header">
        <span class="admin-page-kicker">Catalogue approuve</span>
        <h1>Recettes</h1>
        <p>Creez et gerez les recettes Smart Nutrition en reutilisant le catalogue officiel des aliments.</p>
    </div>

    <?php if (!empty($flashWarnings)): ?>
        <div class="admin-alert admin-alert-error">
            <strong>Equilibre nutritionnel</strong>
            <ul class="admin-alert-list">
                <?php foreach ($flashWarnings as $warning): ?>
                    <li><?= htmlspecialchars((string) $warning) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <h2 class="admin-card-title"><?= $recetteToEdit ? 'Modifier la recette' : 'Creer une recette' ?></h2>

        <form method="POST" action="<?= $baseUrl ?>/index.php?action=admin-recipes" id="recette-form" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="action" value="<?= $recetteToEdit ? 'update' : 'add' ?>">
            <input type="hidden" name="id" value="<?= $recetteToEdit ? htmlspecialchars((string) $recetteToEdit['id']) : '' ?>">
            <input type="hidden" name="existing_image_url" value="<?= $recetteToEdit ? htmlspecialchars((string) ($recetteToEdit['image_url'] ?? '')) : '' ?>">

            <div class="admin-form-grid">
                <div class="admin-form-group">
                    <label for="nom-input">Nom de la recette</label>
                    <input type="text" name="nom" id="nom-input" value="<?= $recetteToEdit ? htmlspecialchars((string) $recetteToEdit['nom']) : '' ?>">
                </div>

                <div class="admin-form-group">
                    <label for="image-input">Photo de la recette</label>
                    <input type="file" name="image" id="image-input" accept="image/*">
                </div>

                <div class="admin-form-group full">
                    <label>Ingredients / aliments associes</label>
                    <div class="admin-checkbox-list">
                        <?php if (!empty($tous_aliments)): ?>
                            <?php foreach ($tous_aliments as $al): ?>
                                <?php
                                $isChecked = false;
                                $qte = '';
                                if ($recetteToEdit && isset($recettes_aliments_map[$recetteToEdit['id']]) && in_array($al['id'], $recettes_aliments_map[$recetteToEdit['id']], true)) {
                                    $isChecked = true;
                                    $qte = $recettes_aliments_quantites_map[$recetteToEdit['id']][$al['id']] ?? '';
                                }
                                ?>
                                <div class="admin-checkbox-item">
                                    <label class="admin-checkbox-label" for="aliment-<?= (int) $al['id'] ?>">
                                        <input
                                            type="checkbox"
                                            name="aliments[]"
                                            id="aliment-<?= (int) $al['id'] ?>"
                                            class="aliment-checkbox"
                                            value="<?= htmlspecialchars((string) $al['id']) ?>"
                                            <?= $isChecked ? 'checked' : '' ?>
                                            onchange="document.getElementById('qte_<?= (int) $al['id'] ?>').style.display = this.checked ? 'block' : 'none';"
                                        >
                                        <span><?= htmlspecialchars((string) $al['nom']) ?></span>
                                    </label>
                                    <input
                                        type="number"
                                        step="1"
                                        min="1"
                                        name="quantites[<?= (int) $al['id'] ?>]"
                                        id="qte_<?= (int) $al['id'] ?>"
                                        placeholder="Qte (g)"
                                        value="<?= htmlspecialchars((string) $qte) ?>"
                                        class="admin-quantity-input"
                                        style="display:<?= $isChecked ? 'block' : 'none' ?>;"
                                    >
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="admin-empty-state">Aucun aliment disponible. Ouvre d'abord la gestion officielle des aliments.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="admin-form-group full">
                    <label for="desc-input">Description / etapes</label>
                    <textarea name="description" id="desc-input"><?= $recetteToEdit ? htmlspecialchars((string) $recetteToEdit['description']) : '' ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label for="temps-input">Temps de preparation</label>
                    <input type="text" name="temps_preparation" id="temps-input" value="<?= $recetteToEdit ? htmlspecialchars((string) ($recetteToEdit['temps_preparation'] ?? '')) : '' ?>">
                </div>

                <div class="admin-form-group">
                    <label for="diff-input">Difficulte</label>
                    <select name="difficulte" id="diff-input">
                        <option value="">Selectionnez un niveau</option>
                        <?php foreach (['Tres Facile', 'Facile', 'Moyen', 'Difficile', 'Expert'] as $difficulte): ?>
                            <option value="<?= htmlspecialchars($difficulte) ?>" <?= ($recetteToEdit && (($recetteToEdit['difficulte'] ?? '') === $difficulte)) ? 'selected' : '' ?>><?= htmlspecialchars($difficulte) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="admin-actions">
                <button type="submit" class="admin-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <?= $recetteToEdit ? 'Sauvegarder la recette' : 'Creer la recette' ?>
                </button>
                <?php if ($recetteToEdit): ?>
                    <a href="<?= $baseUrl ?>/index.php?action=admin-recipes" class="admin-btn secondary">
                        Annuler
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <h2 class="admin-card-title">Recettes existantes</h2>
            <span class="admin-inline-badge"><?= count($recettes) ?> recette(s)</span>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Recette</th>
                        <th>Difficulte</th>
                        <th>Temps</th>
                        <th>Nutrition</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recettes)): ?>
                        <tr>
                            <td colspan="5" class="admin-empty-state">Aucune recette pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recettes as $r): ?>
                            <?php $nutrition = $recettesNutritionMap[$r['id']] ?? []; ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars((string) $r['nom']) ?></strong>
                                    <?php if (!empty($r['description'])): ?>
                                        <div class="admin-table-note"><?= htmlspecialchars((string) $r['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="admin-inline-badge"><?= htmlspecialchars((string) ($r['difficulte'] ?? 'Moyen')) ?></span></td>
                                <td><?= htmlspecialchars((string) ($r['temps_preparation'] ?? '-')) ?></td>
                                <td>
                                    <div class="admin-metric-stack">
                                        <span><?= round((float) ($nutrition['calories'] ?? 0)) ?> kcal</span>
                                        <span><?= round((float) ($nutrition['proteines'] ?? 0), 1) ?> g prot</span>
                                        <span><?= round((float) ($nutrition['glucides'] ?? 0), 1) ?> g gluc</span>
                                        <span><?= round((float) ($nutrition['lipides'] ?? 0), 1) ?> g lip</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-actions admin-actions-inline">
                                        <a href="<?= $baseUrl ?>/index.php?action=admin-recipes&edit_id=<?= urlencode((string) $r['id']) ?>" class="admin-btn secondary admin-btn-sm">
                                            <i class="fa-solid fa-pen"></i>
                                            Modifier
                                        </a>
                                        <form method="POST" action="<?= $baseUrl ?>/index.php?action=admin-recipes" onsubmit="return confirm('Supprimer cette recette ?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $r['id']) ?>">
                                            <button type="submit" class="admin-btn danger admin-btn-sm">
                                                <i class="fa-solid fa-trash"></i>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
