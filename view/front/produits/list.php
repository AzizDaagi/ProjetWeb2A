<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Catalogue approuve</p>
            <h1><i class="fa-solid fa-store icon"></i> Ecommerce</h1>
            <p class="subtitle">Produits disponibles proposes par la communaute.</p>
        </div>
        <a href="/Web/index.php?action=product-submit" class="btn section-action">
            <i class="fa-solid fa-plus"></i> Proposer un produit
        </a>
    </div>

    <?php if (isset($_GET['submitted'])): ?>
        <div class="alert alert-success">Produit envoye pour validation.</div>
    <?php endif; ?>

    <div class="products-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $row): ?>
                <article class="product-card">
                    <div class="product-image-wrap">
                        <img
                            src="/Web/uploads/<?= htmlspecialchars($row['image'] ?: 'jus.jpg') ?>"
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
