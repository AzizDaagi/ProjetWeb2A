<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Validation</p>
            <h1><i class="fa-solid fa-credit-card icon"></i> Checkout</h1>
            <p class="subtitle">Confirmez vos informations de livraison.</p>
        </div>
        <a href="/projet-web-25-26/index.php?action=cart-view" class="btn section-action">
            <i class="fa-solid fa-arrow-left"></i> Retour au panier
        </a>
    </div>

    <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>

    <div class="admin-form-panel order-front-panel">
        <form method="POST" action="/projet-web-25-26/index.php?action=cart-process" class="product-form admin-product-form" novalidate>
            <div class="field-grid">
                <div class="field">
                    <label for="buyer_name">Nom complet</label>
                    <input id="buyer_name" name="buyer_name" value="<?= htmlspecialchars((string) ($old['buyer_name'] ?? '')) ?>" placeholder="Votre nom">
                </div>
                <div class="field">
                    <label for="buyer_phone">Telephone</label>
                    <input id="buyer_phone" name="buyer_phone" value="<?= htmlspecialchars((string) ($old['buyer_phone'] ?? '')) ?>" placeholder="Telephone">
                </div>
            </div>
            <div class="field">
                <label for="buyer_email">Email</label>
                <input id="buyer_email" name="buyer_email" type="email" value="<?= htmlspecialchars((string) ($old['buyer_email'] ?? '')) ?>" placeholder="Email">
            </div>
            <div class="field">
                <label for="buyer_address">Adresse</label>
                <textarea id="buyer_address" name="buyer_address" placeholder="Adresse de livraison"><?= htmlspecialchars((string) ($old['buyer_address'] ?? '')) ?></textarea>
            </div>

            <div class="table-wrap">
                <table class="users-table">
                    <thead>
                        <tr><th>Produit</th><th>Qte</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= (int) $item['quantity'] ?></td>
                                <td><?= number_format((float) $item['subtotal'], 2) ?> DT</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p><strong>Total: <?= number_format((float) $cartTotal, 2) ?> DT</strong></p>
            <button type="submit"><i class="fa-solid fa-check icon success"></i> Confirmer la commande</button>
        </form>
    </div>
</section>
