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
                <h2>Moderation List</h2>
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
