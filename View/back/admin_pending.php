<?php
$pageTitle = 'Smart Nutrition | Pending Products';
$bodyClass = 'back-office';
$pendingCount = count($products);
require __DIR__ . '/../header.php';
?>

<div class="admin-page">
    <div class="admin-page-head">
        <div>
            <h1><i class="fa-solid fa-hourglass-half icon"></i> Pending Queue</h1>
            <p class="subtitle">Review submitted products and decide whether to approve or reject.</p>
        </div>
        <a href="<?= htmlspecialchars(route_url('admin.products')) ?>" class="btn section-action">
            <i class="fa-solid fa-arrow-left"></i> Back to admin list
        </a>
    </div>

    <form method="GET" action="<?= htmlspecialchars(controller_url('index.php')) ?>" class="list-toolbar admin-list-toolbar" style="margin: 18px 0 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
        <input type="hidden" name="action" value="admin.products.pending">
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
        <button type="submit" class="btn section-action">Apply</button>
        <a href="<?= htmlspecialchars(route_url('admin.products.pending')) ?>" class="btn section-action btn-secondary-link">Reset</a>
    </form>

    <div class="admin-dashboard-layout">
        <section class="admin-widget admin-widget-wide">
            <div class="admin-widget-head">
                <h2>Moderation overview</h2>
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="admin-kpi-grid">
                <article class="kpi-card is-warn">
                    <p>Awaiting Decision</p>
                    <strong><?= htmlspecialchars((string) $pendingCount) ?></strong>
                    <i class="fa-solid fa-clock"></i>
                </article>
                <article class="kpi-card">
                    <p>Module</p>
                    <strong>Moderation</strong>
                    <i class="fa-solid fa-filter"></i>
                </article>
            </div>
        </section>

        <section class="admin-widget admin-widget-wide">
            <div class="admin-widget-head">
                <h2>Pending orders List</h2>
            </div>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Calories</th>
                        <th>Seller</th>
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
                        <td class="users-actions">
                            <a href="<?= htmlspecialchars(route_url('admin.products.approve', ['id' => $product['id']])) ?>" class="btn-role is-user">
                                Approve
                            </a>
                            <a href="<?= htmlspecialchars(route_url('admin.products.delete', ['id' => $product['id'], 'from' => 'pending'])) ?>" class="btn-role is-admin" onclick="return confirm('Reject this product?')">
                                Reject
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
