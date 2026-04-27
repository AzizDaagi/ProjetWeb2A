<?php
$pageTitle = 'Smart Nutrition | Edit Product';
$bodyClass = 'back-office';
require __DIR__ . '/../header.php';
?>

<div class="admin-page">
    <div class="admin-page-head">
        <div>
            <h1><i class="fa-solid fa-pen-to-square icon"></i> Edit Product</h1>
            <p class="subtitle">Adjust product details while keeping your catalog data consistent.</p>
        </div>
        <a href="<?= htmlspecialchars(route_url('admin.products')) ?>" class="btn section-action">
            <i class="fa-solid fa-arrow-left"></i> Back to admin list
        </a>
    </div>

    <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error" role="alert">
            <?= htmlspecialchars((string) $error) ?>
        </div>
    <?php endif; ?>

    <section class="admin-widget">
    <form method="POST" action="<?= htmlspecialchars(route_url('admin.products.edit', ['id' => $product['id']])) ?>" class="product-form admin-product-form" novalidate>
        <div class="field">
            <label for="name">Product Name</label>
            <input id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" placeholder="Name">
        </div>

        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Description"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="field-grid">
            <div class="field">
                <label for="price">Price</label>
                <input id="price" name="price" type="text" value="<?= htmlspecialchars((string) $product['price']) ?>" placeholder="Price">
            </div>
            <div class="field">
                <label for="calories">Calories</label>
                <input id="calories" name="calories" type="text" value="<?= htmlspecialchars((string) $product['calories']) ?>" placeholder="Calories">
            </div>
        </div>

        <div class="field">
            <label for="added_by">Seller Name</label>
            <input id="added_by" name="added_by" value="<?= htmlspecialchars($product['added_by']) ?>" placeholder="Seller">
        </div>

        <button type="submit"><i class="fa-solid fa-rotate icon success"></i> Update product</button>
    </form>
    </section>
</div>

<?php require __DIR__ . '/../footer.php'; ?>
