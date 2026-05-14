<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Suivi</p>
            <h1><i class="fa-solid fa-receipt icon"></i> Mes commandes</h1>
        </div>
        <a href="/Web/index.php?action=order-create" class="btn section-action">
            <i class="fa-solid fa-plus"></i> Nouvelle commande
        </a>
    </div>

    <?php if (!empty($created)): ?><div class="alert alert-success">Commande creee.</div><?php endif; ?>
    <?php if (!empty($updated)): ?><div class="alert alert-success">Commande modifiee.</div><?php endif; ?>
    <?php if (!empty($deleted)): ?><div class="alert alert-success">Commande supprimee.</div><?php endif; ?>

    <form method="GET" action="/Web/index.php" class="list-toolbar" style="margin: 18px 0 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
        <input type="hidden" name="action" value="order-list">
        <div class="field" style="min-width: 220px; flex: 1;">
            <label for="q">Recherche</label>
            <input id="q" name="q" value="<?= htmlspecialchars((string) ($query ?? '')) ?>" placeholder="Nom, telephone, email">
        </div>
        <div class="field" style="min-width: 160px;">
            <label for="sort">Tri</label>
            <select id="sort" name="sort">
                <option value="id" <?= ($sortBy ?? '') === 'id' ? 'selected' : '' ?>>Plus recent</option>
                <option value="buyer_name" <?= ($sortBy ?? '') === 'buyer_name' ? 'selected' : '' ?>>Nom</option>
                <option value="total_price" <?= ($sortBy ?? '') === 'total_price' ? 'selected' : '' ?>>Total</option>
            </select>
        </div>
        <div class="field" style="min-width: 140px;">
            <label for="order">Ordre</label>
            <select id="order" name="order">
                <option value="ASC" <?= strtoupper((string) ($sortOrder ?? '')) === 'ASC' ? 'selected' : '' ?>>Ascendant</option>
                <option value="DESC" <?= strtoupper((string) ($sortOrder ?? '')) === 'DESC' ? 'selected' : '' ?>>Descendant</option>
            </select>
        </div>
        <button type="submit" class="btn section-action">Appliquer</button>
    </form>

    <div class="table-wrap">
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th><th>Client</th><th>Produits</th><th>Total</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $orderRow): ?>
                        <tr>
                            <td><?= (int) $orderRow['id'] ?></td>
                            <td><?= htmlspecialchars($orderRow['buyer_name']) ?><br><small><?= htmlspecialchars($orderRow['buyer_phone']) ?></small></td>
                            <td>
                                <?php foreach (($orderRow['items'] ?? []) as $item): ?>
                                    <div><?= htmlspecialchars($item['product_name'] ?? 'Produit') ?> x <?= (int) $item['quantity'] ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td><?= number_format((float) $orderRow['total_price'], 2) ?> DT</td>
                            <td><?= htmlspecialchars((string) $orderRow['created_at']) ?></td>
                            <td class="users-actions">
                                <a href="/Web/index.php?action=order-edit&id=<?= (int) $orderRow['id'] ?>" class="btn-edit">Modifier</a>
                                <a href="/Web/index.php?action=order-delete&id=<?= (int) $orderRow['id'] ?>" class="btn-delete-user" onclick="return confirm('Annuler cette commande ?');">Annuler</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Aucune commande.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
