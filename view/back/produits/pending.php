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
