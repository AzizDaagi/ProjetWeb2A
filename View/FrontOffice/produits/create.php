<?php
$pageTitle = 'Smart Nutrition | Submit Product';
include __DIR__ . '/../../../layouts/header.php';
?>

<div class="container product-form-shell">
    <h1><i class="fa-solid fa-store icon"></i> Submit a Product</h1>
    <p class="subtitle">Share a product with the catalog. It will appear after admin approval.</p>

    <form method="POST" enctype="multipart/form-data" class="product-form">
        <div class="field">
            <label for="name">Product Name</label>
            <input id="name" name="name" placeholder="Name" required>
        </div>

        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Description" required></textarea>
        </div>

        <div class="field-grid">
            <div class="field">
                <label for="price">Price</label>
                <input id="price" name="price" type="number" step="0.01" min="0" placeholder="Price" required>
            </div>
            <div class="field">
                <label for="calories">Calories</label>
                <input id="calories" name="calories" type="number" min="0" placeholder="Calories" required>
            </div>
        </div>

        <div class="field">
            <label for="added_by">Seller Name</label>
            <input id="added_by" name="added_by" placeholder="Your name" required>
        </div>

        <div class="field">
            <label for="image">Image</label>
            <input id="image" type="file" name="image" accept="image/*" required>
        </div>

        <button type="submit"><i class="fa-solid fa-paper-plane icon success"></i> Send for approval</button>
    </form>
</div>

<?php include __DIR__ . '/../../../layouts/footer.php'; ?>
