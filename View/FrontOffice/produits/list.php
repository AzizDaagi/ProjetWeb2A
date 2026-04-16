<?php
$pageTitle = 'Smart Nutrition | Products';
$showFooter = true;
include __DIR__ . '/../../../layouts/header.php';
include __DIR__ . '/../../../front/home.php';
?>

<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Approved catalog</p>
            <h2>Available Products</h2>
        </div>
        <a href="<?= htmlspecialchars(app_url('index.php?action=frontCreate')) ?>" class="btn section-action">
            <i class="fa-solid fa-plus"></i> Add yours
        </a>
    </div>

    <div class="products-grid">
        <?php while ($row = $products->fetch_assoc()): ?>
            <article class="product-card">
                <div class="product-image-wrap">
                    <img
                        src="<?= htmlspecialchars(app_url('uploads/' . $row['image'])) ?>"
                        alt="<?= htmlspecialchars($row['name']) ?>"
                        class="product-image"
                    >
                </div>
                <div class="product-card-body">
                    <div class="product-card-top">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <span class="product-price"><?= htmlspecialchars((string) $row['price']) ?> DT</span>
                    </div>
                    <p class="product-description"><?= htmlspecialchars($row['description']) ?></p>
                    <div class="product-meta">
                        <span><i class="fa-solid fa-fire"></i> <?= htmlspecialchars((string) $row['calories']) ?> kcal</span>
                        <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($row['added_by']) ?></span>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</section>

<?php include __DIR__ . '/../../../layouts/footer.php'; ?>
