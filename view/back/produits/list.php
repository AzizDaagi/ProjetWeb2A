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
            <a href="/Web/index.php?action=products-pending" class="btn-admin-secondary">
                <i class="fa-solid fa-hourglass-half"></i> En attente
            </a>
        </div>
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
