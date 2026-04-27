<?php
$pageTitle = 'Smart Nutrition | Place Order';
require __DIR__ . '/../header.php';
?>

<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Order</p>
            <h2>Place Your Order</h2>
        </div>
        <a href="<?= htmlspecialchars(route_url('home')) ?>" class="btn section-action">
            <i class="fa-solid fa-arrow-left"></i> Back to products
        </a>
    </div>

    <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error" role="alert">
            <?= htmlspecialchars((string) $error) ?>
        </div>
    <?php endif; ?>

    <div class="admin-form-panel order-front-panel">
        <form method="POST" action="<?= htmlspecialchars(route_url('order.create')) ?>" class="product-form admin-product-form" novalidate>
            <div class="field">
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id">
                    <option value="">Select a product</option>
                    <?php foreach ($products as $item): ?>
                        <option value="<?= (int) $item['id'] ?>" <?= (int) ($selectedProduct ?? 0) === (int) $item['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($item['name']) ?> - <?= htmlspecialchars((string) $item['price']) ?> DT
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-grid">
                <div class="field">
                    <label for="buyer_name">Full Name</label>
                    <input id="buyer_name" name="buyer_name" placeholder="Your name" value="<?= htmlspecialchars((string) ($old['buyer_name'] ?? '')) ?>">
                </div>
                <div class="field">
                    <label for="buyer_phone">Phone</label>
                    <input id="buyer_phone" name="buyer_phone" placeholder="Phone number" value="<?= htmlspecialchars((string) ($old['buyer_phone'] ?? '')) ?>">
                </div>
            </div>

            <div class="field">
                <label for="buyer_address">Address</label>
                <textarea id="buyer_address" name="buyer_address" placeholder="Delivery address"><?= htmlspecialchars((string) ($old['buyer_address'] ?? '')) ?></textarea>
            </div>

            <div class="field-grid">
                <div class="field">
                    <label for="quantity">Quantity</label>
                    <input id="quantity" name="quantity" type="text" value="<?= htmlspecialchars((string) ($old['quantity'] ?? '1')) ?>">
                </div>
            </div>

            <button type="submit"><i class="fa-solid fa-paper-plane icon success"></i> Confirm order</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
