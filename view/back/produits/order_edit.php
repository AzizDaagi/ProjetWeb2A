<div class="container admin-dashboard admin-form-page product-form-shell">
    <h1><i class="fa-solid fa-pen-to-square icon"></i> Modifier commande</h1>
    <p class="subtitle">Mise a jour d une commande ecommerce.</p>
    <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>

    <form method="POST" class="product-form" novalidate>
        <div class="field">
            <label for="product_id">Produit</label>
            <select id="product_id" name="product_id">
                <?php foreach ($products as $item): ?>
                    <option value="<?= (int) $item['id'] ?>" <?= (int) ($order['product_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($item['name']) ?> - <?= htmlspecialchars((string) $item['price']) ?> DT
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field-grid">
            <div class="field">
                <label for="buyer_name">Client</label>
                <input id="buyer_name" name="buyer_name" value="<?= htmlspecialchars((string) ($order['buyer_name'] ?? '')) ?>">
            </div>
            <div class="field">
                <label for="buyer_phone">Telephone</label>
                <input id="buyer_phone" name="buyer_phone" value="<?= htmlspecialchars((string) ($order['buyer_phone'] ?? '')) ?>">
            </div>
        </div>
        <div class="field">
            <label for="buyer_email">Email</label>
            <input id="buyer_email" name="buyer_email" value="<?= htmlspecialchars((string) ($order['buyer_email'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="buyer_address">Adresse</label>
            <textarea id="buyer_address" name="buyer_address"><?= htmlspecialchars((string) ($order['buyer_address'] ?? '')) ?></textarea>
        </div>
        <div class="field">
            <label for="quantity">Quantite</label>
            <input id="quantity" name="quantity" value="<?= htmlspecialchars((string) ($order['quantity'] ?: 1)) ?>">
        </div>
        <button type="submit"><i class="fa-solid fa-floppy-disk icon success"></i> Enregistrer</button>
    </form>
</div>
