<?php
$pageTitle = 'Smart Nutrition | Add Product';
$bodyClass = 'back-office';
require __DIR__ . '/../header.php';
?>

<div class="admin-page">
    <div class="admin-page-head">
        <div>
            <h1><i class="fa-solid fa-plus icon"></i> Add Product</h1>
            <p class="subtitle">Create a new product and publish it directly to the approved catalog.</p>
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
    <form method="POST" action="<?= htmlspecialchars(route_url('admin.products.create')) ?>" enctype="multipart/form-data" class="product-form admin-product-form" novalidate>
        <div class="field">
            <label for="name">Product Name</label>
            <input id="name" name="name" placeholder="Name" value="<?= htmlspecialchars((string) ($old['name'] ?? '')) ?>">
        </div>

        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Description"><?= htmlspecialchars((string) ($old['description'] ?? '')) ?></textarea>
        </div>

        <div class="field-grid">
            <div class="field">
                <label for="price">Price</label>
                <input id="price" name="price" type="text" placeholder="Price" value="<?= htmlspecialchars((string) ($old['price'] ?? '')) ?>">
            </div>
            <div class="field">
                <label for="calories">Calories</label>
                <input id="calories" name="calories" type="text" placeholder="Calories" value="<?= htmlspecialchars((string) ($old['calories'] ?? '')) ?>">
            </div>
        </div>

        <div class="field">
            <label for="added_by">Seller Name</label>
            <input id="added_by" name="added_by" placeholder="Seller name" value="<?= htmlspecialchars((string) ($old['added_by'] ?? '')) ?>">
        </div>

        <div class="field">
            <label for="image">Image</label>
            <input id="image" type="file" name="image" accept="image/*">
        </div>

        <button type="submit"><i class="fa-solid fa-floppy-disk icon success"></i> Add product</button>
    </form>
    </section>
</div>

<?php require __DIR__ . '/../footer.php'; ?>
