<?php
$pageTitle = 'Smart Nutrition | Submit Product';
require __DIR__ . '/../header.php';
?>

<div class="container product-form-shell">
    <h1><i class="fa-solid fa-store icon"></i> Submit a Product</h1>
    <p class="subtitle">Share a product with the catalog. It will appear after admin approval.</p>

    <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error" role="alert">
            <?= htmlspecialchars((string) $error) ?>
        </div>
    <?php endif; ?>

    <div class="actions">
        <a href="<?= htmlspecialchars(route_url('home')) ?>" class="btn secondary">Back to catalog</a>
    </div>

    <form method="POST" action="<?= htmlspecialchars(route_url('product.create')) ?>" enctype="multipart/form-data" class="product-form" novalidate>
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
            <input id="added_by" name="added_by" placeholder="Your name" value="<?= htmlspecialchars((string) ($old['added_by'] ?? '')) ?>">
        </div>

        <div class="field">
            <label for="image">Image</label>
            <input id="image" type="file" name="image" accept="image/*">
        </div>

        <button type="submit"><i class="fa-solid fa-paper-plane icon success"></i> Send for approval</button>
    </form>
</div>

<?php require __DIR__ . '/../footer.php'; ?>
