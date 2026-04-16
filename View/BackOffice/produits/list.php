<?php
$pageTitle = 'Smart Nutrition | Admin Products';
include __DIR__ . '/../../../layouts/header.php';
?>

<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Back office</p>
            <h2>Manage Products</h2>
        </div>
        <div class="section-actions">
            <a href="<?= htmlspecialchars(app_url('index.php?action=create')) ?>" class="btn section-action">
                <i class="fa-solid fa-plus"></i> Add Product
            </a>
            <a href="<?= htmlspecialchars(app_url('index.php?action=pending')) ?>" class="btn section-action btn-secondary-link">
                <i class="fa-solid fa-hourglass-half"></i> Pending
            </a>
        </div>
    </div>

    <div class="table-shell">
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Calories</th>
                    <th>Seller</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars((string) $row['price']) ?> DT</td>
                    <td><?= htmlspecialchars((string) $row['calories']) ?> kcal</td>
                    <td><?= htmlspecialchars($row['added_by']) ?></td>
                    <td>
                        <span class="role-badge <?= (int) $row['is_approved'] === 1 ? 'role-user' : 'role-admin' ?>">
                            <?= (int) $row['is_approved'] === 1 ? 'Approved' : 'Pending' ?>
                        </span>
                    </td>
                    <td class="users-actions">
                        <a href="<?= htmlspecialchars(app_url('index.php?action=edit&id=' . $row['id'])) ?>" class="btn-edit">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <a href="<?= htmlspecialchars(app_url('index.php?action=delete&id=' . $row['id'])) ?>" class="btn-role is-admin" onclick="return confirm('Delete this product?')">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . '/../../../layouts/footer.php'; ?>
