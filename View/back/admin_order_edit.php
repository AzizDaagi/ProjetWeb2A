<?php
$pageTitle = 'Smart Nutrition | Edit Order';
$bodyClass = 'back-office';
require __DIR__ . '/../header.php';
?>

<div class="admin-page">
    <div class="admin-page-head">
        <div>
            <h1><i class="fa-solid fa-pen-to-square icon"></i> Edit Order</h1>
            <p class="subtitle">Update customer order details and quantity.</p>
        </div>
        <a href="<?= htmlspecialchars(route_url('admin.orders')) ?>" class="btn section-action">
            <i class="fa-solid fa-arrow-left"></i> Back to orders
        </a>
    </div>

    <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error" role="alert">
            <?= htmlspecialchars((string) $error) ?>
        </div>
    <?php endif; ?>

    <section class="admin-widget">
        <div class="order-product-brief compact-brief">
            <strong>Product:</strong> <?= htmlspecialchars((string) ($product['name'] ?? 'Unknown')) ?>
            <span class="brief-divider">|</span>
            <strong>Unit Price:</strong> <?= htmlspecialchars((string) ($product['price'] ?? '0')) ?> DT
        </div>

        <form method="POST" action="<?= htmlspecialchars(route_url('admin.orders.edit', ['id' => $order['id']])) ?>" class="product-form admin-product-form" novalidate>
            <div class="field">
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id">
                    <?php foreach ($products as $item): ?>
                        <option value="<?= (int) $item['id'] ?>" <?= (int) $order['product_id'] === (int) $item['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($item['name']) ?> - <?= htmlspecialchars((string) $item['price']) ?> DT
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

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
    </section>
</div>

<?php require __DIR__ . '/../footer.php'; ?>