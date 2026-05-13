<?php
$pageTitle = 'Smart Nutrition | Admin Orders';
$bodyClass = 'back-office';
require __DIR__ . '/../header.php';
?>

<div class="admin-page">
    <div class="admin-page-head">
        <div>
            <h1><i class="fa-solid fa-clipboard-list icon"></i> Orders Dashboard</h1>
            <p class="subtitle">A compact list of every customer order.</p>
        </div>
        <div class="section-actions">
            <a href="<?= htmlspecialchars(route_url('admin.products')) ?>" class="btn section-action btn-secondary-link">
                <i class="fa-solid fa-boxes-stacked"></i> Products
            </a>
            <a href="<?= htmlspecialchars(route_url('order.create')) ?>" class="btn section-action">
                <i class="fa-solid fa-cart-shopping"></i> Front order form
            </a>
        </div>
    </div>

    <form method="GET" action="<?= htmlspecialchars(controller_url('index.php')) ?>" class="list-toolbar admin-list-toolbar" style="margin: 18px 0 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
        <input type="hidden" name="action" value="admin.orders">
        <div class="field" style="min-width: 180px;">
            <label for="sort">Sort by</label>
            <select id="sort" name="sort">
                <option value="id" <?= ($sortBy ?? '') === 'id' ? 'selected' : '' ?>>Newest</option>
                <option value="buyer_name" <?= ($sortBy ?? '') === 'buyer_name' ? 'selected' : '' ?>>Buyer Name</option>
                <option value="buyer_phone" <?= ($sortBy ?? '') === 'buyer_phone' ? 'selected' : '' ?>>Phone</option>
                <option value="total_price" <?= ($sortBy ?? '') === 'total_price' ? 'selected' : '' ?>>Total</option>
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
        <a href="<?= htmlspecialchars(route_url('admin.orders')) ?>" class="btn section-action btn-secondary-link">Reset</a>
    </form>

    <div class="admin-dashboard-layout">
        <section class="admin-widget admin-widget-wide">
            <div class="admin-widget-head">
                <h2>Orders overview</h2>
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="admin-kpi-grid">
                <article class="kpi-card">
                    <p>Total Orders</p>
                    <strong><?= htmlspecialchars((string) count($orders)) ?></strong>
                    <i class="fa-solid fa-receipt"></i>
                </article>
                <article class="kpi-card">
                    <p>Module</p>
                    <strong>Orders</strong>
                    <i class="fa-solid fa-clipboard-list"></i>
                </article>
            </div>
        </section>

        <section class="admin-widget admin-widget-wide">
            <div class="admin-widget-head">
                <h2>Orders List</h2>
                <span class="admin-table-badge">Live overview</span>
            </div>

        <?php if ($orders === []): ?>
            <p class="empty-hint">No orders available.</p>
        <?php else: ?>
            <div class="order-list-stack">
                <?php foreach ($orders as $order): ?>
                    <article class="order-list-card is-admin-list">
                        <div class="order-list-card-head">
                            <div>
                                <h4>Order #<?= htmlspecialchars((string) $order['id']) ?></h4>
                                <p>
                                    <?= htmlspecialchars((string) $order['buyer_name']) ?> ·
                                    <?= htmlspecialchars((string) $order['buyer_phone']) ?>
                                </p>
                            </div>
                            <strong class="order-list-total"><?= htmlspecialchars((string) $order['total_price']) ?> DH</strong>
                        </div>

                        <div class="order-list-items">
                            <?php if (!empty($order['items'])): ?>
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-list-item">
                                        <span class="order-list-item-name"><?= htmlspecialchars($item['product_name'] ?? 'Unknown') ?></span>
                                        <span class="order-list-item-meta">Qty <?= (int) $item['quantity'] ?> · <?= htmlspecialchars((string) $item['unit_price']) ?> DH</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="order-list-item">
                                    <span class="order-list-item-name">No items</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="order-list-footer">
                            <span>Placed on <?= htmlspecialchars((string) $order['created_at']) ?></span>
                            <div class="users-actions">
                                <a href="<?= htmlspecialchars(route_url('admin.orders.pdf', ['id' => $order['id']])) ?>" class="btn-role is-user" download="order-<?= (int) $order['id'] ?>.pdf">
                                    <i class="fa-solid fa-file-pdf"></i> PDF
                                </a>
                                <a href="<?= htmlspecialchars(route_url('admin.orders.edit', ['id' => $order['id']])) ?>" class="btn-edit">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="<?= htmlspecialchars(route_url('admin.orders.delete', ['id' => $order['id']])) ?>" class="btn-role is-admin" onclick="return confirm('Delete this order?')">
                                    Delete
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../footer.php'; ?>
