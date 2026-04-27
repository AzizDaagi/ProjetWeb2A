<?php
$pageTitle = 'Smart Nutrition | Edit Order';
require __DIR__ . '/../header.php';
?>

<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Order</p>
            <h2>Edit Order #<?= htmlspecialchars((string) $order['id']) ?></h2>
        </div>
        <a href="<?= htmlspecialchars(route_url('order.list')) ?>" class="btn section-action">
            <i class="fa-solid fa-arrow-left"></i> Back to my orders
        </a>
    </div>

    <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error" role="alert">
            <?= htmlspecialchars((string) $error) ?>
        </div>
    <?php endif; ?>

    <div class="admin-form-panel order-front-panel">
        <?php if (!empty($product)): ?>
            <div class="order-product-brief">
                <h3><?= htmlspecialchars((string) ($product['name'] ?? 'Product')) ?></h3>
                <p>Unit Price: <?= htmlspecialchars((string) ($product['price'] ?? '0')) ?> DT</p>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= htmlspecialchars(route_url('order.edit', ['id' => (int) $order['id']])) ?>" class="product-form admin-product-form" novalidate>
            <div class="field">
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id">
                    <?php foreach ($products as $item): ?>
                        <option value="<?= (int) $item['id'] ?>" <?= (int) ($order['product_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($item['name']) ?> - <?= htmlspecialchars((string) $item['price']) ?> DT
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-grid">
                <div class="field">
                    <label for="buyer_name">Full Name</label>
                    <input id="buyer_name" name="buyer_name" value="<?= htmlspecialchars((string) ($order['buyer_name'] ?? '')) ?>">
                </div>
                <div class="field">
                    <label for="buyer_phone">Phone</label>
                    <input id="buyer_phone" name="buyer_phone" value="<?= htmlspecialchars((string) ($order['buyer_phone'] ?? '')) ?>">
                </div>
            </div>

            <div class="field">
                <label for="buyer_address">Address</label>
                <textarea id="buyer_address" name="buyer_address" placeholder="Delivery address"><?= htmlspecialchars((string) ($order['buyer_address'] ?? '')) ?></textarea>
            </div>

            <div class="field">
                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="text" value="<?= htmlspecialchars((string) ($order['quantity'] ?? '1')) ?>">
            </div>

            <button type="submit"><i class="fa-solid fa-rotate icon success"></i> Update Order</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
