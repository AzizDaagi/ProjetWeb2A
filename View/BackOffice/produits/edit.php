<?php
$pageTitle = 'Smart Nutrition | Edit Product';
include __DIR__ . '/../../../layouts/header.php';
?>

<div class="container product-form-shell">
    <h1><i class="fa-solid fa-pen-to-square icon"></i> Edit Product</h1>
    <p class="subtitle">Update the selected product information.</p>

    <form method="POST" class="product-form">
        <div class="field">
            <label for="name">Product Name</label>
            <input id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" placeholder="Name" required>
        </div>

        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Description" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="field-grid">
            <div class="field">
                <label for="price">Price</label>
                <input id="price" name="price" type="number" step="0.01" min="0" value="<?= htmlspecialchars((string) $product['price']) ?>" placeholder="Price" required>
            </div>
            <div class="field">
                <label for="calories">Calories</label>
                <input id="calories" name="calories" type="number" min="0" value="<?= htmlspecialchars((string) $product['calories']) ?>" placeholder="Calories" required>
            </div>
        </div>

        <div class="field">
            <label for="added_by">Seller Name</label>
            <input id="added_by" name="added_by" value="<?= htmlspecialchars($product['added_by']) ?>" placeholder="Seller" required>
        </div>

        <button type="submit"><i class="fa-solid fa-rotate icon success"></i> Update product</button>
    </form>
</div>

<?php include __DIR__ . '/../../../layouts/footer.php'; ?>
