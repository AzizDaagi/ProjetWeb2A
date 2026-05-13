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

    <div class="list-toolbar admin-list-toolbar" style="margin: 18px 0 24px; display: flex; gap: 18px; flex-wrap: wrap; align-items: stretch;">
        <form method="GET" action="<?= htmlspecialchars(controller_url('index.php')) ?>" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end; flex: 1; min-width: 320px;">
            <input type="hidden" name="action" value="admin.products">
            <div class="field" style="min-width: 180px;">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all" <?= ($status ?? 'all') === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <button type="submit" class="btn section-action">Apply Filter</button>
            <a href="<?= htmlspecialchars(route_url('admin.products')) ?>" class="btn section-action btn-secondary-link">Reset</a>
        </form>

        <form method="GET" action="<?= htmlspecialchars(controller_url('index.php')) ?>" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end; flex: 1; min-width: 320px; justify-content: flex-end;">
            <input type="hidden" name="action" value="admin.products">
            <div class="field" style="min-width: 180px;">
                <label for="sort">Sort by</label>
                <select id="sort" name="sort">
                    <option value="id" <?= ($sortBy ?? '') === 'id' ? 'selected' : '' ?>>Newest</option>
                    <option value="name" <?= ($sortBy ?? '') === 'name' ? 'selected' : '' ?>>Name</option>
                    <option value="price" <?= ($sortBy ?? '') === 'price' ? 'selected' : '' ?>>Price</option>
                    <option value="calories" <?= ($sortBy ?? '') === 'calories' ? 'selected' : '' ?>>Calories</option>
                    <option value="added_by" <?= ($sortBy ?? '') === 'added_by' ? 'selected' : '' ?>>Seller</option>
                </select>
            </div>
            <div class="field" style="min-width: 140px;">
                <label for="order">Order</label>
                <select id="order" name="order">
                    <option value="ASC" <?= strtoupper((string) ($sortOrder ?? '')) === 'ASC' ? 'selected' : '' ?>>Ascending</option>
                    <option value="DESC" <?= strtoupper((string) ($sortOrder ?? '')) === 'DESC' ? 'selected' : '' ?>>Descending</option>
                </select>
            </div>
            <button type="submit" class="btn section-action">Apply Sort</button>
            <a href="<?= htmlspecialchars(route_url('admin.products')) ?>" class="btn section-action btn-secondary-link">Reset</a>
        </form>
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
