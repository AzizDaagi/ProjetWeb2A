<?php
$pageTitle = 'Smart Nutrition | Products';
$showFooter = true;
require __DIR__ . '/../header.php';
require __DIR__ . '/../hero.php';
?>

<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Approved catalog</p>
            <h2>Available Products</h2>
        </div>
        <a href="<?= htmlspecialchars(route_url('product.create')) ?>" class="btn section-action">
            <i class="fa-solid fa-plus"></i> Add yours
        </a>
        <a href="<?= htmlspecialchars(route_url('cart.view')) ?>" class="btn section-action btn-secondary-link">
            <i class="fa-solid fa-cart-shopping"></i> View Cart
        </a>
        <a href="<?= htmlspecialchars(route_url('order.list')) ?>" class="btn section-action btn-secondary-link">
            <i class="fa-solid fa-receipt"></i> My Orders
        </a>
    </div>

    <?php if ($products === []): ?>
        <div class="table-shell">
            <p>No approved products yet.</p>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <div class="product-image-wrap">
                        <img
                            src="<?= htmlspecialchars(upload_url((string) $product['image'])) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            class="product-image"
                        >
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-top">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <span class="product-price"><?= htmlspecialchars((string) $product['price']) ?> DT</span>
                        </div>
                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="product-meta">
                            <span><i class="fa-solid fa-fire"></i> <?= htmlspecialchars((string) $product['calories']) ?> kcal</span>
                            <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($product['added_by']) ?></span>
                        </div>
                        <div class="product-card-actions">
                            <form method="POST" action="<?= htmlspecialchars(route_url('cart.add')) ?>" style="display: flex; gap: 10px; align-items: center;" novalidate>
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input type="text" name="quantity" value="1"
                                       style="width: 50px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-size: 13px;">
                                <button type="submit" class="btn section-action" style="flex: 1;">
                                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../footer.php'; ?>
