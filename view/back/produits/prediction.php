<div class="container admin-dashboard admin-form-page product-form-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Prediction</p>
            <h1><i class="fa-solid fa-brain icon"></i> Prediction produit</h1>
            <p class="subtitle">Estimation simple du prix ou des calories selon le nom et la description.</p>
        </div>
        <a href="/projet-web-25-26/index.php?action=products-admin" class="btn-admin-secondary">Retour</a>
    </div>

    <?php $predMessage = trim((string) ($_GET['pred_message'] ?? '')); ?>
    <?php if ($predMessage !== ''): ?>
        <div class="alert <?= ($_GET['pred_type'] ?? '') === 'error' ? 'alert-error' : 'alert-success' ?>">
            <?= htmlspecialchars($predMessage) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/projet-web-25-26/index.php?action=products-predict" class="product-form" novalidate>
        <div class="field">
            <label for="name">Nom</label>
            <input id="name" name="name" placeholder="Nom du produit">
        </div>
        <div class="field">
            <label for="description">Description</label>
            <input id="description" name="description" placeholder="Courte description">
        </div>
        <div class="field">
            <label for="prediction_type">Prediction</label>
            <select id="prediction_type" name="prediction_type">
                <option value="calories">Calories</option>
                <option value="price">Prix</option>
            </select>
        </div>
        <button type="submit"><i class="fa-solid fa-wand-magic-sparkles icon success"></i> Predire</button>
    </form>

    <div class="table-wrap" style="margin-top: 24px;">
        <table class="users-table">
            <thead><tr><th>ID</th><th>Produit</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= (int) $product['id'] ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><a class="btn-edit" href="/projet-web-25-26/index.php?action=product-predict&id=<?= (int) $product['id'] ?>">Predire</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
