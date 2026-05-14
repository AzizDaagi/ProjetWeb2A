<div class="container admin-dashboard products-admin-list">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Moderation</p>
            <h1><i class="fa-solid fa-hourglass-half icon"></i> Produits en attente</h1>
            <p class="subtitle">Validation des produits proposes par les utilisateurs.</p>
        </div>
        <a href="/Web/index.php?action=products-admin" class="btn-admin-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <form method="GET" action="/Web/index.php" class="list-toolbar" style="margin: 18px 0 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
        <input type="hidden" name="action" value="products-pending">
        <div class="field" style="min-width: 220px; flex: 1;">
            <label for="q">Recherche</label>
            <input id="q" name="q" value="<?= htmlspecialchars((string) ($query ?? '')) ?>" placeholder="Nom, vendeur, prix">
        </div>
        <div class="field" style="min-width: 160px;">
            <label for="sort">Tri</label>
            <select id="sort" name="sort">
                <option value="id" <?= ($sortBy ?? '') === 'id' ? 'selected' : '' ?>>Plus recent</option>
                <option value="name" <?= ($sortBy ?? '') === 'name' ? 'selected' : '' ?>>Nom</option>
                <option value="price" <?= ($sortBy ?? '') === 'price' ? 'selected' : '' ?>>Prix</option>
            </select>
        </div>
        <div class="field" style="min-width: 140px;">
            <label for="order">Ordre</label>
            <select id="order" name="order">
                <option value="ASC" <?= strtoupper((string) ($sortOrder ?? '')) === 'ASC' ? 'selected' : '' ?>>Ascendant</option>
                <option value="DESC" <?= strtoupper((string) ($sortOrder ?? '')) === 'DESC' ? 'selected' : '' ?>>Descendant</option>
            </select>
        </div>
        <button type="submit" class="btn-admin">Appliquer</button>
    </form>

    <div class="table-wrap">
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Calories</th>
                    <th>Vendeur</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $row['id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars((string) $row['price']) ?> DT</td>
                            <td><?= htmlspecialchars((string) $row['calories']) ?> kcal</td>
                            <td><?= htmlspecialchars($row['added_by']) ?></td>
                            <td class="users-actions">
                                <a href="/Web/index.php?action=product-approve&id=<?= (int) $row['id'] ?>" class="btn-edit">
                                    <i class="fa-solid fa-check"></i> Approuver
                                </a>
                                <a href="/Web/index.php?action=product-delete&id=<?= (int) $row['id'] ?>&from=pending" class="btn-delete-user" onclick="return confirm('Rejeter ce produit ?');">
                                    <i class="fa-solid fa-xmark"></i> Rejeter
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Aucun produit en attente</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
