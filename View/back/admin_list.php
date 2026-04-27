<?php
$pageTitle = 'Smart Nutrition | Admin Products';
$bodyClass = 'back-office';
$totalProducts = count($products);
$approvedProducts = count(array_filter($products, static fn(array $product): bool => (int) $product['is_approved'] === 1));
$pendingProducts = $totalProducts - $approvedProducts;
require __DIR__ . '/../header.php';
?>

<div class="admin-page">
    <div class="admin-page-head">
        <div>
            <h1><i class="fa-solid fa-boxes-stacked icon"></i> Product Dashboard</h1>
            <p class="subtitle">Track, validate, and maintain your product catalog from one place.</p>
        </div>
        <div class="section-actions">
            <a href="<?= htmlspecialchars(route_url('admin.products.create')) ?>" class="btn section-action">
                <i class="fa-solid fa-plus"></i> Add Product
            </a>
            <a href="<?= htmlspecialchars(route_url('admin.orders')) ?>" class="btn section-action btn-secondary-link">
                <i class="fa-solid fa-clipboard-list"></i> Orders
            </a>
            <a href="<?= htmlspecialchars(route_url('admin.products.pending')) ?>" class="btn section-action btn-secondary-link">
                <i class="fa-solid fa-hourglass-half"></i> Pending
            </a>
        </div>
    </div>

    <div class="admin-dashboard-layout">
        <section class="admin-widget admin-widget-wide">
            <div class="admin-widget-head">
                <h2>Catalog overview</h2>
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="admin-kpi-grid">
                <article class="kpi-card">
                    <p>Total Products</p>
                    <strong><?= htmlspecialchars((string) $totalProducts) ?></strong>
                    <i class="fa-solid fa-boxes-stacked"></i>
                </article>
                <article class="kpi-card">
                    <p>Approved</p>
                    <strong><?= htmlspecialchars((string) $approvedProducts) ?></strong>
                    <i class="fa-solid fa-circle-check"></i>
                </article>
                <article class="kpi-card">
                    <p>Pending Review</p>
                    <strong><?= htmlspecialchars((string) $pendingProducts) ?></strong>
                    <i class="fa-solid fa-hourglass-half"></i>
                </article>
                <article class="kpi-card">
                    <p>Module</p>
                    <strong>Catalog</strong>
                    <i class="fa-solid fa-leaf"></i>
                </article>
            </div>
        </section>

        <section class="admin-widget admin-widget-wide">
            <div class="admin-widget-head">
                <h2>Products</h2>
            </div>
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
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $product['id']) ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars((string) $product['price']) ?> DT</td>
                        <td><?= htmlspecialchars((string) $product['calories']) ?> kcal</td>
                        <td><?= htmlspecialchars($product['added_by']) ?></td>
                        <td>
                            <span class="role-badge <?= (int) $product['is_approved'] === 1 ? 'role-user' : 'role-admin' ?>">
                                <?= (int) $product['is_approved'] === 1 ? 'Approved' : 'Pending' ?>
                            </span>
                        </td>
                        <td class="users-actions">
                            <a href="<?= htmlspecialchars(route_url('admin.products.edit', ['id' => $product['id']])) ?>" class="btn-edit">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                            <a href="<?= htmlspecialchars(route_url('admin.products.delete', ['id' => $product['id']])) ?>" class="btn-role is-admin" onclick="return confirm('Delete this product?')">
                                Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../footer.php'; ?>
