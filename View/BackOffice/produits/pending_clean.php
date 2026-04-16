<?php
$pageTitle = 'Smart Nutrition | Pending Products';
include __DIR__ . '/../../../layouts/header.php';
?>

<section class="products-shell">
    <div class="section-heading">
        <div>
            <p class="section-kicker">Moderation</p>
            <h2>Pending Products</h2>
        </div>
        <a href="<?= htmlspecialchars(app_url('index.php?action=backList')) ?>" class="btn section-action">
            <i class="fa-solid fa-arrow-left"></i> Back to admin list
        </a>
    </div>

    <div class="table-shell">
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Calories</th>
                    <th>Seller</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars((string) $row['price']) ?> DT</td>
                    <td><?= htmlspecialchars((string) $row['calories']) ?> kcal</td>
                    <td><?= htmlspecialchars($row['added_by']) ?></td>
                    <td class="users-actions">
                        <a href="<?= htmlspecialchars(app_url('index.php?action=approve&id=' . $row['id'])) ?>" class="btn-role is-user">
                            Approve
                        </a>
                        <a href="<?= htmlspecialchars(app_url('index.php?action=delete&id=' . $row['id'])) ?>" class="btn-role is-admin" onclick="return confirm('Reject this product?')">
                            Reject
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . '/../../../layouts/footer.php'; ?>
