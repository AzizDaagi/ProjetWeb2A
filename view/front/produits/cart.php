<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Commande</p>
            <h1><i class="fa-solid fa-cart-shopping icon"></i> Panier</h1>
            <p class="subtitle">Verifiez vos produits avant la validation.</p>
        </div>
        <a href="/Web/index.php?action=foods-management" class="btn section-action">
            <i class="fa-solid fa-arrow-left"></i> Continuer les achats
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-cart-shopping"></i>
            <p>Votre panier est vide.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <form method="POST" action="/Web/index.php?action=cart-update" novalidate>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix unitaire</th>
                            <th>Quantite</th>
                            <th>Sous-total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= number_format((float) $item['unit_price'], 2) ?> DT</td>
                                <td>
                                    <input name="cart_update[<?= (int) $item['product_id'] ?>]" value="<?= (int) $item['quantity'] ?>" style="width: 70px; text-align: center;">
                                </td>
                                <td><?= number_format((float) $item['subtotal'], 2) ?> DT</td>
                                <td>
                                    <a href="/Web/index.php?action=cart-remove&product_id=<?= (int) $item['product_id'] ?>" class="btn-delete-user">
                                        <i class="fa-solid fa-trash"></i> Retirer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="section-actions" style="margin-top: 18px; justify-content: flex-end;">
                    <strong>Total: <?= number_format((float) $cartTotal, 2) ?> DT</strong>
                    <button type="submit" class="btn section-action">
                        <i class="fa-solid fa-rotate"></i> Mettre a jour
                    </button>
                    <a href="/Web/index.php?action=cart-checkout" class="btn section-action">
                        <i class="fa-solid fa-credit-card"></i> Valider
                    </a>
                    <a href="/Web/index.php?action=cart-clear" class="btn section-action" onclick="return confirm('Vider le panier ?');">
                        <i class="fa-solid fa-xmark"></i> Vider
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</section>
