<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Commande rapide</p>
            <h1><i class="fa-solid fa-bag-shopping icon"></i> Commander</h1>
        </div>
        <a href="/Web/index.php?action=foods-management" class="btn section-action">
            <i class="fa-solid fa-arrow-left"></i> Produits
        </a>
    </div>

    <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>

    <div class="admin-form-panel order-front-panel">
        <form method="POST" action="/Web/index.php?action=order-create" class="product-form admin-product-form" novalidate>
            <div class="field">
                <label for="product_id">Produit</label>
                <select id="product_id" name="product_id">
                    <option value="">Choisir un produit</option>
                    <?php foreach ($products as $item): ?>
                        <option value="<?= (int) $item['id'] ?>" <?= (int) ($selectedProduct ?? ($old['product_id'] ?? 0)) === (int) $item['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($item['name']) ?> - <?= htmlspecialchars((string) $item['price']) ?> DT
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label for="buyer_name">Nom complet</label>
                    <input id="buyer_name" name="buyer_name" value="<?= htmlspecialchars((string) ($old['buyer_name'] ?? '')) ?>">
                </div>
                <div class="field">
                    <label for="buyer_phone">Telephone</label>
                    <input id="buyer_phone" name="buyer_phone" value="<?= htmlspecialchars((string) ($old['buyer_phone'] ?? '')) ?>">
                </div>
            </div>
            <div class="field">
                <label for="buyer_email">Email</label>
                <input id="buyer_email" name="buyer_email" type="email" value="<?= htmlspecialchars((string) ($old['buyer_email'] ?? '')) ?>">
            </div>
            <div class="field">
                <label for="buyer_address">Adresse</label>
                <textarea id="buyer_address" name="buyer_address"><?= htmlspecialchars((string) ($old['buyer_address'] ?? '')) ?></textarea>
            </div>
            <div class="field">
                <label for="quantity">Quantite</label>
                <input id="quantity" name="quantity" value="<?= htmlspecialchars((string) ($old['quantity'] ?? '1')) ?>">
            </div>
            <button type="submit"><i class="fa-solid fa-paper-plane icon success"></i> Confirmer</button>
        </form>
    </div>
</section>
