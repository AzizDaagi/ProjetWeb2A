<div class="container product-form-shell">
    <h1><i class="fa-solid fa-store icon"></i> Proposer un produit</h1>
    <p class="subtitle">Le produit sera visible apres validation par un administrateur.</p>

    <form method="POST" enctype="multipart/form-data" class="product-form" novalidate>
        <div class="field">
            <label for="name">Nom du produit</label>
            <input id="name" name="name" placeholder="Nom" required>
        </div>

        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Description" required></textarea>
        </div>

        <div class="field-grid">
            <div class="field">
                <label for="price">Prix</label>
                <input id="price" name="price" type="number" step="0.01" min="0" placeholder="Prix" required>
            </div>
            <div class="field">
                <label for="calories">Calories</label>
                <input id="calories" name="calories" type="number" min="0" placeholder="Calories" required>
            </div>
        </div>

        <div class="field">
            <label for="added_by">Vendeur</label>
            <input id="added_by" name="added_by" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" placeholder="Votre nom" required>
        </div>

        <div class="field">
            <label for="image">Image</label>
            <input id="image" type="file" name="image" accept="image/*" required>
        </div>

        <button type="submit"><i class="fa-solid fa-paper-plane icon success"></i> Envoyer pour validation</button>
    </form>
</div>
