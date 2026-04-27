<?php
$pageTitle = 'Smart Nutrition | My Orders';
require __DIR__ . '/../header.php';
?>

<section class="products-shell admin-dashboard-shell">
    <div class="admin-page-head">
        <div>
            <p class="section-kicker">Order Tracking</p>
            <h2>My Orders</h2>
            <p class="admin-page-subtitle">Every order placed through the cart is listed here.</p>
        </div>
        <a href="<?= htmlspecialchars(route_url('order.create')) ?>" class="btn section-action">
            <i class="fa-solid fa-cart-shopping"></i> New Order
        </a>
    </div>

    <?php if (($created ?? false) === true): ?>
        <div class="alert alert-success">Order created successfully.</div>
    <?php endif; ?>
    <?php if (($deleted ?? false) === true): ?>
        <div class="alert alert-success">Order deleted successfully.</div>
    <?php endif; ?>
    <?php if (($updated ?? false) === true): ?>
        <div class="alert alert-success">Order updated successfully.</div>
    <?php endif; ?>

    <div class="table-shell admin-table-shell">
        <div class="admin-table-head">
            <h3>Orders List</h3>
            <span class="admin-table-badge"><?= htmlspecialchars((string) count($orders)) ?> total</span>
        </div>

        <?php if ($orders === []): ?>
            <p class="empty-hint">No orders have been placed yet.</p>
        <?php else: ?>
            <div class="order-list-stack">
                <?php foreach ($orders as $order): ?>
                    <article class="order-list-card">
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
                                <a href="<?= htmlspecialchars(route_url('order.edit', ['id' => $order['id']])) ?>" class="btn-edit">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="<?= htmlspecialchars(route_url('order.delete', ['id' => $order['id']])) ?>" class="btn-role is-admin" onclick="return confirm('Cancel this order?')">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
