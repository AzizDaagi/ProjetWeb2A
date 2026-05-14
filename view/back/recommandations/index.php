<?php $baseUrl = $baseUrl ?? '/projet-web-25-26'; ?>

<div class="admin-page admin-module-page">
    <div class="admin-page-header">
        <span class="admin-page-kicker">Catalogue approuve</span>
        <h1>Recommandations</h1>
        <p>Creez et gerez les regles de recommandations nutritionnelles.</p>
    </div>

    <section class="admin-card">
        <h2 class="admin-card-title">Ajouter une regle</h2>

        <form method="POST" action="<?= $baseUrl ?>/index.php?action=admin-recommendations" id="reco-form" novalidate>
            <input type="hidden" name="action" value="add">

            <div class="admin-form-grid">
                <div class="admin-form-group">
                    <label for="titre-input">Titre de la regle</label>
                    <input type="text" name="titre" id="titre-input">
                </div>

                <div class="admin-form-group">
                    <label for="type-input">Type d'objectif</label>
                    <select name="type_objectif" id="type-input">
                        <option value="Perte de poids">Perte de poids</option>
                        <option value="Prise de masse">Prise de masse</option>
                        <option value="Maintien">Maintien</option>
                        <option value="Sante globale">Sante globale</option>
                    </select>
                </div>

                <div class="admin-form-group full">
                    <label for="contenu-input">Contenu / explication</label>
                    <textarea name="contenu_regle" id="contenu-input"></textarea>
                </div>
            </div>

            <div class="admin-actions">
                <button type="submit" class="admin-btn">
                    <i class="fa-solid fa-plus"></i>
                    Ajouter la regle
                </button>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <h2 class="admin-card-title">Liste des recommandations</h2>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Objectif</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recommandations)): ?>
                        <tr>
                            <td colspan="3" class="admin-empty-state">Aucune recommandation enregistree.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recommandations as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars((string) $r['titre']) ?></strong>
                                    <?php if (!empty($r['contenu_regle'])): ?>
                                        <div class="admin-table-note"><?= htmlspecialchars((string) $r['contenu_regle']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="admin-inline-badge admin-inline-badge-accent"><?= htmlspecialchars((string) $r['type_objectif']) ?></span></td>
                                <td>
                                    <form method="POST" action="<?= $baseUrl ?>/index.php?action=admin-recommendations">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $r['id']) ?>">
                                        <button type="submit" class="admin-btn danger admin-btn-sm">
                                            <i class="fa-solid fa-trash"></i>
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
document.getElementById('reco-form').addEventListener('submit', function (e) {
    const titre = document.getElementById('titre-input').value.trim();
    const contenu = document.getElementById('contenu-input').value.trim();
    if (!titre || !contenu) {
        e.preventDefault();
    }
});
</script>
