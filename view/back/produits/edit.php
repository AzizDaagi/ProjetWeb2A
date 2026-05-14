<div class="container admin-dashboard admin-form-page product-form-shell">
    <h1><i class="fa-solid fa-pen-to-square icon"></i> Modifier un produit</h1>
    <p class="subtitle">Mise a jour des informations du produit.</p>
    <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>

    <form method="POST" class="product-form" novalidate>
        <div class="field">
            <label for="name">Nom du produit</label>
            <input id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" placeholder="Nom" required>
        </div>

        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Description" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="field-grid">
            <div class="field">
                <label for="price">Prix</label>
                <input id="price" name="price" type="number" step="0.01" min="0" value="<?= htmlspecialchars((string) $product['price']) ?>" placeholder="Prix" required>
            </div>
            <div class="field">
                <label for="calories">Calories</label>
                <input id="calories" name="calories" type="number" min="0" value="<?= htmlspecialchars((string) $product['calories']) ?>" placeholder="Calories" required>
            </div>
        </div>

        <div class="field">
            <label for="added_by">Vendeur</label>
            <input id="added_by" name="added_by" value="<?= htmlspecialchars($product['added_by']) ?>" placeholder="Vendeur" required>
        </div>

        <button type="submit"><i class="fa-solid fa-rotate icon success"></i> Enregistrer</button>
    </form>
</div>
