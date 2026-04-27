<?php
$pageTitle = 'Smart Nutrition | Edit Order';
$bodyClass = 'back-office';
require __DIR__ . '/../header.php';
?>

<section class="products-shell admin-dashboard-shell">
    <div class="admin-page-head">
        <div>
            <p class="section-kicker">Administration</p>
            <h2>Edit Order #<?= htmlspecialchars((string) $order['id']) ?></h2>
            <p class="admin-page-subtitle">Update customer order details and quantity.</p>
        </div>
        <a href="<?= htmlspecialchars(route_url('admin.orders')) ?>" class="btn section-action">
            <i class="fa-solid fa-arrow-left"></i> Back to orders
        </a>
    </div>

    <div class="admin-form-panel">
        <div class="order-product-brief compact-brief">
            <strong>Product:</strong> <?= htmlspecialchars((string) ($product['name'] ?? 'Unknown')) ?>
            <span class="brief-divider">|</span>
            <strong>Unit Price:</strong> <?= htmlspecialchars((string) ($product['price'] ?? '0')) ?> DT
        </div>

        <form method="POST" action="<?= htmlspecialchars(route_url('admin.orders.edit', ['id' => (int) $order['id']])) ?>" class="product-form admin-product-form" novalidate>
            <div class="field-grid">
                <div class="field">
                    <label for="buyer_name">Buyer Name</label>
                    <input id="buyer_name" name="buyer_name" value="<?= htmlspecialchars((string) $order['buyer_name']) ?>">
                </div>
                <div class="field">
                    <label for="buyer_phone">Buyer Phone</label>
                    <input id="buyer_phone" name="buyer_phone" value="<?= htmlspecialchars((string) $order['buyer_phone']) ?>">
                </div>
            </div>

            <div class="field">
                <label for="buyer_address">Buyer Address</label>
                <textarea id="buyer_address" name="buyer_address"><?= htmlspecialchars((string) $order['buyer_address']) ?></textarea>
            </div>

            <div class="field">
                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="text" value="<?= htmlspecialchars((string) $order['quantity']) ?>">
            </div>

            <button type="submit"><i class="fa-solid fa-rotate icon success"></i> Save Order</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
