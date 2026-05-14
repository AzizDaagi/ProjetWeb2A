<div class="container admin-dashboard products-admin-list">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Back office</p>
            <h1><i class="fa-solid fa-boxes-stacked icon"></i> Produits</h1>
            <p class="subtitle">Gestion des produits ecommerce.</p>
        </div>
        <div class="section-actions">
            <a href="/Web/index.php?action=product-create" class="btn-admin">
                <i class="fa-solid fa-plus"></i> Ajouter
            </a>
            <a href="/Web/index.php?action=admin-orders" class="btn-admin-secondary">
                <i class="fa-solid fa-clipboard-list"></i> Commandes
            </a>
            <a href="/Web/index.php?action=products-pending" class="btn-admin-secondary">
                <i class="fa-solid fa-hourglass-half"></i> En attente
            </a>
            <a href="/Web/index.php?action=products-prediction" class="btn-admin-secondary">
                <i class="fa-solid fa-brain"></i> Prediction
            </a>
        </div>
    </div>

    <form method="GET" action="/Web/index.php" class="list-toolbar" style="margin: 18px 0 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
        <input type="hidden" name="action" value="products-admin">
        <div class="field" style="min-width: 160px;">
            <label for="status">Statut</label>
            <select id="status" name="status">
                <option value="all" <?= ($status ?? 'all') === 'all' ? 'selected' : '' ?>>Tous</option>
                <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Approuve</option>
                <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>En attente</option>
            </select>
        </div>
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
                <option value="calories" <?= ($sortBy ?? '') === 'calories' ? 'selected' : '' ?>>Calories</option>
                <option value="added_by" <?= ($sortBy ?? '') === 'added_by' ? 'selected' : '' ?>>Vendeur</option>
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
                    <th>Statut</th>
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
                            <td>
                                <span class="role-badge <?= (int) $row['is_approved'] === 1 ? 'role-user' : 'role-admin' ?>">
                                    <?= (int) $row['is_approved'] === 1 ? 'Approuve' : 'En attente' ?>
                                </span>
                            </td>
                            <td class="users-actions">
                                <a href="/Web/index.php?action=product-edit&id=<?= (int) $row['id'] ?>" class="btn-edit">
                                    <i class="fa-solid fa-pen"></i> Modifier
                                </a>
                                <a href="/Web/index.php?action=product-delete&id=<?= (int) $row['id'] ?>" class="btn-delete-user" onclick="return confirm('Supprimer ce produit ?');">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Aucun produit trouve</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
