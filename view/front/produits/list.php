<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Catalogue approuve</p>
            <h1><i class="fa-solid fa-store icon"></i> Ecommerce</h1>
            <p class="subtitle">Produits disponibles proposes par la communaute.</p>
        </div>
        <div class="section-actions">
            <a href="/projet-web-25-26/index.php?action=product-submit" class="btn section-action">
                <i class="fa-solid fa-plus"></i> Proposer un produit
            </a>
            <a href="/projet-web-25-26/index.php?action=cart-view" class="btn section-action">
                <i class="fa-solid fa-cart-shopping"></i> Panier
            </a>
            <a href="/projet-web-25-26/index.php?action=order-list" class="btn section-action">
                <i class="fa-solid fa-receipt"></i> Mes commandes
            </a>
        </div>
    </div>

    <?php if (isset($_GET['submitted'])): ?>
        <div class="alert alert-success">Produit envoye pour validation.</div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="GET" action="/projet-web-25-26/index.php" class="list-toolbar" style="margin: 18px 0 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
        <input type="hidden" name="action" value="foods-management">
        <div class="field" style="min-width: 220px; flex: 1;">
            <label for="q">Recherche</label>
            <input id="q" name="q" value="<?= htmlspecialchars((string) ($query ?? '')) ?>" placeholder="Nom, vendeur, calories, prix">
        </div>
        <div class="field" style="min-width: 160px;">
            <label for="sort">Tri</label>
            <select id="sort" name="sort">
                <option value="name" <?= ($sortBy ?? '') === 'name' ? 'selected' : '' ?>>Nom</option>
                <option value="price" <?= ($sortBy ?? '') === 'price' ? 'selected' : '' ?>>Prix</option>
                <option value="calories" <?= ($sortBy ?? '') === 'calories' ? 'selected' : '' ?>>Calories</option>
                <option value="added_by" <?= ($sortBy ?? '') === 'added_by' ? 'selected' : '' ?>>Vendeur</option>
                <option value="id" <?= ($sortBy ?? '') === 'id' ? 'selected' : '' ?>>Plus recent</option>
            </select>
        </div>
        <div class="field" style="min-width: 140px;">
            <label for="order">Ordre</label>
            <select id="order" name="order">
                <option value="ASC" <?= strtoupper((string) ($sortOrder ?? '')) === 'ASC' ? 'selected' : '' ?>>Ascendant</option>
                <option value="DESC" <?= strtoupper((string) ($sortOrder ?? '')) === 'DESC' ? 'selected' : '' ?>>Descendant</option>
            </select>
        </div>
        <button type="submit" class="btn section-action">Appliquer</button>
        <a href="/projet-web-25-26/index.php?action=foods-management" class="btn section-action">Reset</a>
    </form>

    <div class="products-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $row): ?>
                <article class="product-card">
                    <div class="product-image-wrap">
                        <img
                            src="/projet-web-25-26/uploads/<?= htmlspecialchars($row['image'] ?: 'jus.jpg') ?>"
                            alt="<?= htmlspecialchars($row['name']) ?>"
                            class="product-image"
                        >
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-top">
                            <h2><?= htmlspecialchars($row['name']) ?></h2>
                            <span class="product-price"><?= htmlspecialchars((string) $row['price']) ?> DT</span>
                        </div>
                        <p class="product-description"><?= htmlspecialchars($row['description']) ?></p>
                        <div class="product-meta">
                            <span><i class="fa-solid fa-fire"></i> <?= htmlspecialchars((string) $row['calories']) ?> kcal</span>
                            <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($row['added_by']) ?></span>
                        </div>
                        <div class="product-card-actions">
                            <form method="POST" action="/projet-web-25-26/index.php?action=cart-add" style="display: flex; gap: 10px; align-items: center;" novalidate>
                                <input type="hidden" name="product_id" value="<?= (int) $row['id'] ?>">
                                <input type="text" name="quantity" value="1" style="width: 56px; padding: 8px; text-align: center;">
                                <button type="submit" class="btn section-action">
                                    <i class="fa-solid fa-cart-plus"></i> Ajouter
                                </button>
                            </form>
                            <a href="/projet-web-25-26/index.php?action=order-create&product_id=<?= (int) $row['id'] ?>" class="btn section-action">
                                <i class="fa-solid fa-bag-shopping"></i> Commander
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-box-open"></i>
                <p>Aucun produit approuve pour le moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
