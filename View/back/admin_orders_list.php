<?php
$pageTitle = 'Smart Nutrition | Admin Orders';
$bodyClass = 'back-office';
require __DIR__ . '/../header.php';
?>

<section class="products-shell admin-dashboard-shell">
    <div class="admin-page-head">
        <div>
            <p class="section-kicker">Administration</p>
            <h2>Orders Dashboard</h2>
            <p class="admin-page-subtitle">Monitor and manage all customer orders.</p>
        </div>
        <div class="section-actions">
            <a href="<?= htmlspecialchars(route_url('admin.products')) ?>" class="btn section-action btn-secondary-link">
                <i class="fa-solid fa-boxes-stacked"></i> Products
            </a>
        </div>
    </div>

    <div class="admin-kpi-grid">
        <article class="admin-kpi-card">
            <p class="admin-kpi-label">Total Orders</p>
            <p class="admin-kpi-value"><?= htmlspecialchars((string) $totalOrders) ?></p>
            <p class="admin-kpi-note">All customer orders</p>
        </article>
        <article class="admin-kpi-card is-good">
            <p class="admin-kpi-label">Total Revenue</p>
            <p class="admin-kpi-value"><?= htmlspecialchars(number_format($totalRevenue, 2)) ?> DT</p>
            <p class="admin-kpi-note">Based on order totals</p>
        </article>
    </div>

    <div class="table-shell admin-table-shell">
        <div class="admin-table-head">
            <h3>Orders Table</h3>
            <span class="admin-table-badge">Live overview</span>
        </div>
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Buyer</th>
                    <th>Phone</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $order['id']) ?></td>
                    <td><?= htmlspecialchars((string) ($order['product_name'] ?? 'Unknown')) ?></td>
                    <td><?= htmlspecialchars((string) $order['buyer_name']) ?></td>
                    <td><?= htmlspecialchars((string) $order['buyer_phone']) ?></td>
                    <td><?= htmlspecialchars((string) $order['quantity']) ?></td>
                    <td><?= htmlspecialchars((string) $order['total_price']) ?> DT</td>
                    <td><?= htmlspecialchars((string) $order['created_at']) ?></td>
                    <td class="users-actions">
                        <a href="<?= htmlspecialchars(route_url('admin.orders.edit', ['id' => $order['id']])) ?>" class="btn-edit">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <a href="<?= htmlspecialchars(route_url('admin.orders.delete', ['id' => $order['id']])) ?>" class="btn-role is-admin" onclick="return confirm('Delete this order?')">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
